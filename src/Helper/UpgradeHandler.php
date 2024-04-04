<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Database;

/**
 * Upgrade handler class that changes structural changes in the database.
 * This should rarely be necessary, but sometimes we need it.
 */
class UpgradeHandler
{
    /**
     * Retrieve the database instance from Contao.
     *
     * @return Database
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected static function DB()
    {
        return Database::getInstance();
    }

    /**
     * Handle database upgrade for the jumpTo field.
     *
     * Introduced: pre-release 1.0.
     *
     * If the field 'metamodel_jumpTo' does exist in tl_module or tl_content,
     * it will get created and the content from jumpTo will get copied over.
     *
     * @return void
     */
    protected static function upgradeJumpTo()
    {
        $objDB = self::DB();
        if (
            $objDB->tableExists('tl_content', null, true)
            && !$objDB->fieldExists('metamodel_jumpTo', 'tl_content', true)
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_content',
                'metamodel_jumpTo',
                'int(10) unsigned NOT NULL default \'0\''
            );
            if ($objDB->fieldExists('jumpTo', 'tl_content', true)) {
                $objDB->execute('UPDATE tl_content SET metamodel_jumpTo=jumpTo;');
            }
        }

        if (
            $objDB->tableExists('tl_module', null, true)
            && !$objDB->fieldExists('metamodel_jumpTo', 'tl_module', true)
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_module',
                'metamodel_jumpTo',
                'int(10) unsigned NOT NULL default \'0\''
            );
            if ($objDB->fieldExists('jumpTo', 'tl_module', true)) {
                $objDB->execute('UPDATE tl_module SET metamodel_jumpTo=jumpTo;');
            }
        }
    }

    /**
     * Handle database upgrade for the published field in tl_metamodel_dcasetting.
     *
     * Introduced: version 1.0.1
     *
     * If the field 'published' does not exist in tl_metamodel_dcasetting,
     * it will get created and all rows within that table will get initialized to 1
     * to have the prior behaviour back (everything was being published before then).
     *
     * @return void
     */
    protected static function upgradeDcaSettingsPublished()
    {
        $objDB = self::DB();
        if (
            $objDB->tableExists('tl_metamodel_dcasetting', null, true)
            && !$objDB->fieldExists('published', 'tl_metamodel_dcasetting', true)
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_metamodel_dcasetting',
                'published',
                'char(1) NOT NULL default \'\''
            );
            // Publish everything we had so far.
            $objDB->execute('UPDATE tl_metamodel_dcasetting SET published=1;');
        }
    }

    /**
     * Handle database upgrade for changing sub palettes to input field conditions.
     *
     * @return void
     */
    protected static function changeSubPalettesToConditions()
    {
        $objDB = self::DB();

        // Create the table.
        if (!$objDB->tableExists('tl_metamodel_dcasetting_condition')) {
            $objDB->execute(
                'CREATE TABLE `tl_metamodel_dcasetting_condition` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
                `settingId` int(10) unsigned NOT NULL default \'0\',
                `sorting` int(10) unsigned NOT NULL default \'0\',
                `tstamp` int(10) unsigned NOT NULL default \'0\',
                `enabled` char(1) NOT NULL default \'\',
                `type` varchar(255) NOT NULL default \'\',
                `attr_id` int(10) unsigned NOT NULL default \'0\',
                `comment` varchar(255) NOT NULL default \'\',
                `value` blob NULL,
                PRIMARY KEY  (`id`)
                )ENGINE=MyISAM DEFAULT CHARSET=utf8;'
            );
        }

        if (
            $objDB->tableExists('tl_metamodel_dcasetting', null, true)
            && $objDB->fieldExists('subpalette', 'tl_metamodel_dcasetting', true)
        ) {
            $subpalettes = $objDB->execute('SELECT * FROM tl_metamodel_dcasetting WHERE subpalette!=0');

            if ($subpalettes->numRows) {
                // Get all attribute names and setting ids.
                $attributes = $objDB
                    ->execute('
                        SELECT attr_id, colName
                        FROM tl_metamodel_dcasetting AS setting
                        LEFT JOIN tl_metamodel_attribute AS attribute
                        ON (setting.attr_id=attribute.id)
                        WHERE dcatype=\'attribute\'
                    ');

                $attr = array();
                while ($attributes->next()) {
                    /** @psalm-suppress UndefinedMagicPropertyFetch */
                    $attr[$attributes->attr_id] = $attributes->colName;
                }

                $checkboxes = $objDB->execute('
                    SELECT *
                    FROM tl_metamodel_dcasetting
                    WHERE
                        subpalette=0
                        AND dcatype=\'attribute\'
                    ');

                $check = array();
                while ($checkboxes->next()) {
                    /** @psalm-suppress UndefinedMagicPropertyFetch */
                    $check[$checkboxes->id] = $checkboxes->attr_id;
                }

                while ($subpalettes->next()) {
                    // Add property value condition for parent property dependency.
                    /** @psalm-suppress UndefinedMagicPropertyFetch */
                    $data = [
                        'pid'       => 0,
                        'settingId' => $subpalettes->id,
                        'sorting'   => '128',
                        'tstamp'    => \time(),
                        'enabled'   => '1',
                        'type'      => 'conditionpropertyvalueis',
                        'attr_id'   => $check[$subpalettes->subpalette],
                        'comment'   => \sprintf(
                            'Only show when checkbox "%s" is checked',
                            $attr[$check[$subpalettes->subpalette]]
                        ),
                        'value'     => '1',
                    ];

                    $objDB
                        ->prepare('INSERT INTO tl_metamodel_dcasetting_condition %s')
                        ->set($data)
                        ->execute();

                    $objDB
                        ->prepare('UPDATE tl_metamodel_dcasetting SET subpalette=0 WHERE id=?')
                        ->execute($subpalettes->id);

                    $objDB
                        ->prepare('UPDATE tl_metamodel_dcasetting SET submitOnChange=1 WHERE id=?')
                        ->execute($subpalettes->subpalette);
                }
            }

            TableManipulation::dropColumn('tl_metamodel_dcasetting', 'subpalette', true);
        }
    }

    /**
     * Upgrade the database to change from closed dca to editable, creatable and deletable.
     *
     * @return void
     */
    protected static function upgradeClosed()
    {
        $objDB = self::DB();

        // Change isclosed to iseditable, iscreatable and isdeleteable.
        if (
            $objDB->tableExists('tl_metamodel_dca', null, true)
            && !$objDB->fieldExists('iseditable', 'tl_metamodel_dca')
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_metamodel_dca',
                'iseditable',
                'char(1) NOT NULL default \'\''
            );
            TableManipulation::createColumn(
                'tl_metamodel_dca',
                'iscreatable',
                'char(1) NOT NULL default \'\''
            );
            TableManipulation::createColumn(
                'tl_metamodel_dca',
                'isdeleteable',
                'char(1) NOT NULL default \'\''
            );

            $objDB->execute('
                UPDATE tl_metamodel_dca
                SET
                    iseditable=isclosed^1,
                    iscreatable=isclosed^1,
                    isdeleteable=isclosed^1
            ');

            TableManipulation::dropColumn('tl_metamodel_dca', 'isclosed', true);
        }
    }

    /**
     * Upgrade the input screens.
     *
     * @return void
     */
    protected static function upgradeInputScreenMode()
    {
        $objDB = self::DB();
        if (!$objDB->tableExists('tl_metamodel_dca', null, true)) {
            return;
        }

        if (!$objDB->fieldExists('mode', 'tl_metamodel_dca')) {
            return;
        }

        // Create the fields for grouping and sorting and migrate.
        if (!$objDB->fieldExists('rendermode', 'tl_metamodel_dca')) {
            TableManipulation::createColumn(
                'tl_metamodel_dca',
                'rendermode',
                'varchar(12) NOT NULL default \'\''
            );
        }

        $objDB->execute('UPDATE tl_metamodel_dca SET rendermode="flat" WHERE mode IN (0,1,2,3)');
        $objDB->execute('UPDATE tl_metamodel_dca SET rendermode="parented" WHERE mode IN (4)');
        $objDB->execute('UPDATE tl_metamodel_dca SET rendermode="hierarchical" WHERE mode IN (5,6)');

        TableManipulation::dropColumn('tl_metamodel_dca', 'mode', true);
    }

    /**
     * Upgrade the input screens.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function upgradeInputScreenFlag()
    {
        $objDB = self::DB();
        if (!$objDB->tableExists('tl_metamodel_dca', null, true)) {
            return;
        }
        if (!$objDB->fieldExists('flag', 'tl_metamodel_dca')) {
            return;
        }

        if (!$objDB->tableExists('tl_metamodel_dca_sortgroup', null, true)) {
            $objDB->execute('
                CREATE TABLE `tl_metamodel_dca_sortgroup` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
                `sorting` int(10) unsigned NOT NULL default \'0\',
                `tstamp` int(10) unsigned NOT NULL default \'0\',
                `name` text NULL,
                `isdefault` char(1) NOT NULL default \'\',
                `ismanualsort` char(1) NOT NULL default \'\',
                `rendergrouptype` varchar(10) NOT NULL default \'none\',
                `rendergrouplen` int(10) unsigned NOT NULL default \'1\',
                `rendergroupattr` int(10) unsigned NOT NULL default \'0\',
                `rendersort` varchar(10) NOT NULL default \'asc\',
                `rendersortattr` int(10) unsigned NOT NULL default \'0\',
                PRIMARY KEY  (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            ');
        }

        $dca = $objDB->execute('SELECT * FROM tl_metamodel_dca');

        while ($dca->next()) {
            $renderGroupLen = 0;
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            if (\in_array($dca->flag, [1, 2, 3, 4])) {
                $renderGroupType = 'char';
                if (\in_array($dca->flag, [1, 2])) {
                    $renderGroupLen = 1;
                } else {
                    $renderGroupLen = 2;
                }
            } elseif (\in_array($dca->flag, [5, 6])) {
                $renderGroupType = 'day';
            } elseif (\in_array($dca->flag, [7, 8])) {
                $renderGroupType = 'month';
            } elseif (\in_array($dca->flag, [9, 10])) {
                $renderGroupType = 'year';
            } elseif (\in_array($dca->flag, [11, 12])) {
                $renderGroupType = 'digit';
            } else {
                $renderGroupType = 'none';
            }

            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $data = [
                'pid'             => $dca->id,
                'sorting'         => 128,
                'tstamp'          => time(),
                'name'            => null,
                'isdefault'       => '1',
                'ismanualsort'    => '1',
                'rendergrouptype' => $renderGroupType,
                'rendergrouplen'  => $renderGroupLen,
                'rendergroupattr' => 0,
                'rendersort'      => \in_array($dca->flag, [2, 4, 6, 8, 10, 12]) ? 'desc' : 'asc',
                'rendersortattr'  => 0,
            ];

            $objDB
                ->prepare('INSERT INTO tl_metamodel_dca_sortgroup %s')
                ->set($data)
                ->execute();
        }

        TableManipulation::dropColumn('tl_metamodel_dca', 'flag', true);
    }

    /**
     * Perform all upgrade steps.
     *
     * @return void
     */
    public static function perform()
    {
        self::upgradeJumpTo();
        self::upgradeDcaSettingsPublished();
        self::changeSubPalettesToConditions();
        self::upgradeClosed();
        self::upgradeInputScreenMode();
        self::upgradeInputScreenFlag();
    }
}
