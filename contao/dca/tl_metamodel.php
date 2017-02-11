<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <please.tim@metamodel.me>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$this->loadLanguageFile('languages');

$GLOBALS['TL_DCA']['tl_metamodel'] = array
(
    'config'          => array
    (
        'dataContainer'    => 'General',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => array
        (
            'keys' => array
            (
                'id'        => 'primary',
                'tableName' => 'index',
            ),
        ),
    ),
    'dca_config'      => array
    (
        'data_provider'  => array
        (
            'default' => array
            (
                'source' => 'tl_metamodel'
            ),

            'tl_metamodel_attribute' => array
            (
                'source' => 'tl_metamodel_attribute'
            ),

            'tl_metamodel_rendersettings' => array
            (
                'source' => 'tl_metamodel_rendersettings'
            ),
            'tl_metamodel_rendersetting'  => array
            (
                'source' => 'tl_metamodel_rendersetting'
            ),

            'tl_metamodel_dca'                  => array
            (
                'source' => 'tl_metamodel_dca'
            ),
            'tl_metamodel_dca_sortgroup'        => array
            (
                'source' => 'tl_metamodel_dca_sortgroup'
            ),
            'tl_metamodel_dcasetting'           => array
            (
                'source' => 'tl_metamodel_dcasetting'
            ),
            'tl_metamodel_dcasetting_condition' => array
            (
                'source' => 'tl_metamodel_dcasetting_condition'
            ),

            'tl_metamodel_searchable_pages' => array
            (
                'source' => 'tl_metamodel_searchable_pages'
            ),

            'tl_metamodel_filter'        => array
            (
                'source' => 'tl_metamodel_filter'
            ),
            'tl_metamodel_filtersetting' => array
            (
                'source' => 'tl_metamodel_filtersetting'
            ),

            'tl_metamodel_dca_combine' => array
            (
                'source' => 'tl_metamodel_dca_combine'
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
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_rendersettings',
                'setOn'   => array
                (
                    array(
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
                'from'   => 'tl_metamodel_rendersettings',
                'to'     => 'tl_metamodel_rendersetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
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
            ),

            array
            (
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca',
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
            array
            (
                'from'   => 'tl_metamodel_dca',
                'to'     => 'tl_metamodel_dca_sortgroup',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
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
                ),
            ),
            array
            (
                'from'   => 'tl_metamodel_dca',
                'to'     => 'tl_metamodel_dcasetting',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
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
            ),
            array
            (
                'from'   => 'tl_metamodel_dcasetting',
                'to'     => 'tl_metamodel_dcasetting_condition',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'settingId',
                        'from_field' => 'id',
                    )
                ),
                'filter' => array
                (
                    array
                    (
                        'local'     => 'settingId',
                        'remote'    => 'id',
                        'operation' => '=',
                    ),
                )
            ),

            array
            (
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_searchable_pages',
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

            array
            (
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_filter',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
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
                ),
            ),
            array(
                'from'   => 'tl_metamodel_filter',
                'to'     => 'tl_metamodel_filtersetting',
                'setOn'  => array
                (
                    array
                    (
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

            array
            (
                'from'   => 'tl_metamodel',
                'to'     => 'tl_metamodel_dca_combine',
                'setOn'  => array
                (
                    array
                    (
                        'to_field'   => 'pid',
                        'from_field' => 'id',
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
                ),
            ),
        ),
    ),
    'list'            => array
    (
        'sorting'           => array
        (
            'mode'        => 2,
            'fields'      => array(),
            'flag'        => 1,
            'panelLayout' => 'sort,limit'
        ),
        'label'             => array
        (
            'fields' => array('name'),
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
            ),
        ),
        'operations'        => array
        (
            'edit'             => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ),
            'cut'              => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif'
            ),
            'delete'           => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'             => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),
            'fields'           => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['fields'],
                'href'    => 'table=tl_metamodel_attribute',
                'icon'    => 'system/modules/metamodels/assets/images/icons/fields.png',
                'idparam' => 'pid'
            ),
            'rendersettings'   => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['rendersettings'],
                'href'    => 'table=tl_metamodel_rendersettings',
                'icon'    => 'system/modules/metamodels/assets/images/icons/rendersettings.png',
                'idparam' => 'pid'
            ),
            'dca'              => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['dca'],
                'href'    => 'table=tl_metamodel_dca',
                'icon'    => 'system/modules/metamodels/assets/images/icons/dca.png',
                'idparam' => 'pid'
            ),
            'searchable_pages' => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['searchable_pages'],
                'href'    => 'table=tl_metamodel_searchable_pages',
                'icon'    => 'system/modules/metamodels/assets/images/icons/searchable_pages.png',
                'idparam' => 'pid'
            ),
            'filter'           => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['filter'],
                'href'    => 'table=tl_metamodel_filter',
                'icon'    => 'system/modules/metamodels/assets/images/icons/filter.png',
                'idparam' => 'pid'
            ),
            'dca_combine'      => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['dca_combine'],
                'href'    => 'table=tl_metamodel_dca_combine&act=edit',
                'icon'    => 'system/modules/metamodels/assets/images/icons/dca_combine.png',
                'idparam' => 'pid'
            ),
        )
    ),
    'metapalettes'    => array
    (
        'default' => array
        (
            'title'      => array
            (
                'name',
                'tableName'
            ),
            'translated' => array
            (
                ':hide',
                'translated'
            ),
            'advanced'   => array
            (
                ':hide',
                'varsupport'
            ),
        )
    ),
    'metasubpalettes' => array
    (
        'translated' => array
        (
            'languages'
        ),
    ),
    'fields'          => array
    (
        'id'         => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp'     => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'    => array
        (
            'label'   => &$GLOBALS['TL_LANG']['tl_metamodel']['sorting'],
            'sorting' => true,
            'flag'    => 11,
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ),
        'name'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['name'],
            'sorting'   => true,
            'flag'      => 3,
            'length'    => 1,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 64,
                'tl_class'  => 'w50',
                'unique'    => true
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'tableName'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['tableName'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 64,
                'doNotCopy' => true,
                'tl_class'  => 'w50'
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'mode'       => array
        (
            'sql' => "int(1) unsigned NOT NULL default '1'"
        ),
        'translated' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['translated'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'       => 'clr',
                'submitOnChange' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'languages'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['languages'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => array
            (
                'columnFields' => array
                (
                    'langcode'   => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_langcode'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'eval'      => array
                        (
                            'style'  => 'width:470px',
                            'chosen' => 'true'
                        ),
                    ),
                    'isfallback' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['languages_isfallback'],
                        'exclude'   => true,
                        'inputType' => 'checkbox',
                        'eval'      => array
                        (
                            'style' => 'width:50px',
                        ),
                    ),
                ),
            ),
            'sql'       => "text NULL"
        ),
        'varsupport' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel']['varsupport'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class'       => 'clr',
                'submitOnChange' => true
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
    ),
);
