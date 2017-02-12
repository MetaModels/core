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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_attribute'] = array
(
    'config'       => array
    (
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => array
        (
            'keys' => array
            (
                'id'      => 'primary',
                'pid'     => 'index',
                'colname' => 'index'
            ),
        ),
    ),
    'dca_config'   => array
    (
        'data_provider'  => array
        (
            'parent' => array
            (
                'source' => 'tl_metamodel'
            ),

            'tl_metamodel_rendersetting' => array
            (
                'source' => 'tl_metamodel_rendersetting'
            ),

            'tl_metamodel_dcasetting'           => array
            (
                'source' => 'tl_metamodel_dcasetting'
            ),
            'tl_metamodel_dcasetting_condition' => array
            (
                'source' => 'tl_metamodel_dcasetting_condition'
            ),
        ),
        'childCondition' => array
        (
            array
            (
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_attribute',
                'setOn'   => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ),
                ),
                'filter'  => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                ),
                'inverse' => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_rendersetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'attr_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'attr_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_dcasetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'attr_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'attr_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedcheckbox',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),
            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedlongblob',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedlongtext',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedtabletext',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedtext',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array(
                'from'   => 'tl_metamodel_attribute',
                'to'     => 'tl_metamodel_translatedurl',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'att_id',
                        'from_field' => 'id',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'att_id',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),
        ),
    ),
    'list'         => array
    (
        'sorting'           => array
        (
            'disableGrouping' => true,
            'mode'            => 4,
            'fields'          => array('sorting'),
            'panelLayout'     => 'filter,limit',
            'headerFields'    => array
            (
                'name',
                'tableName',
                'tstamp',
                'translated',
                'varsupport'
            ),
            'flag'            => 1,
        ),
        'label'             => array
        (
            'fields' => array('name'),
            'format' => '%s'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            )
        ),
        'operations'        => array
        (
            'edit'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'cut'    => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),

        )
    ),
    'metapalettes' => array
    (
        // Initial palette with only the type to be selected.
        'default'                           => array
        (
            'title' => array
            (
                'type'
            )
        ),
        // Base palette for MetaModelAttribute derived types.
        '_base_ extends default'            => array
        (
            '+title'            => array
            (
                'colname',
                'name',
                'description'
            ),
            'advanced'          => array
            (
                ':hide',
                'isvariant',
                'isunique'
            ),
            'metamodeloverview' => array
            (),
            'backenddisplay'    => array
            (),
        ),
        // Default palette for MetaModelAttributeSimple derived types.
        // WARNING: even though it is empty, we have to keep it as otherwise
        // metapalettes will have no way for deriving the palettes. - They need the index.
        '_simpleattribute_ extends _base_'  => array
        (),
        // Default palette for MetaModelAttributeComplex derived types.
        // WARNING: even though it is empty, we have to keep it as otherwise
        // metapalettes will have no way for deriving the palettes. - They need the index.
        '_complexattribute_ extends _base_' => array
        (),
    ),
    // Palettes.
    'palettes'     => array
    (
        '__selector__' => array
        (
            'type'
        )
    ),
    // Fields.
    'fields'       => array
    (
        'id'          => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid'         => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'     => array
        (
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'      => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'type'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'includeBlankOption' => true,
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => 'true'
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'name'        => array
        (
            'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name'],
            'exclude' => true,
            'eval'    => array
            (
                'tl_class' => 'clr'
            ),
            'sql'     => "text NULL"
        ),
        'description' => array
        (
            'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['description'],
            'exclude' => true,
            'eval'    => array
            (
                'tl_class' => 'clr'
            ),
            'sql'     => "text NULL"
        ),
        // AVOID: doNotCopy => true, as child records won't be copied when copy metamodel.
        'colname'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['colname'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 64,
                'tl_class'  => 'w50'
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'isvariant'   => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isvariant'],
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'submitOnChange' => true,
                'tl_class'       => 'cbx w50'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'isunique'    => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_attribute']['isunique'],
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'cbx w50'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
    )
);
