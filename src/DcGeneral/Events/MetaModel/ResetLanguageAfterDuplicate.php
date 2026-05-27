<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;

/**
 * Resets the active language to the fallback language after a model is duplicated.
 *
 * When a new record is created, LanguageFilter automatically resets to the fallback language. Copying a record
 * redirects to act=edit with the new ID, so LanguageFilter does not apply the reset. This listener writes the
 * fallback language into the dc-general session so that the subsequent edit opens in the fallback language,
 * consistent with the behaviour for new records.
 */
final class ResetLanguageAfterDuplicate
{
    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param IFactory $factory The factory.
     */
    public function __construct(IFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Reset the session language to the fallback after a model has been duplicated.
     *
     * @param PostDuplicateModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PostDuplicateModelEvent $event): void
    {
        $model        = $event->getModel();
        $providerName = $model->getProviderName();

        $metaModel = $this->factory->getMetaModel($providerName);
        if (!$metaModel instanceof ITranslatedMetaModel) {
            return;
        }

        $environment    = $event->getEnvironment();
        $sessionStorage = $environment->getSessionStorage();
        assert($sessionStorage instanceof SessionStorageInterface);

        $session                               = (array) $sessionStorage->get('dc_general');
        $session['ml_support'][$providerName]  = $metaModel->getMainLanguage();
        $sessionStorage->set('dc_general', $session);
    }
}
