<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * This is the extension of the main MetaModels {@link \MetaModels\Attribute\IAttribute} interface which provides some
 * helper function for converting the alias values of the options to an id or the other way around. To create Attribute
 * instances, use a {@link \MetaModels\Factory}
 */
interface IAliasConverter extends IAttribute
{
    /**
     * Try to get the id for the given alias.
     *
     * @param string $alias    The alias to search for.
     * @param string $language The language to use for the search.
     *                         If the metamodels didn't support languages this parameter will be ignored.
     *
     * @return string|null - When language support for metamodels is given:
     *                          - Return the id for the alias in the given language
     *                          - Return null if the alias isn't found in the given language
     *                          - Return null if the given language isn't supported
     *                     - When language support for metamodels isn't given:
     *                          - Return the id for the alias, language parameter will be ignored
     *                          - Return null if the alias isn't found, language parameter will be ignored
     */
    public function getIdForAlias(string $alias, string $language): ?string;

    /**
     * Try to get the alias for the given id.
     *
     * Returns:
     * - When language support for metamodels is given:
     *      - Return the alias for the id in the given language
     *      - Return null if the id isn't found
     *      - Return null if the given language isn't supported
     * - When language support for metamodels isn't given:
     *      - Return the alias for the id, language parameter will be ignored
     *      - Return null if the id isn't found
     *
     * @param string $id       The id to search for.
     * @param string $language The target language to use for the result.
     *                         If the metamodels didn't support languages this parameter will be ignored.
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function getAliasForId(string $id, string $language): ?string;
}
