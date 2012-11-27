MetaModels
==========

Contao Extension :: The new catalog

Metamodels are the upcoming replacement for the famous Catalog extension.

As the catalog extension was growing too complex too maintain and most support for extendability was rather hacky, we decided it was time to take everything we learned during the development of Catalog 1 and Catalog 2 to provide you with Catalog 3 which shall be even more flexible and very easy to extend with own classes.

As development did go on nicely, we realized that an easy migration from Catalog to the new version will not very likely be possible both in implementation and learning curve but that this will be a very own and unique extension reensembling only of the name with it's ancestor.
Therefore we rebranded everything as "MetaModels".

So what are MetaModels?
=======================

In short:
MetaModels are data models you can configure in the Contao Backend. Every MetaModel consists of various attributes of certain data types (attribute types are available as extensions and get registered upon installation).

To present the data on the screen (i.e. website, RSS feed, etc.), you define render settings for the MetaModel which define how the various attribute output shall look like (image sizes, use lightboxes, etc.).

Filtering data in list views needs configuration of filter settings.
Filter settings are a very complex topic, as they can be nested (AND/OR conditions i.e.) and be of various nature.


Known limitations:
==================

* We have no manual yet. Bummer! :/
* We need some filter module which generates itself from filter settings. Drafts welcome!
* We have not tested it all yet, so please give it a try yourself.


How to use it.
==============

First install the dependencies (see [DEPENDENCIES.md])

Download the code as zip ball or clone the repoisitory.
Below the toplevel src dir, you find the usual Contao 2.X directory layout, upload it to your webspace and update the database in the Contao Backend.

We strongly recommend to install one or more attribute types into your installation along the MetaModels extension.

After that, you can edit the MetaModels in the backend.

Who did it?
===========
See the [CONTRIBUTORS.md] file.


[DEPENDENCIES.md]: https://github.com/MetaModels/core/tree/master/DEPENDENCIES.md
[CONTRIBUTORS.md]: https://github.com/MetaModels/core/tree/master/CONTRIBUTORS.md