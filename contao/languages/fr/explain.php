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
 * last-updated: 2015-08-04T04:10:59+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Résumé';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Entrez chaque requêtes SQL qui devrait être executée.<br />⏎
⇥⇥il est requis que cette requête retourne au moins une colomne nommée "id".⏎
⇥⇥';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Example 1<br />requête plaine';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>⏎
»»Cela sélectionne tous les identifiants de la table <em>mm_mymetamodel</em> qui ont la valeur <em>page_id=1</em>⏎
»»';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Example 2<br />insert tablename';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
C\'est simplement le même que l\'exemple 1 mais le nom du tableau du MetaModel actuel (ex.: le <em>mm_mymetamodel</em> ci-dessus) sera injecté dans la requête.

';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Balises d\'insertion';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Les balises sont supportées, mais gardez à l\'esprit que toutes ne pourraient être disponibles
quand le paramètre de filtre est utilisé (par exemple le <em>{{page::id}}</em> est
disponible seulement lorsqu\'utilisée depuis une page du front-office et non depuis un flux RSS).';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Sécuriser les balises';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'Les balises sécurisée sont comme les balises standard, mais leur valeur s\'échappe dans les requêtes.<br />
Ainsi vous devriez mieux utilisez les équivalent sécurisés à moins de savoir exactement ce que vous faites.<br />
La notation est du type:
<pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Source des paramètres<br />';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'Les sources de paramètre ont un agencement:
<pre>{{param::[source]?[query string]}}</pre>
Ou la source peut être au choix:
<ul>
<li><strong>get</strong> - chaine de requête HTTP GET</li>
<li><strong>post</strong> - champs HTTP POST</li>
<li><strong>session</strong> - n\'importe quel champs de la session Contao</li>
<li><strong>filter</strong> - n\'importe quel paramètre de filtre transmis (pour partager les paramètres dans les propriétés de filtres).</li>
</ul>
La chaîne de requête est construite comme une chaîne de requête HTTP normale "name=value" pairs which are combined using the & char and must at least contain the field "name".
Une ou plusieurs de clés optionnelles suivante peuvent être utilisées:
<ul>
<li><strong>default</strong> - la valeur par défaut à utiliser si aucune valeur n\'est disponible.
/li>
<li><strong>aggregate</strong> - soit "list" ou "set"</li>
<li><strong>key</strong> - paramétrer à 1 pour lire les clés du tableau (nécessite l\'option aggregate).</li>
<li><strong>recursive</strong> - paramétrer à 1 pour lire les recursif du tableau (nécessite l\'option aggregate).</li>
</ul>';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Example 3<br />Utilise des sources de paramètres de filtre complexe';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT id
FROM {{table}}
WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
<p>
Identique à l\'exemple 2 mais nous utilisons maintenant un paramètre de la chaîne de "requête".
</p>
<p>
Imaginez l\'URL de la page tel que : "http://example.org/list/category/demo.html"<br />
La requête résultante serait alors : "SELECT id FROM mm_demo WHERE catname=\'demo\'"
</p>
<p>
Si l\'URL devait être : "http://example.org/list.html",<br />
la requête résultante serait alors : "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
</p>';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['0'] = 'OR';

