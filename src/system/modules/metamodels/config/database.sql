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
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',

-- name of the MetaModel table.
  `tableName` varchar(64) NOT NULL default '',
-- mode for parent<->child relationship
  `mode` int(1) unsigned NOT NULL default '1',
-- is this MetaModel translatable
  `translated` char(1) NOT NULL default '',
-- if translated, to which languages?
  `languages` text NULL,
-- do we support variants?
  `varsupport` char(1) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `tableName` (`tableName`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_dca`
--

CREATE TABLE `tl_metamodel_dca` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
-- is default?
  `isdefault` char(1) NOT NULL default '',
-- type, either standalone or attached.
  `rendertype` varchar(10) NOT NULL default '',
-- sorting mode.
  `mode` int(4) unsigned NOT NULL default '0',
-- sorting flag.
  `flag` int(4) unsigned NOT NULL default '0',
-- the panel layouts we want to display.
  `panelLayout` blob NULL,
-- parent table (if mode 3,4,6)
  `ptable` varchar(64) NOT NULL default '',
  `backendsection` varchar(255) NOT NULL default '',
  `backendcaption` text NULL,
  `backendicon` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Table `tl_metamodel_dca`
--

CREATE TABLE `tl_metamodel_dcasetting` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',

-- type, either legend or attribute.
  `dcatype` varchar(10) NOT NULL default '',

  `subpalette` int(10) NOT NULL default '0',

  `legendtitle` varchar(255) NOT NULL default '',
  `legendhide` varchar(5) NOT NULL default '',

  `attr_id` int(10) unsigned NOT NULL default '0',
  `tl_class` varchar(64) NOT NULL default '',

  `filterable` char(1) NOT NULL default '',
  `sortable` char(1) NOT NULL default '',
  `searchable` char(1) NOT NULL default '',
-- sorting flag override.
  `flag` int(4) unsigned NOT NULL default '0',
-- mandatory flag
  `mandatory` char(1) NOT NULL default ''
-- alwaysSave flag
  `alwaysSave` char(1) NOT NULL default ''
-- allow html in content.
  `allowHtml` char(1) NOT NULL default '',
-- preserve html tags.
  `preserveTags` char(1) NOT NULL default '',
  `chosen` char(1) NOT NULL default '',
-- decode entities.
  `decodeEntities` char(1) NOT NULL default '',
-- enable rich text editor configuration
  `rte` varchar(64) NOT NULL default '',
-- amount of rows in longtext and tables.
  `rows` int(10) NOT NULL default '0',
-- amount of columns in longtext and tables.
  `cols` int(10) NOT NULL default '0',
-- allow trailing slash, 2 => do nothing, 1 => add one on save, 0 => strip it on save.
  `trailingSlash` char(1) NOT NULL default '2',
-- if true any whitespace character will be replaced by an underscore.
  `spaceToUnderscore` char(1) NOT NULL default '',
-- if true a blank option will be added to the options array.
  `includeBlankOption` char(1) NOT NULL default '',
-- if true, the form will get reloaded when the widget changes
  `submitOnChange` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`)
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
  `colname` varchar(64) NOT NULL default '',
  `type` varchar(64) NOT NULL default '',
  `isvariant` char(1) NOT NULL default '',
-- unique value throughout the metamodel
  `isunique` char(1) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `colname` (`colname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_filter`
--

CREATE TABLE `tl_metamodel_filter` (
  `id` int(10) unsigned NOT NULL auto_increment,
-- corresponding meta model
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
-- human readable name of the filter setting for internal use only.
  `name` varchar(64) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_filtersetting`
--

CREATE TABLE `tl_metamodel_filtersetting` (
  `id` int(10) unsigned NOT NULL auto_increment,
-- corresponding meta model filter setting for hierarchical settings.
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
-- corresponding tl_metamodel_filter
  `fid` int(10) unsigned NOT NULL default '0',
-- filter setting type
  `type` varchar(64) NOT NULL default '',
-- active or disabled
  `enabled` char(1) NOT NULL default '',
-- short comment for describing the purpose
  `comment` varchar(255) NOT NULL default '',
-- corresponding tl_metamodel_attribute
  `attr_id` int(10) unsigned NOT NULL default '0',
-- search language independant (only valid for translation sensitive attributes)
  `all_langs` char(1) NOT NULL default '',
-- allow empty parameters
  `allow_empty` char(1) NOT NULL default '',
-- stop filtering after one rule found soem matches
  `stop_after_match` char(1) NOT NULL default '',
-- simple lookup - url param override.
  `urlparam` varchar(255) NOT NULL default '',
  `predef_param` char(1) NOT NULL default '',
-- custom SQL - query content.
  `customsql` text NULL,
-- items for idlist rule,
  `items` text NULL,
-- label for frontend filter widget.
  `label` blob NULL,
-- template for frontend filter widget.
  `template` varchar(64) NOT NULL default '',
-- include a reset option in FE filter.
  `blankoption` char(1) NOT NULL default '1',
-- display only attached options in FE filters.
  `onlyused` char(1) NOT NULL default '0',
-- display only remaining options in Fe filters.
  `onlypossible` char(1) NOT NULL default '0'
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_rendersettings`
--

CREATE TABLE `tl_metamodel_rendersettings` (
  `id` int(10) unsigned NOT NULL auto_increment,
-- corresponding meta model
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
-- human readable name of the setting for internal use only.
  `name` varchar(64) NOT NULL default '',
-- is default?
  `isdefault` char(1) NOT NULL default '',
-- the template to use.
  `template` varchar(64) NOT NULL default '',
  `format` varchar(255) NOT NULL default '',
-- CSS JS files
   `additionalCss` blob NULL,
   `additionalJs` blob NULL,
-- special options for the template
  `hideEmptyValues` char(1) NOT NULL default '',
  `hideLabels` char(1) NOT NULL default '',
-- the jumpTo page to use.
  `jumpTo` blob NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_rendersetting`
--

CREATE TABLE `tl_metamodel_rendersetting` (
  `id` int(10) unsigned NOT NULL auto_increment,
-- corresponding tl_metamodel_rendersettings
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
-- corresponding tl_metamodel_attribute
  `attr_id` int(10) unsigned NOT NULL default '0',
-- template to use
  `template` varchar(64) NOT NULL default '',
-- active or disabled
  `enabled` char(1) NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_dca_combine`
--

CREATE TABLE `tl_metamodel_dca_combine` (
  `id` int(10) unsigned NOT NULL auto_increment,
-- corresponding tl_metamodel
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `sorting` int(10) unsigned NOT NULL default '0',
-- fe usergroup, if any
  `fe_group` int(10) unsigned NOT NULL default '0',
-- be usergroup, if any (keep signed as admins are -1)
  `be_group` int(10) NOT NULL default '0',
-- corresponding tl_metamodel_dca (palette)
  `dca_id` int(10) unsigned NOT NULL default '0',
-- corresponding tl_metamodel_rendersetting (view)
  `view_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
  KEY `fe_group` (`be_group`),
  KEY `be_group` (`be_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_module`
--

CREATE TABLE `tl_module` (
  `metamodel` int(10) unsigned NOT NULL default '0',
  `metamodel_layout` varchar(64) NOT NULL default '',

-- LIMIT n,m for listings
  `metamodel_use_limit` char(1) NOT NULL default '',
  `metamodel_limit` smallint(5) NOT NULL default '0',
  `metamodel_offset` smallint(5) NOT NULL default '0',
-- filtering and sorting
  `metamodel_sortby` varchar(64) NOT NULL default '',
  `metamodel_sortby_direction` varchar(4) NOT NULL default '',
  `metamodel_filtering` int(10) NOT NULL default '0',
  `metamodel_rendersettings` int(10) NOT NULL default '0',
  `metamodel_noparsing` char(1) NOT NULL default '',
  `metamodel_filterparams` longblob NULL,
  `metamodel_fef_autosubmit` char(1) NOT NULL default '',
  `metamodel_fef_hideclearfilter` char(1) NOT NULL default '',
  `metamodel_fef_params` blob NULL,
  `metamodel_fef_template` varchar(64) NOT NULL default '',
  `metamodel_jumpTo` int(10) unsigned NOT NULL default '0',
  `metamodel_donotindex` char(1) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_content`
--

CREATE TABLE `tl_content` (
  `metamodel` int(10) unsigned NOT NULL default '0',
  `metamodel_layout` varchar(64) NOT NULL default '',

-- LIMIT n,m for listings
  `metamodel_use_limit` char(1) NOT NULL default '',
  `metamodel_limit` smallint(5) NOT NULL default '0',
  `metamodel_offset` smallint(5) NOT NULL default '0',
-- filtering and sorting
  `metamodel_sortby` varchar(64) NOT NULL default '',
  `metamodel_sortby_direction` varchar(4) NOT NULL default '',
  `metamodel_filtering` int(10) NOT NULL default '0',
  `metamodel_rendersettings` int(10) NOT NULL default '0',
  `metamodel_noparsing` char(1) NOT NULL default '',
  `metamodel_filterparams` longblob NULL,
  `metamodel_fef_autosubmit` char(1) NOT NULL default '',
  `metamodel_fef_hideclearfilter` char(1) NOT NULL default '',
  `metamodel_fef_params` blob NULL,
  `metamodel_fef_template` varchar(64) NOT NULL default '',
  `metamodel_jumpTo` int(10) unsigned NOT NULL default '0',
  `metamodel_donotindex` char(1) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;