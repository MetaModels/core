-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the Contao    *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************


-- --------------------------------------------------------

-- 
-- Table `tl_metamodel`
-- 

CREATE TABLE `tl_metamodel` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',

-- name of the MetaModel table.
  `tableName` varchar(64) NOT NULL default '',
-- mode for parent<->child relationship
  `mode` int(1) unsigned NOT NULL default '1',
-- parent table
  `ptable` varchar(64) NOT NULL default '',
-- is this MetaModel translatable
  `translated` char(1) NOT NULL default '',
-- if translated, to which languages?
  `languages` text NULL,
-- do we support variants?
  `varsupport` char(1) NOT NULL default '',

  `backendsection` varchar(64) NOT NULL default '',


  `format` text NULL,
  `addImage` char(1) NOT NULL default '',
  `singleSRC` varchar(255) NOT NULL default '',
  `size` varchar(255) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `tableName` (`tableName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_metamodel_attribute`
-- 

CREATE TABLE `tl_metamodel_attribute` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',

  `name` text NULL,
  `description` text NULL,
  `colName` varchar(64) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `isvariant` char(1) NOT NULL default '',


  `titleField` char(1) NOT NULL default '',
  `aliasTitle` varchar(255) NOT NULL default '',
  `filteredField` char(1) NOT NULL default '',
  `insertBreak` char(1) NOT NULL default '',
  `legendTitle` varchar(255) NOT NULL default '',
  `legendHide` char(1) NOT NULL default '',
  `width50` char(1) NOT NULL default '',
  `sortingField` char(1) NOT NULL default '',
  `groupingMode` int(10) NOT NULL default '0',
  `searchableField` char(1) NOT NULL default '',
  `parentCheckbox` varchar(255) NOT NULL default '',
  `mandatory` char(1) NOT NULL default '',
  `includeBlankOption` char(1) NOT NULL default '',
  `parentFilter` varchar(255) NOT NULL default '',
  `calcValue` text NULL,
  `defValue` varchar(255) NOT NULL default '',
  `minValue` int(10) NULL default NULL,
  `maxValue` int(10) NULL default NULL,
  `format` char(1) NOT NULL default '',
  `formatFunction` varchar(6) NOT NULL default '',
  `formatStr` varchar(255) NOT NULL default '',
  `formatPrePost` varchar(255) NOT NULL default '',
  `unique` char(1) NOT NULL default '',
  `rte` char(1) NOT NULL default '',
  `rte_editor` varchar(255) NOT NULL default 'tinyMCE',
  `allowHtml` char(1) NOT NULL default '',
  `textHeight` int(10) unsigned NOT NULL default '0',
  `itemTable` varchar(255) NOT NULL default '',
  `itemTableValueCol` varchar(255) NOT NULL default '',
  `itemSortCol` varchar(255) NOT NULL default '',
  `limitItems` char(1) NOT NULL default '',
  `items` text NULL,
  `childrenSelMode` varchar(64) NOT NULL default '',
  `treeMinLevel` int(10) NULL default NULL,
  `treeMaxLevel` int(10) NULL default NULL,
  `itemFilter` text NULL,
  `includeTime` char(1) NOT NULL default '',
  `multiple` char(1) NOT NULL default '',
  `sortBy` varchar(32) NOT NULL default '',
  `showLink` char(1) NOT NULL default '',
  `showImage` char(1) NOT NULL default '',
  `imageSize` varchar(255) NOT NULL default '',
  `customFiletree` char(1) NOT NULL default '',
  `uploadFolder` varchar(255) NOT NULL default '',
  `validFileTypes` varchar(255) NOT NULL default '',
  `filesOnly` char(1) NOT NULL default '',
  `editGroups` blob NULL,
  `allowedHosts` blob NULL,

  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `colName` (`colName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_user_group`
-- 

CREATE TABLE `tl_user_group` (
  `metamodels` blob NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

-- 
-- Table `tl_user`
-- 

CREATE TABLE `tl_user` (
  `metamodels` blob NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;