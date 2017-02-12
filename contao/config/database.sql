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
