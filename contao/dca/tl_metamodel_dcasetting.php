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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting'] = array
(
    'config'                => array
    (
        'dataContainer'    => 'General',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => array
        (
            'keys' => array
            (
                'id' => 'primary',
            ),
        ),
    ),
    'dca_config'            => array
    (
        'data_provider'  => array
        (
            'default'      => array
            (
                'source' => 'tl_metamodel_dcasetting'
            ),
            'parent'       => array
            (
                'source' => 'tl_metamodel_dca'
            ),
            'root'         => array
            (
                'source' => 'tl_metamodel_dca'
            ),
            'tl_metamodel' => array
            (
                'source' => 'tl_metamodel'
            )
        ),
        'childCondition' => array
        (
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
            )
        ),
        'rootEntries'    => array
        (
            'tl_metamodel_dcasetting' => array
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
            'tl_metamodel_dcasetting' => array
            (
                'fields' => array
                (
                    'type',
                    'attr_id',
                    'urlparam',
                    'comment'
                ),
                'format' => '%s %s',
            ),
        ),
    ),
    'list'                  => array
    (
        'sorting'           => array
        (
            'mode'         => 4,
            'fields'       => array('sorting'),
            'panelLayout'  => 'limit',
            'headerFields' => array('name'),
        ),
        'global_operations' => array
        (
            'addall' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'],
                'href'       => 'act=dca_addall',
                'class'      => 'header_add_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'all'    => array
            (
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
        ),
        'operations'        => array
        (
            'edit'       => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ),
            'cut'        => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cut'],
                'href'  => 'act=paste&amp;mode=cut',
                'icon'  => 'cut.gif'
            ),
            'delete'     => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'       => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ),
            'toggle'     => array
            (
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['toggle'],
                'icon'           => 'visible.gif',
                'toggleProperty' => 'published',
            ),
            'conditions' => array
            (
                'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['conditions'],
                'href'    => 'table=tl_metamodel_dcasetting_condition',
                'icon'    => 'system/modules/metamodels/assets/images/icons/dca_condition.png',
                'idparam' => 'pid'
            ),
        )
    ),
    'palettes'              => array
    (
        '__selector__' => array
        (
            'dcatype',
            'attr_id'
        )
    ),
    'metapalettes'          => array
    (
        'default' => array
        (
            'title' => array
            (
                'dcatype'
            ),
        ),
    ),
    'metasubselectpalettes' => array
    (
        'dcatype' => array
        (
            'attribute' => array
            (
                'title'     => array
                (
                    'attr_id'
                ),
                'functions' => array
                (
                    'readonly'
                ),
                'advanced'  => array
                ()
            ),
            'legend'    => array
            (
                'title' => array
                (
                    'legendhide',
                    'legendtitle',
                )
            )
        ),
        'attr_id' => array
        (
            // Core legends:
            // * title
            // * backend
            // * config
            // * advanced
            // Core fields:
            // * tl_class           css class to use in backend.
            // * mandatory          mandatory
            // * chosen
            // * filterable         can be filtered (in backend)
            // * searchable         can be searched (in backend)
            // * allowHtml          do not strip html content.
            // * preserveTags       do not encode html tags.
            // * decodeEntities     do decode HTML entities.
            // * rte                enable richtext editor on this
            // * rows               amount of rows in longtext and tables.
            // * cols               amount of columns in longtext and tables.
            // * trailingSlash      allow trailing slash, 2 => do nothing, 1 => add one on save, 0 => strip it on save.
            // * spaceToUnderscore  if true any whitespace character will be replaced by an underscore.
            // * includeBlankOption if true a blank option will be added to the options array.
        )
    ),
    'fields'                => array
    (
        'id'                 => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid'                => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'            => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'             => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'published'          => array
        (
            'sql' => "char(1) NOT NULL default ''"
        ),
        'dcatype'            => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatype'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array('attribute', 'legend'),
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['dcatypes'],
            'eval'      => array
            (
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
            ),
            'sql'       => "varchar(10) NOT NULL default ''"
        ),
        'attr_id'            => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['attr_id'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'tl_class'           => 'w50',
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'submitOnChange'     => true,
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'tl_class'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['tl_class'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'long wizard',
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'legendhide'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendhide'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50 m12 cbx'
            ),
            'sql'       => "varchar(5) NOT NULL default ''"
        ),
        'legendtitle'        => array
        (
            'label'   => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legendtitle'],
            'exclude' => true,
            'eval'    => array
            (
                'tl_class' => 'clr'
            ),
            'sql'     => "varchar(255) NOT NULL default ''"
        ),
        'mandatory'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['mandatory'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'alwaysSave'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['alwaysSave'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'filterable'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['filterable'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'searchable'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['searchable'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'chosen'             => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['chosen'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50 m12'
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'allowHtml'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['allowHtml'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'preserveTags'       => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['preserveTags'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'decodeEntities'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['decodeEntities'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'rte'                => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rte'],
            'exclude'   => true,
            'inputType' => 'select',
            'default'   => 'tinyMCE',
            'eval'      => array
            (
                'tl_class'           => 'm12',
                'includeBlankOption' => true,
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'rows'               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['rows'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ),
            'sql'       => "int(10) NOT NULL default '0'"
        ),
        'cols'               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['cols'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ),
            'sql'       => "int(10) NOT NULL default '0'"
        ),
        'trailingSlash'      => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => array(0, 1, 2),
            'default'   => 2,
            'reference' => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['trailingSlash_options'],
            'eval'      => array
            (
                'tl_class' => 'w50 clr',
            ),
            'sql'       => "char(1) NOT NULL default '2'"
        ),
        'spaceToUnderscore'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['spaceToUnderscore'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50 m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'includeBlankOption' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['includeBlankOption'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'clr m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'submitOnChange'     => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['submitOnChange'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'clr m12',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        ),
        'readonly'           => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['readonly'],
            'inputType' => 'checkbox',
            'eval'      => array
            (
                'tl_class' => 'w50',
            ),
            'sql'       => "char(1) NOT NULL default ''"
        )
    )
);
