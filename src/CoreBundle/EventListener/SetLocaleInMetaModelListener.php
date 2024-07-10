<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener;

use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\ITranslatedMetaModel;

final class SetLocaleInMetaModelListener
{
    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function __invoke(CreateMetaModelEvent $event): void
    {
        $metaModel = $event->getMetaModel();
        $language  = $GLOBALS['TL_LANGUAGE'] ?? null;
        if (null !== $language && $metaModel instanceof ITranslatedMetaModel) {
            $metaModel->selectLanguage($language);
        }
    }
}
