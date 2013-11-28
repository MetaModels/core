<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/fr/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2013-09-29T00:13:17+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0'] = 'Résumé';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1'] = 'Entrez chaque requêtes SQL qui devrait être executée.<br />⏎
⇥⇥il est requis que cette requête retourne au moins une colomne nommée "id".⏎
⇥⇥';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0'] = 'Example 1<br />requête plaine';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1'] = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>⏎
»»Cela sélectionne tous les identifiants de la table <em>mm_mymetamodel</em> qui ont la valeur <em>page_id=1</em>⏎
»»';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0'] = 'Example 2<br />insert tablename';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0'] = 'Balises d\'insertion';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1'] = 'Les balises sont supportées, mais gardez à l\'esprit que toutes ne pourraient être disponibles
quand le paramètre de filtre est utilisé (par exemple le <em>{{page::id}}</em> est
disponible seulement lorsqu\'utilisée depuis une page du front-office et non depuis un flux RSS).';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0'] = 'Sécuriser les balises';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0'] = 'Source des paramètres<br />';
