<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table;

use Contao\BackendTemplate;
use Contao\Database;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This class is an abstraction over the add all handler.
 *
 * This is in use in the render settings and input screen settings.
 */
abstract class AbstractAddAllHandler implements EventSubscriberInterface
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * The environment in use.
     *
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * The translator in use.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * The Contao Database to use.
     *
     * @var Database
     */
    private $database;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The database rows already present.
     *
     * @var array
     */
    private $knownAttributes;

    /**
     * The input provider.
     *
     * @var InputProviderInterface
     */
    private $input;

    /**
     * The template being rendered.
     *
     * @var BackendTemplate
     */
    private $template;

    /**
     * The table name to work on.
     *
     * @var string
     */
    protected static $table;

    /**
     * The parent table name to work on.
     *
     * @var string
     */
    protected static $ptable;

    /**
     * The field to use for activating (published, enabled, ...).
     *
     * @var string
     */
    protected static $activeField;

    /**
     * The action name to listen on.
     *
     * @var string
     */
    protected static $actionName;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;
        $this->dispatcher       = $serviceContainer->getEventDispatcher();
        $this->database         = $serviceContainer->getDatabase();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            DcGeneralEvents::ACTION => 'handleActionEvent'
        );
    }

    /**
     * Create an empty data set for inclusion into the database.
     *
     * @param IAttribute $attribute The attribute to generate the data for.
     *
     * @return array
     */
    abstract protected function createEmptyDataFor($attribute);

    /**
     * Test if the passed attribute is acceptable.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    abstract protected function accepts($attribute);

    /**
     * Handle the action event.
     *
     * @param ActionEvent $event The event to handle.
     *
     * @return void
     *
     * @throws \RuntimeException When the MetaModel could not be determined.
     */
    public function handleActionEvent(ActionEvent $event)
    {
        $environment = $event->getEnvironment();

        if (($environment->getDataDefinition()->getName() !== static::$table)) {
            return;
        }

        if ($event->getAction()->getName() !== static::$actionName) {
            return;
        }

        $this->input = $environment->getInputProvider();
        $referrer    = new GetReferrerEvent(true, static::$table);
        $this->dispatcher->dispatch(ContaoEvents::SYSTEM_GET_REFERRER, $referrer);

        $this->environment = $environment;
        $this->translator  = $environment->getTranslator();

        $pid             = ModelId::fromSerialized($this->input->getParameter('pid'))->getId();
        $this->metaModel = $this->getMetaModelById($this->database
            ->prepare('SELECT * FROM ' . static::$ptable . ' WHERE id=?')
            ->execute($pid)->pid);

        if (!$this->metaModel) {
            throw new \RuntimeException('Could not retrieve MetaModel from ' . $this->input->getParameter('pid'));
        }

        $startSort             = 0;
        $this->knownAttributes = array();
        $alreadyExisting       = $this->database
            ->prepare('SELECT * FROM ' . static::$table . ' WHERE pid=? ORDER BY sorting ASC')
            ->execute($pid);
        while ($alreadyExisting->next()) {
            $this->knownAttributes[$alreadyExisting->attr_id] = $alreadyExisting->row();
            // Keep the sorting value.
            $startSort = $alreadyExisting->sorting;
        }

        if ($this->input->hasValue('add') || $this->input->hasValue('saveNclose')) {
            $this->perform(($startSort + 128), $pid, $this->input->hasValue('activate'));
        }
        if ($this->input->hasValue('saveNclose')) {
            \Controller::redirect($referrer->getReferrerUrl());
        }

        $this->template                = new \BackendTemplate('be_addallattributes');
        $this->template->href          = $referrer->getReferrerUrl();
        $this->template->backBt        = $this->translator->translate('MSC.backBT');
        $this->template->add           = $this->translator->translate('MSC.continue');
        $this->template->saveNclose    = $this->translator->translate('MSC.saveNclose');
        $this->template->activate      = $this->translator->translate('addAll_activate', static::$table);
        $this->template->headline      = $this->translator->translate('addall.1', static::$table);
        $this->template->selectAll     = $this->translator->translate('MSC.selectAll');
        $this->template->cacheMessage  = '';
        $this->template->updateMessage = '';
        $this->template->hasCheckbox   = false;
        $this->template->fields        = $this->generateForm();

        $event->setResponse($this->template->parse());
    }

    /**
     * Load the language file.
     *
     * @return void
     */
    private function loadLanguageFiles()
    {
        $this->dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, new LoadLanguageFileEvent('default'));
        $this->dispatcher->dispatch(ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE, new LoadLanguageFileEvent(static::$table));
    }

    /**
     * Perform the action.
     *
     * @param int  $startSort The first sort index.
     * @param int  $pid       The pid.
     * @param bool $activate  Flag if the new entries shall be activated from the beginning.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function perform($startSort, $pid, $activate)
    {
        $this->loadLanguageFiles();

        // Loop over all attributes now.
        foreach ($this->metaModel->getAttributes() as $attribute) {
            if (!$this->accepts($attribute)) {
                continue;
            }
            $attrId = $attribute->get('id');
            if ($this->knowsAttribute($attribute) || !$this->input->hasValue('attribute_' . $attrId)) {
                continue;
            }
            $data = array_replace_recursive(
                $this->createEmptyDataFor($attribute),
                array
                (
                    'pid'      => $pid,
                    'sorting'  => $startSort,
                    'tstamp'   => time(),
                    'attr_id'  => $attrId,
                )
            );
            if ($activate) {
                $data[static::$activeField] = 1;
            }

            $startSort += 128;
            $query      = $this->database
                ->prepare('INSERT INTO ' . static::$table . ' %s')
                ->set($data);
            $query->execute();
            $data['id'] = $query->insertId;

            $this->knownAttributes[$attrId] = $data;
        }
    }

    /**
     * Generate the form.
     *
     * @return array
     */
    private function generateForm()
    {
        $fields = array();
        // Loop over all attributes now.
        foreach ($this->metaModel->getAttributes() as $attribute) {
            $attrId = $attribute->get('id');
            if (!$this->accepts($attribute)) {
                continue;
            }
            if ($this->knowsAttribute($attribute)) {
                if ($this->input->hasValue('attribute_' . $attrId)) {
                    $fields[] = array(
                        'checkbox' => false,
                        'text'     => $this->translator->translate(
                            'addAll_addsuccess',
                            static::$table,
                            array($attribute->getName())
                        ),
                        'class'    => 'tl_confirm',
                        'attr_id'  => $attrId
                    );
                    continue;
                }

                $fields[] = array(
                    'checkbox' => false,
                    'text'     => $this->translator->translate(
                        'addAll_alreadycontained',
                        static::$table,
                        array($attribute->getName())
                    ),
                    'class'    => 'tl_info',
                    'attr_id'  => $attrId
                );
                continue;
            }
            $fields[] = array(
                'checkbox' => true,
                'text'     => $this->translator->translate(
                    'addAll_willadd',
                    static::$table,
                    array($attribute->getName())
                ),
                'class'    => 'tl_new',
                'attr_id'  => $attrId
            );

            $this->template->hasCheckbox = true;
        }

        return $fields;
    }

    /**
     * Check if an attribute is already present.
     *
     * @param IAttribute $attribute The attribute to check.
     *
     * @return bool
     */
    private function knowsAttribute($attribute)
    {
        return array_key_exists($attribute->get('id'), $this->knownAttributes);
    }

    /**
     * Retrieve the MetaModel with the given id.
     *
     * @param int $modelId The model being processed.
     *
     * @return IMetaModel
     */
    private function getMetaModelById($modelId)
    {
        $modelFactory = $this->serviceContainer->getFactory();
        $name         = $modelFactory->translateIdToMetaModelName($modelId);

        return $modelFactory->getMetaModel($name);
    }
}
