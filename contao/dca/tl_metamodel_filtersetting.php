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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

/**
 * Table tl_metamodel_attribute
 */

$GLOBALS['TL_DCA']['tl_metamodel_filtersetting'] = array
(
    'config'                => array
    (
        'dataContainer'    => 'General',
        'switchToEdit'     => false,
        'enableVersioning' => false,
        'sql'              => array
        (
            'keys' => array
            (
                'id'  => 'primary',
                'pid' => 'index'
            ),
        )
    ),
    'dca_config'            => array
    (
        'data_provider'  => array
        (
            'root'   => array
            (
                'source' => 'tl_metamodel_filtersetting'
            ),
            'parent' => array
            (
                'source' => 'tl_metamodel_filter',
            ),
        ),
        'childCondition' => array
        (
            array(
                'from'   => 'tl_metamodel_filter',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => array
                (
                    array(
                        'to_field'   => 'fid',
                        'from_field' => 'id',
                    )
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'fid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),
            array(
                'from'   => 'tl_metamodel_filtersetting',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ),
                    array
                    (
                        'to_field'   => 'fid',
                        'from_field' => 'fid',
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            )
        ),
        'rootEntries'    => array
        (
            'tl_metamodel_filtersetting' => array
            (
                'setOn'  => array
                (
                    array
                    (
                        'property' => 'pid',
                        'value'    => '0'
                    ),
                ),
                'filter' => array
                (
                    array
                    (
                        'property'  => 'pid',
                        'operation' => '=',
                        'value'     => '0'
                    )
                )
            )
        ),
        'child_list'     => array
        (
            'tl_metamodel_filtersetting' => array
            (
                'fields' => array
                (
                    'type',
                    'attr_id',
                    'urlparam',
                    'comment',
                    'enabled'
                ),
                'format' => '%s %s',
            ),
        )
    ),
    'list'                  => array
    (
        'sorting'           => array
        (
            'mode'         => 5,
            'fields'       => array('sorting'),
            'headerFields' => array('type', 'attr_id'),
            'flag'         => 1,
            'icon'         => 'system/modules/metamodels/assets/images/icons/filter_and.png',
        ),
        'label'             => array
        (
            'fields' => array
            (
                'fid',
                'type',
                'attr_id',
                'urlparam',
                'comment'
            ),
            'format' => '%s',
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
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ),
            'cut'    => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['cut'],
                'href'       => 'act=paste&amp;mode=cut',
                'icon'       => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),
            'toggle' => array
            (
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['toggle'],
                'icon'           => 'visible.gif',
                'toggleProperty' => 'enabled',
            )
        )
    ),
    'palettes'              => array
    (
        '__selector__' => array
        (
            'type'
        )
    ),
    'metapalettes'          => array
    (
        'default'                                      => array
        (
            'title' => array(
                'type',
                'enabled',
                'comment'
            ),
        ),
        '_attribute_ extends default'                  => array
        (
            'config' => array
            (
                'attr_id'
            )
        ),
        'conditionor extends default'                  => array
        (
            'config' => array
            (
                'stop_after_match'
            )
        ),
        'idlist extends default'                       => array
        (
            '+config' => array
            (
                'items'
            ),
        ),
        'simplelookup extends _attribute_'             => array
        (
            '+fefilter' => array
            (
                'urlparam',
                'predef_param',
                'allow_empty',
                'label',
                'template',
                'defaultid',
                'blankoption',
                'onlyused',
                'onlypossible',
                'skipfilteroptions'
            ),
        ),
        'customsql extends default'                    => array
        (
            '+config' => array
            (
                'customsql'
            ),
        ),
        'simplelookup_translated extends simplelookup' => array
        (
            '+config' => array
            (
                'all_langs'
            ),
        ),
    ),
    'metasubselectpalettes' => array
    (
        'attr_id' => array
        ()
    ),
    'simplelookup_palettes' => array
    (
        '_translated_' => array
        (
            'all_langs'
        )
    ),
    'fields'                => array
    (
        'id'                => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid'               => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'           => array
        (
            'sorting' => true,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'            => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'fid'               => array
        (
            // Keep this empty but keep it here!
            // needed for act=copy in DC_Table, as otherwise the fid value will not be copied.
            'label' => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ),
        'type'              => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'enabled'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'comment'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['comment'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array('tl_class' => 'clr long'),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'attr_id'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'tl_class'           => 'w50',
                'chosen'             => true
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'all_langs'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['all_langs'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12 cbx',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'items'             => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['items'],
            'exclude'   => true,
            'inputType' => 'textarea',
            'eval'      => array
            (
                'doNotSaveEmpty' => true,
                'alwaysSave'     => true,
                'mandatory'      => true,
            ),
            'sql'       => "text NULL"
        ),
        'urlparam'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'predef_param'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['predef_param'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'alwaysSave' => true,
                'tl_class'   => 'clr w50 m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'customsql'         => array
        (
            'label'       => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'],
            'exclude'     => true,
            'inputType'   => 'textarea',
            'eval'        => array
            (
                'allowHtml'      => true,
                'preserveTags'   => true,
                'decodeEntities' => true,
                'rte'            => 'ace|sql',
                'class'          => 'monospace',
                'helpwizard'     => true,
            ),
            'explanation' => 'customsql',
            'sql'         => "text NULL"
        ),
        'allow_empty'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['allow_empty'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'stop_after_match'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['stop_after_match'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'alwaysSave' => true,
                'tl_class'   => 'w50 m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'label'             => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'clr w50',
            ),
            'sql'       => "blob NULL"
        ),
        'template'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template'],
            'default'   => 'mm_filteritem_default',
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class' => 'w50',
                'chosen'   => true
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'blankoption'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50 clr',
            ),
            'sql'       => "char(1) NOT NULL default '1'"
        ),
        'onlyused'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'       => 'w50',
                'submitOnChange' => true,
            ),
            'sql'       => "char(1) NOT NULL default '0'"
        ),
        'onlypossible'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default '0'"
        ),
        'skipfilteroptions' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['skipfilteroptions'],
            'exclude'   => true,
            'default'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default '0'"
        ),
        'defaultid'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['defaultid'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class'           => 'w50 clr',
                'includeBlankOption' => true
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        )
    )
);
