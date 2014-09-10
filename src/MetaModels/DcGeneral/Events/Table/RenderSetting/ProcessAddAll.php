<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\GetReferrerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Event\ActionEvent;
use MetaModels\Factory as ModelFactory;
use MetaModels\IMetaModel;

/**
 * Process the "add all" button.
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSetting
 */
class ProcessAddAll
{
    /**
     * Perform the action.
     *
     * @param IMetaModel $metaModel       The MetaModel.
     *
     * @param array      $knownAttributes The list of known attributes.
     *
     * @param int        $startSort       The first sort index.
     *
     * @param int        $pid             The pid.
     *
     * @param array      $messages        The output messages.
     *
     * @return void
     */
    protected static function perform(IMetaModel $metaModel, $knownAttributes, $startSort, $pid, &$messages)
    {
        $database = \Database::getInstance();

        // Loop over all attributes now.
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!array_key_exists($attribute->get('id'), $knownAttributes)) {
                $arrData = array();

                $objRenderSetting = $attribute->getDefaultRenderSettings();
                foreach ($objRenderSetting->getKeys() as $key) {
                    $arrData[$key] = $objRenderSetting->get($key);
                }

                $arrData = array_replace_recursive(
                    $arrData,
                    array
                    (
                        'pid'      => $pid,
                        'sorting'  => $startSort,
                        'tstamp'   => time(),
                        'attr_id'  => $attribute->get('id'),
                    )
                );

                $startSort += 128;
                $database
                    ->prepare('INSERT INTO tl_metamodel_rendersetting %s')
                    ->set($arrData)
                    ->execute();
                $messages[] = array
                (
                    'severity' => 'confirm',
                    'message'  => sprintf(
                        $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_addsuccess'],
                        $attribute->getName()
                    ),
                );
            }
        }
    }

    /**
     * Handle the add all action event.
     *
     * @param ActionEvent $event The event.
     *
     * @return void
     */
    public static function handleAddAll(ActionEvent $event)
    {
        if ($event->getAction()->getName() !== 'rendersetting_addall') {
            return;
        }

        $environment = $event->getEnvironment();
        $propagator  = $environment->getEventPropagator();
        $database    = \Database::getInstance();
        $input       = $environment->getInputProvider();
        $pid         = IdSerializer::fromSerialized($input->getParameter('pid'));

        $event->getAction()->getName();

        $propagator->propagate(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            new LoadLanguageFileEvent('default')
        );
        $propagator->propagate(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            new LoadLanguageFileEvent('tl_metamodel_rendersetting')
        );
        $referrer = new GetReferrerEvent(true, 'tl_metamodel_rendersetting');
        $propagator->propagate(
            ContaoEvents::SYSTEM_GET_REFERRER,
            $referrer
        );

        $template = new \BackendTemplate('be_autocreatepalette');

        $template->cacheMessage  = '';
        $template->updateMessage = '';
        $template->href          = $referrer->getReferrerUrl();
        $template->headline      = $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'][1];

        // Severity is: error, confirm, info, new.
        $messages = array();

        $palette = $database
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
            ->execute($pid->getId());

        $metaModel = ModelFactory::byId($palette->pid);

        $alreadyExisting = $database
            ->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=?')
            ->execute($pid->getId());

        $knownAttributes = array();
        $intMax          = 128;
        while ($alreadyExisting->next()) {
            $knownAttributes[$alreadyExisting->attr_id] = $alreadyExisting->row();
            if ($intMax < $alreadyExisting->sorting) {
                $intMax = $alreadyExisting->sorting;
            }
        }

        $blnWantPerform = false;
        // Perform the labour work.
        if ($input->getValue('act') == 'perform') {
            self::perform($metaModel, $knownAttributes, $intMax, $pid->getId(), $arrMessages);
        } else {
            // Loop over all attributes now.
            foreach ($metaModel->getAttributes() as $attribute) {
                if (array_key_exists($attribute->get('id'), $knownAttributes)) {
                    $messages[] = array
                    (
                        'severity' => 'info',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_alreadycontained'],
                            $attribute->getName()
                        ),
                    );
                } else {
                    $messages[] = array
                    (
                        'severity' => 'confirm',
                        'message'  => sprintf(
                            $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addAll_willadd'],
                            $attribute->getName()
                        ),
                    );

                    $blnWantPerform = true;
                }
            }
        }

        if ($blnWantPerform) {
            $template->action = ampersand(\Environment::getInstance()->request);
            $template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
        } else {
            $template->action = ampersand($referrer->getReferrerUrl());
            $template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
        }

        $template->error = $messages;

        $event->setResponse($template->parse());
    }
}
