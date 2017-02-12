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
-- skip the filter from the search of id list for generating the widgets.
  `skipfilteroptions` char(1) NOT NULL default '0'
-- default value for filter
  `defaultid` varchar(255) NOT NULL default '',
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
-- additional css class to use
  `additional_class` varchar(64) NOT NULL default '',
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
  KEY `fe_group` (`fe_group`),
  KEY `be_group` (`be_group`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table `tl_metamodel_searchable_pages`
--

CREATE TABLE `tl_metamodel_searchable_pages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `tstamp` int(10) unsigned NOT NULL default '0',
  `published` char(1) NOT NULL default '',
-- human readable description of the searchable page setting for internal use only.
  `name` varchar(255) NOT NULL default '',
-- corresponding tl_metamodel_filtersetting
  `filter` int(10) unsigned NOT NULL default '0',
-- filter overwrite
  `filterparams` longblob NULL,
-- corresponding tl_metamodel_dca
  `rendersetting` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`),
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
  `metamodel_sort_override` char(1) NOT NULL default '',
  `metamodel_filtering` int(10) NOT NULL default '0',
  `metamodel_rendersettings` int(10) NOT NULL default '0',
  `metamodel_noparsing` char(1) NOT NULL default '',
  `metamodel_filterparams` longblob NULL,
  `metamodel_fef_autosubmit` char(1) NOT NULL default '',
  `metamodel_fef_hideclearfilter` char(1) NOT NULL default '',
  `metamodel_fef_params` blob NULL,
  `metamodel_fef_template` varchar(64) NOT NULL default '',
  `metamodel_jumpTo` int(10) unsigned NOT NULL default '0',
  `metamodel_donotindex` char(1) NOT NULL default '',
  `metamodel_available_values` char(1) NOT NULL default '',
-- meta information
  `metamodel_meta_title` varchar(64) NOT NULL default '',
  `metamodel_meta_description` varchar(64) NOT NULL default ''
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
  `metamodel_sort_override` char(1) NOT NULL default '',
  `metamodel_filtering` int(10) NOT NULL default '0',
  `metamodel_rendersettings` int(10) NOT NULL default '0',
  `metamodel_noparsing` char(1) NOT NULL default '',
  `metamodel_filterparams` longblob NULL,
  `metamodel_fef_autosubmit` char(1) NOT NULL default '',
  `metamodel_fef_hideclearfilter` char(1) NOT NULL default '',
  `metamodel_fef_params` blob NULL,
  `metamodel_fef_template` varchar(64) NOT NULL default '',
  `metamodel_jumpTo` int(10) unsigned NOT NULL default '0',
  `metamodel_donotindex` char(1) NOT NULL default '',
  `metamodel_available_values` char(1) NOT NULL default '',
-- meta information
  `metamodel_meta_title` varchar(64) NOT NULL default '',
  `metamodel_meta_description` varchar(64) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
