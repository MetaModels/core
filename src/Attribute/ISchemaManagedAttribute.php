<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * This interface denotes an attribute that has been migrated to the new schema manager and therefore the manipulation
 * methods shall not be called anymore.
 *
 * It is part of the migration process to MetaModels 3.0 and will get removed then.
 *
 * In order to migrate to a ISchemaManagedAttribute, you should implement and register a doctrine schema generator.
 *
 * @see \MetaModels\Schema\Doctrine\AbstractAttributeTypeSchemaGenerator
 */
interface ISchemaManagedAttribute extends IAttribute
{
}
