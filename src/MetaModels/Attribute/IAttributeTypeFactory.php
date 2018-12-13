<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 */
interface IAttributeTypeFactory
{
    /**
     * Return the type name - this is the internal type name used by MetaModels.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Retrieve the (relative to TL_ROOT) path to a icon for the type.
     *
     * @return string
     */
    public function getTypeIcon();

    /**
     * Create a new instance with the given information.
     *
     * @param array      $information The attribute information.
     *
     * @param IMetaModel $metaModel   The MetaModel instance the attribute shall be created for.
     *
     * @return IAttribute|null
     */
    public function createInstance($information, $metaModel);

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType();

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType();

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType();
}
