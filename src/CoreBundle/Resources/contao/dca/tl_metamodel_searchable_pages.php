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
 * @author     Tim Becker <please.tim@metamodel.me>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Becker <tim@westwerk.ac>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_searchable_pages'] = array
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
                'id'  => 'primary',
                'pid' => 'index',
            ),
        )
    ),
    'dca_config'   => array
    (
        'data_provider'  => array
        (
            'parent' => array
            (
                'source' => 'tl_metamodel'
            )
        ),
        'childCondition' => array
        (
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
            )
        ),
    ),
    'list'         => array
    (
        'sorting'           => array
        (
            'mode'         => 4,
            'fields'       => array('name'),
            'panelLayout'  => 'filter,limit',
            'headerFields' => array('name'),
            'flag'         => 1,
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
            'edit'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
            ),
            'copy'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
            ),
            'delete' => array
            (
                'label'      => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            ),
            'show'   => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ),
            'toggle' => array
            (
                'label'          => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['toggle'],
                'icon'           => 'visible.gif',
                'toggleProperty' => 'published'
            )
        )
    ),
    'metapalettes' => array
    (
        'default' => array
        (
            'title'   => array
            (
                'name',
            ),
            'general' => array
            (
                'filter',
                'filterparams',
                'rendersetting',
            ),
        )
    ),
    'fields'       => array
    (
        'id'            => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid'           => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'tstamp'        => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'name'          => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => array
            (
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class'  => 'w50'
            ),
            'sql'       => "varchar(255) NOT NULL default ''"
        ),
        'filter'        => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filter'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'includeBlankOption' => true,
                'chosen'             => true,
                'submitOnChange'     => true
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'filterparams'  => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filterparams'],
            'exclude'   => true,
            'inputType' => 'mm_subdca',
            'eval'      => array
            (
                'tl_class'   => 'clr m12',
                'flagfields' => array
                (
                    'use_get' => array
                    (
                        'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['filterparams'],
                        'inputType' => 'checkbox'
                    ),
                ),
            ),
            'sql'       => "longblob NULL"
        ),
        'rendersetting' => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_metamodel_searchable_pages']['rendersetting'],
            'exclude'   => true,
            'inputType' => 'select',
            'eval'      => array
            (
                'includeBlankOption' => true,
                'mandatory'          => true,
                'chosen'             => true
            ),
            'sql'       => "int(10) unsigned NOT NULL default '0'"
        ),
        'published'     => array
        (
            'sql' => "char(1) NOT NULL default ''"
        )
    )
);
