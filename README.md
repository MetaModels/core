[![Build Status](https://github.com/MetaModels/core/actions/workflows/diagnostics.yml/badge.svg)](https://github.com/MetaModels/core/actions)
[![Latest Version tagged](http://img.shields.io/github/tag/MetaModels/core.svg)](https://github.com/MetaModels/core/tags)
[![Latest Version on Packagist](http://img.shields.io/packagist/v/MetaModels/core.svg)](https://packagist.org/packages/MetaModels/core)
[![Installations via composer per month](http://img.shields.io/packagist/dm/MetaModels/core.svg)](https://packagist.org/packages/MetaModels/core)

# MetaModels

Deutsch - s.u.

## So what are MetaModels?

MetaModels are data models you can configure in the Contao Backend. Every MetaModel consists of various attributes of
certain data types (attribute types are available as extensions and get registered upon installation). In addition,
several MetaModel can be linked to each other via relations as single or multiple selections can be linked with each
other. This allows very complex data structures to be mapped in Contao, e.g. for product catalogues, employee lists,
event management, etc. Programming knowledge is generally not necessary for the creation not necessary.

To present the data on the screen (i.e. website, RSS feed, etc.), you define render settings for the MetaModel which
define how the various attribute output shall look like (image sizes, use light boxes, etc.).

Filtering data in list views needs configuration of filter settings. Filter settings are a very complex topic, as they
can be nested (AND/OR conditions i.e.) and be of various nature.

Before you start it is helpful to look at the [MetaModels manual](http://metamodels.readthedocs.org/de/latest/index.html).
There you will find actual information about the usage and the installation.

## Install

You can install MetaModels core with Contao Manager - search "metamodels/core" - or you can use composer

``php web/contao-manager.phar.php composer require metamodels/core``

Then add all the necessary [attributes, filters or MetaModel extensions](https://extensions.contao.org/?q=metamodels).

Please [check the manual to see which versions are available](https://metamodels.readthedocs.io/de/latest/manual/install.html#ubersicht-der-versionen)
and which Contao version works with them. Please also note that the latest version is not always available on Github
and that a newer version may only be available as an "early adopter" with special access - [see Fundrasing](https://now.metamodel.me/en/supporters/fundraising).

For the first evaluation of the possibilities of MetaModels you can also use [metamodels/bundle_start](https://extensions.contao.org/?q=metamodels%252Fbundle_start),
which installs some attributes and filters.

Please do not forget to perform the migration of the database!

## Docs:

* [The official MetaModel Documentation (de)](http://metamodels.readthedocs.org/de/latest/index.html) (Currently the main documentation)
* [The official MetaModel Documentation (en)](http://metamodels.readthedocs.org/en/latest/index.html)


Feel free to contribute the MetaModel Documentation in [EN](https://github.com/MetaModels/docs) or
[DE](https://github.com/MetaModels/docs-de)

## Resources:

* [MetaModels Website](https://now.metamodel.me)
* [MetaModels Contao Wiki [DE]](https://de.contaowiki.org/MetaModels)
* [MetaModels Contao Community Subforum [DE]](https://community.contao.org/de/forumdisplay.php?149-MetaModels)
* [MetaModels Channel on Contao Slack #metamodels](https://contao.slack.com/archives/CKGEBDV60)

## History:

Metamodels are the replacement for the famous Catalog extension for [Contao CMS](https://github.com/contao/core).

As the catalog extension was growing too complex to maintain and most support for extendability was rather hacky, we
decided it was time to take everything we learned during the development of Catalog 1 and Catalog 2 to provide you
with Catalog 3 which shall be even more flexible and very easy to extend with own classes.

As development did go on nicely, we realized that an easy migration from Catalog to the new version will not very
likely be possible both in implementation and learning curve but that this will be a very own and unique extension
resembling only of the name with it's ancestor.
Therefore, we rebranded everything as "MetaModels".

## Who did it?

See the [CONTRIBUTORS.md](https://github.com/MetaModels/core/tree/master/CONTRIBUTORS.md) file.

## Third Party Licenses:

Icons: This software uses the [Fugue Icons](http://p.yusukekamiyamane.com)

## Deutsch

## Was ist MetaModells?

MetaModels sind Datenmodelle, die Sie im Contao Backend konfigurieren können. Jedes MetaModel besteht aus verschiedenen
Attributen von bestimmten Datentypen wie Text, Checkbox, Dateien (Attributtypen sind als Erweiterungen verfügbar und
werden bei der Installation registriert). Zudem können mehrere MetaModel über Relationen als Einzel- oder Mehrfachauswahl
miteinander verknüpft werden. Damit lassen sich sehr komplexe Datenstrukturen in Contao abbilden wie z. B. für
Produktkataloge, Mitarbeiterlisten, Eventmanagements usw. Programmierkenntnisse sind in der Regel für die Erstellung
nicht notwendig.

Um die Daten auf dem Bildschirm (z. B. Website, RSS-Feed, etc.) darzustellen, definieren Sie Rendereinstellungen für
das MetaModel, die festlegen, wie die verschiedenen Attributausgaben aussehen sollen (Bildgrößen, Verwendung von
Lightboxes usw.).

Das Filtern von Daten in Listenansichten erfordert die Konfiguration von Filtereinstellungen. Filtereinstellungen sind
ein sehr komplexes Thema, da sie verschachtelt (z.B. AND/OR-Bedingungen) und von unterschiedlicher Natur sein können.

Vor dem Start ist es hilfreich, einen Blick in das [MetaModels Handbuch](http://metamodels.readthedocs.org/de/latest/index.html)
zu werfen. Dort findet man aktuelle Informationen über die Verwendung, Installation und Tipps&Tricks.

## Installation

MetaModels Core kann mit dem Contao Manager installiert werden - suchen Sie nach "metamodels/core" - oder Sie können
composer verwenden

``php public/contao-manager.phar.php composer require metamodels/core``

Dann fügen Sie alle notwendigen [Attribute, Filter oder MetaModel-Erweiterungen](https://extensions.contao.org/?q=metamodels)
hinzu, die Sie für ihr Model benötigen.

Bitte [schauen Sie im Handbuch nach, welche Versionen verfügbar sind](https://metamodels.readthedocs.io/de/latest/manual/install.html#ubersicht-der-versionen)
und welche Contao-Version mit ihnen funktioniert. Bitte beachten Sie auch, dass nicht immer die neueste Version auf
Github verfügbar ist und dass eine neuere Version möglicherweise nur als "Early Adopter" mit speziellem Zugang
erhältlich ist - [siehe Fundrasing](https://now.metamodel.me/en/supporters/fundraising).

Für eine erste Evaluierung der Möglichkeiten von MetaModels kann man auch [metamodels/bundle_start](https://extensions.contao.org/?q=metamodels%252Fbundle_start)
verwenden, das einige Attribute und Filter installiert.

Bitte vergessen Sie nicht, die Migration der Datenbank durchzuführen!
