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
