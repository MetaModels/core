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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_rendersetting'] = array
(
    'config'       => array
    (
        'dataContainer'    => 'General',
        'ptable'           => 'tl_metamodel_rendersettings',
        'switchToEdit'     => true,
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
    'dca_config'   => array
    (
        'data_provider'  => array
        (
            'parent'       => array
            (
                'source' => 'tl_metamodel_rendersettings'
            ),
            'tl_metamodel' => array
            (
                'source' => 'tl_metamodel'
            )
        ),
        'childCondition' => array
        (
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
            array(
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_rendersettings',
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
        'child_list'     => array
        (
            'tl_metamodel_rendersetting' => array
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
    'list'         => array
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
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'],
                'class'      => 'header_add_all rendersetting_add_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            ),
            'all'    => array
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
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ),
            'cut'    => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['cut'],
                'icon'  => 'cut.svg'
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ),
            'toggle' => array
            (
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['toggle'],
                'icon'           => 'visible.svg',
                'toggleProperty' => 'enabled',
            )
        )
    ),
    'palettes'     => array
    (
        '__selector__' => array
        (
            'attr_id'
        )
    ),
    'metapalettes' => array
    (
        'default' => array
        (
            'title' => array
            (
                'attr_id',
                'template',
                'additional_class'
            )
        ),
    ),
    // Fields.
    'fields'       => array
    (
        'id'               => array
        (
            'sql' => 'int(10) unsigned NOT NULL auto_increment'
        ),
        'pid'              => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting'          => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'           => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'attr_id'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['attr_id'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array(
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'submitOnChange'     => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true,
                'tl_class'           => 'w50'
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'template'         => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['template'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'tl_class'           => 'w50',
                'chosen'             => true,
                'includeBlankOption' => true,
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'additional_class' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['additional_class'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'tl_class'  => 'w50',
                'maxlength' => 64,
            ),
            'sql'       => "varchar(64) NOT NULL default ''"
        ),
        'enabled'          => array
        (
            'sql' => "char(1) NOT NULL default ''"
        )
    )
);
