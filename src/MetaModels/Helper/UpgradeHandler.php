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

namespace MetaModels\Helper;

/**
 * Upgrade handler class that changes structural changes in the database.
 * This should rarely be necessary but sometimes we need it.
 */
class UpgradeHandler
{
    /**
     * Retrieve the database instance from Contao.
     *
     * @return \Database
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected static function DB()
    {
        return \Database::getInstance();
    }

    /**
     * Handle database upgrade for the jumpTo field.
     *
     * Introduced: pre release 1.0.
     *
     * If the field 'metamodel_jumpTo' does exist in tl_module or tl_content,
     * it will get created and the content from jumpTo will get copied over.
     *
     * @return void
     */
    protected static function upgradeJumpTo()
    {
        $objDB = self::DB();
        if ($objDB->tableExists('tl_content', null, true)
            && !$objDB->fieldExists('metamodel_jumpTo', 'tl_content', true)
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_content',
                'metamodel_jumpTo',
                'int(10) unsigned NOT NULL default \'0\''
            );
            $objDB->execute('UPDATE tl_content SET metamodel_jumpTo=jumpTo;');
        }
        if ($objDB->tableExists('tl_module', null, true)
            && !$objDB->fieldExists('metamodel_jumpTo', 'tl_module', true)
        ) {
            // Create the column in the database and copy the data over.
            TableManipulation::createColumn(
                'tl_module',
                'metamodel_jumpTo',
                'int(10) unsigned NOT NULL default \'0\''
            );
            $objDB->execute('UPDATE tl_module SET metamodel_jumpTo=jumpTo;');
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
        if ($objDB->tableExists('tl_metamodel_dcasetting', null, true)
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

        if ($objDB->tableExists('tl_metamodel_dcasetting', null, true)
            && !$objDB->fieldExists('subpalette', 'tl_metamodel_dcasetting', true)
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
                    $check[$checkboxes->id] = $checkboxes->attr_id;
                }

                while ($subpalettes->next()) {
                    // Add property value condition for parent property dependency.
                    $data = array(
                        'pid' => 0,
                        'settingId' => $subpalettes->id,
                        'sorting' => '128',
                        'tstamp' => time(),
                        'enabled' => '1',
                        'type' => 'conditionpropertyvalueis',
                        'attr_id' => $check[$subpalettes->subpalette],
                        'comment' => sprintf(
                            'Only show when checkbox "%s" is checked',
                            $attr[$check[$subpalettes->subpalette]]
                        ),
                        'value' => '1',
                    );

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
     * Perform all upgrade steps.
     *
     * @return void
     */
    public static function perform()
    {
        self::upgradeJumpTo();
        self::upgradeDcaSettingsPublished();
        self::changeSubPalettesToConditions();
    }
}
