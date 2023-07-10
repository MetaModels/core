<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_metamodel_dcasetting'] = [
    'config'                => [
        'dataContainer'    => 'General',
        'switchToEdit'     => true,
        'enableVersioning' => false,
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ],
        ],
    ],
    'dca_config'            => [
        'data_provider'  => [
            'root'         => [
                'source' => 'tl_metamodel_dcasetting'
            ],
            'parent'       => [
                'source' => 'tl_metamodel_dca'
            ],
            'tl_metamodel' => [
                'source' => 'tl_metamodel'
            ]
        ],
        'childCondition' => [
            [
                'from'    => 'tl_metamodel_dca',
                'to'      => 'tl_metamodel_dcasetting',
                'setOn'   => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                ],
                'filter'  => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ],
            [
                'from'    => 'tl_metamodel',
                'to'      => 'tl_metamodel_dca',
                'setOn'   => [
                    [
                        'to_field'   => 'pid',
                        'from_field' => 'id',
                    ],
                ],
                'filter'  => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ],
                'inverse' => [
                    [
                        'local'     => 'pid',
                        'remote'    => 'id',
                        'operation' => '=',
                    ],
                ]
            ]
        ],
        'rootEntries'    => [
            'tl_metamodel_dcasetting' => [
                'setOn'  => [
                    [
                        'property' => 'pid',
                        'value'    => '0'
                    ],
                ],
                'filter' => [
                    [
                        'property'  => 'pid',
                        'operation' => '=',
                        'value'     => '0'
                    ]
                ]
            ]
        ],
        'child_list'     => [
            'tl_metamodel_dcasetting' => [
                'fields' => [
                    'type',
                    'attr_id',
                    'urlparam',
                    'comment'
                ],
                'format' => '%s %s',
            ],
        ],
    ],
    'list'                  => [
        'sorting'           => [
            'mode'         => 4,
            'fields'       => ['sorting'],
            'panelLayout'  => 'limit',
            'headerFields' => ['name'],
        ],
        'global_operations' => [
            'addall' => [
                'label'       => 'addall.label',
                'description' => 'addall.description',
                'class'       => 'header_add_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
            'all'    => [
                'label'       => 'all.label',
                'description' => 'all.description',
                'href'        => 'act=select',
                'class'       => 'header_edit_all',
                'attributes'  => 'onclick="Backend.getScrollOffset();"'
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label'       => 'edit.label',
                'description' => 'edit.description',
                'href'        => 'act=edit',
                'icon'        => 'edit.svg'
            ],
            'cut'        => [
                'label'       => 'cut.label',
                'description' => 'cut.description',
                'href'        => 'act=paste&amp;mode=cut',
                'icon'        => 'cut.svg'
            ],
            'delete'     => [
                'label'       => 'delete.label',
                'description' => 'delete.description',
                'href'        => 'act=delete',
                'icon'        => 'delete.svg',
                'attributes'  => 'onclick="if (!confirm(this.dataset.msgConfirm)) return false; Backend.getScrollOffset();"',
            ],
            'show'       => [
                'label'       => 'show.label',
                'description' => 'show.description',
                'href'        => 'act=show',
                'icon'        => 'show.svg'
            ],
            'toggle'     => [
                'label'          => 'toggle.label',
                'description'    => 'toggle.description',
                'icon'           => 'visible.svg',
                'toggleProperty' => 'published',
            ],
            'conditions' => [
                'label'       => 'conditions.label',
                'description' => 'conditions.description',
                'href'        => 'table=tl_metamodel_dcasetting_condition',
                'icon'        => 'bundles/metamodelscore/images/icons/dca_condition.png',
                'idparam'     => 'pid'
            ],
        ]
    ],
    'palettes'              => [
        '__selector__' => [
            'dcatype',
            'attr_id'
        ]
    ],
    'metapalettes'          => [
        'default' => [
            'title' => [
                'dcatype'
            ],
        ],
    ],
    'metasubselectpalettes' => [
        'dcatype' => [
            'attribute' => [
                'title'     => [
                    'attr_id'
                ],
                'functions' => [
                    'readonly'
                ],
                'advanced'  => []
            ],
            'legend'    => [
                'title' => [
                    'legendhide',
                    'legendtitle',
                ]
            ]
        ],
        'attr_id' => [
            // Core legends:
            // * title
            // * functions
            // * advanced
            // Core fields:
            // * tl_class           css class to use in backend.
            // * be_template        template for backend widget.
            // * mandatory          mandatory.
            // * alwaysSave         always save.
            // * filterable         can be filtered (in backend).
            // * searchable         can be searched (in backend).
            // * chosen             chosen for select.
            // * allowHtml          do not strip html content.
            // * preserveTags       do not encode html tags.
            // * decodeEntities     do decode HTML entities.
            // * rte                enable richtext editor on this
            // * rows               amount of rows in longtext and tables.
            // * cols               amount of columns in longtext and tables.
            // * trailingSlash      allow trailing slash, 2 => do nothing, 1 => add one on save, 0 => strip it on save.
            // * spaceToUnderscore  if true any whitespace character will be replaced by an underscore.
            // * includeBlankOption if true a blank option will be added to the options array.
            // * submitOnChange     submit on change value.
            // * readonly           readonly.
        ]
    ],
    'fields'                => [
        'id'                 => [
            'label' => 'id.label',
            'sql'   => 'int(10) unsigned NOT NULL auto_increment'
        ],
        'pid'                => [
            'label' => 'pid.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'sorting'            => [
            'label' => 'sorting.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'tstamp'             => [
            'label' => 'tstamp.label',
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'published'          => [
            'label'   => 'published.label',
            'default' => 1,
            'sql'     => "char(1) NOT NULL default '1'"
        ],
        'dcatype'            => [
            'label'       => 'dcatype.label',
            'description' => 'dcatype.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => ['attribute', 'legend'],
            'reference'   => [
                'legend'    => 'dcatypes.legend',
                'attribute' => 'dcatypes.attribute',
            ],
            'eval'        => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
                'submitOnChange'     => true,
            ],
            'sql'         => "varchar(10) NOT NULL default ''"
        ],
        'attr_id'            => [
            'label'       => 'attr_id.label',
            'description' => 'attr_id.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'eval'        => [
                'tl_class'           => 'w50',
                'doNotSaveEmpty'     => true,
                'alwaysSave'         => true,
                'includeBlankOption' => true,
                'mandatory'          => true,
                'submitOnChange'     => true,
            ],
            'sql'         => "int(10) unsigned NOT NULL default '0'"
        ],
        'tl_class'           => [
            'label'       => 'tl_class.label',
            'description' => 'tl_class.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'default'     => 'w50',
            'eval'        => [
                'tl_class'   => 'clr w50',
                'helpwizard' => true,
            ],
            'explanation' => 'tl_class',
            'sql'         => ['type' => 'string', 'length' => 64, 'default' => 'w50']
        ],
        'be_template'        => [
            'label'       => 'be_template.label',
            'description' => 'be_template.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'sql'         => 'varchar(255) NOT NULL default \'\'',
            'eval'        => [
                'includeBlankOption' => true,
                'tl_class'           => 'clr w50',
                'chosen'             => 'true'
            ]
        ],
        'legendhide'         => [
            'label'       => 'legendhide.label',
            'description' => 'legendhide.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12'
            ],
            'sql'         => "varchar(5) NOT NULL default ''"
        ],
        'legendtitle'        => [
            'label'       => 'legendtitle.label',
            'description' => 'legendtitle.description',
            'exclude'     => true,
            'eval'        => [
                'tl_class' => 'clr'
            ],
            'sql'         => 'text NULL'
        ],
        'mandatory'          => [
            'label'       => 'mandatory.label',
            'description' => 'mandatory.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'alwaysSave'         => [
            'label'       => 'alwaysSave.label',
            'description' => 'alwaysSave.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'filterable'         => [
            'label'       => 'filterable.label',
            'description' => 'filterable.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'searchable'         => [
            'label'       => 'searchable.label',
            'description' => 'searchable.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'chosen'             => [
            'label'       => 'chosen.label',
            'description' => 'chosen.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12'
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'allowHtml'          => [
            'label'       => 'allowHtml.label',
            'description' => 'allowHtml.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        =>
                [
                    'tl_class' => 'w50 cbx m12',
                ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'preserveTags'       => [
            'label'       => 'preserveTags.label',
            'description' => 'preserveTags.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'decodeEntities'     => [
            'label'       => 'decodeEntities.label',
            'description' => 'decodeEntities.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'rte'                => [
            'label'       => 'rte.label',
            'description' => 'rte.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'default'     => 'tinyMCE',
            'eval'        => [
                'tl_class'           => 'w50',
                'includeBlankOption' => true,
            ],
            'sql'         => "varchar(64) NOT NULL default 'tinyMCE'"
        ],
        'rows'               => [
            'label'       => 'rows.label',
            'description' => 'rows.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        =>
                [
                    'tl_class' => 'w50',
                    'rgxp'     => 'digit'
                ],
            'sql'         => "int(10) NOT NULL default '0'"
        ],
        'cols'               => [
            'label'       => 'cols.label',
            'description' => 'cols.description',
            'exclude'     => true,
            'inputType'   => 'text',
            'eval'        => [
                'tl_class' => 'w50',
                'rgxp'     => 'digit'
            ],
            'sql'         => "int(10) NOT NULL default '0'"
        ],
        'trailingSlash'      => [
            'label'       => 'trailingSlash.label',
            'description' => 'trailingSlash.description',
            'exclude'     => true,
            'inputType'   => 'select',
            'options'     => [0, 1, 2],
            'default'     => 2,
            'reference'   => [
                '0' => 'trailingSlash_options.0',
                '1' => 'trailingSlash_options.1',
                '2' => 'trailingSlash_options.2',
            ],
            'eval'        => [
                'tl_class' => 'clr w50',
            ],
            'sql'         => "char(1) NOT NULL default '2'"
        ],
        'spaceToUnderscore'  => [
            'label'       => 'spaceToUnderscore.label',
            'description' => 'spaceToUnderscore.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'includeBlankOption' => [
            'label'       => 'includeBlankOption.label',
            'description' => 'includeBlankOption.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'default'     => '1',
            'eval'        => [
                'tl_class' => 'clr w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default '1'"
        ],
        'submitOnChange'     => [
            'label'       => 'submitOnChange.label',
            'description' => 'submitOnChange.description',
            'exclude'     => true,
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'clr w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ],
        'readonly'           => [
            'label'       => 'readonly.label',
            'description' => 'readonly.description',
            'inputType'   => 'checkbox',
            'eval'        => [
                'tl_class' => 'w50 cbx m12',
            ],
            'sql'         => "char(1) NOT NULL default ''"
        ]
    ]
];
