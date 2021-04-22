<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Symfony\Component\Finder\Finder;

/**
 * This migration find own xhtml template files and write a notice.
 */
class FindXhtmlTemplateMigration extends AbstractMigration
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * FindXhtmlTemplateMigration constructor.
     *
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Detect old MetaModels template files with extension "xhtml".';
    }

    /**
     * Must only run if:
     * - we find own xhtml template files.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        if ($this->findXhtmlTemplates()) {
            return true;
        }

        return false;
    }

    /**
     * Search own xhtml template files.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        if ($this->findXhtmlTemplates()) {
            return new MigrationResult(
                false,
                'Old template files with extension "xhtml" found - please rename extension to "html5" and select in module and content elements. This CAN NOT be done automatically!'
            );
        }
    }

    /**
     * Find own xhtml template files.
     *
     * @return bool
     */
    private function findXhtmlTemplates(): bool
    {
        $finder = Finder::create();
        $finder->in($this->projectDir . '/templates')->name(
            ['ce_metamodel_*.xhtml', 'metamodel_*.xhtml', 'mm_*.xhtml', 'mod_metamodel_*.xhtml']
        );

        if ($finder->hasResults()) {
            return true;
        }

        return false;
    }
}
