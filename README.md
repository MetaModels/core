[![Build Status](https://travis-ci.org/MetaModels/core.png)](https://travis-ci.org/MetaModels/core)
[![Latest Version tagged](http://img.shields.io/github/tag/MetaModels/core.svg)](https://github.com/MetaModels/core/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/MetaModels/core.svg)](https://packagist.org/packages/MetaModels/core)
[![Installations via composer per month](http://img.shields.io/packagist/dm/MetaModels/core.svg)](https://packagist.org/packages/MetaModels/core)

MetaModels
==========

So what are MetaModels?
-----------------------

MetaModels are data models you can configure in the Contao Backend. Every MetaModel consists of various attributes of certain data types (attribute types are available as extensions and get registered upon installation).

To present the data on the screen (i.e. website, RSS feed, etc.), you define render settings for the MetaModel which define how the various attribute output shall look like (image sizes, use lightboxes, etc.).

Filtering data in list views needs configuration of filter settings. Filter settings are a very complex topic, as they can be nested (AND/OR conditions i.e.) and be of various nature.

How to use it.
--------------

### Composer

MetaModels and all its dependencies are available through the great [composer extension](https://c-c-a.org/ueber-composer) 

When your Contao Installation is composered, you can simply installing MetaModels by adding following package

* MetaModels/bundle_all 

If you do not need all attributes & filters, feel free to just install the core (MetaModels/core) and grab some filter and attributes of your choice. (Or another [bundle](https://github.com/MetaModels?query=bundle))

### Nightly

Use the nightly package from our project website:

http://now.metamodel.me/

Docs:
-----------

* [The official MetaModel Documentation (en)](http://metamodels.readthedocs.org/en/latest/index.html)
* [The official MetaModel Documentation (de)](http://metamodels.readthedocs.org/de/latest/index.html)

Feel free to contribute the MetaModel Documentation in [EN](https://github.com/MetaModels/docs) or [DE](https://github.com/MetaModels/docs-de)

Ressources:
-----------

* [MetaModels Website](https://now.metamodel.me)
* [MetaModels Contao Wiki [DE]](http://de.contaowiki.org/MetaModels)
* [MetaModels Contao Community Subforum [DE]](https://community.contao.org/de/forumdisplay.php?149-MetaModels)
* [MetaModels IRC Channel on freenode #contao.mm](irc://chat.freenode.net/#contao.mm)

History
------------------
Metamodels are the replacement for the famous Catalog extension for [Contao CMS](https://github.com/contao/core).

As the catalog extension was growing too complex too maintain and most support for extendability was rather hacky, we decided it was time to take everything we learned during the development of Catalog 1 and Catalog 2 to provide you with Catalog 3 which shall be even more flexible and very easy to extend with own classes.

As development did go on nicely, we realized that an easy migration from Catalog to the new version will not very likely be possible both in implementation and learning curve but that this will be a very own and unique extension reensembling only of the name with it's ancestor.
Therefore we rebranded everything as "MetaModels".

Who did it?
-----------

See the [CONTRIBUTORS.md](https://github.com/MetaModels/core/tree/master/CONTRIBUTORS.md) file.

Third Party Licenses:
---------------------

Icons: This software uses the [Fugue Icons](http://p.yusukekamiyamane.com)
