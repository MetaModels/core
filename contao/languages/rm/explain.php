<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/rm/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2015-08-04T04:10:59+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Resumaziun';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Tippa ina dumonda dad SQL che duai vegnir exequida.<br />
		Igl è necessari ch\'il resultat da questa dumonda cuntegna almain ina colonna cun il num "id".
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Exempel 1<br />dumonda simpla';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
		Quai tscherna tut las IDs da la tabella <em>mm_mymetamodel</em> che han la valur <em>page_id=1</em>
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Exempel 2<br />inserir num da tabella';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
		Quai è prest il medem sco en l\'exempel 1, be vegn il num da la tabella dal MetaModel actual (il <em>mm_mymetamodel</em> en l\'exempel sura) inserì en la dumonda.
		
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Insert-tags';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Insert-tags vegnan sustegnids. Ma ponderesche che betg tut ils tags pudessan star a disopsiziun
		sche la configuraziun dal filter vegn utilisada (p.ex. stat <em>{{page::id}}</em> 
		be a disposiziun sch\'i vegn utilisà sin ina pagina e betg en in feed RSS).';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Insert-tags segirs';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'Insert-tags segir èn sco normals insert-tags, ma lur valur vegn codada en la dumonda.<br />
		Perquai èsi probabel pli segir dad utilisar l\'equivalent segir sche ti n\'ès betg dal tut segir tge che ti fas.<br />
		La notaziun è la suandanta:
		<pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Funtaunas da parameters<br />';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'Funtaunas da parameters han in layout da funtauna da:
		<pre>{{param::[funtauna]?[dumonda]}}</pre>
		Nua che funtauna po esser in da:
		<ul>
		<li><strong>get</strong> - Dumonda da HTTP GET</li>
		<li><strong>post</strong> - Champs per HTTP POST</li>
		<li><strong>session</strong> - in champ da la sessiun da Contao</li>
		<li><strong>filter</strong> - in dals parameters da filter surdads (per cundivider parameters tranter configuraziuns da filters).</li>
		</ul>
		Il string da dumonda sa cumpona sco in a normala dumonda da HTTP da pèrs da "num=valur" che èn cumbinads entras il caracter & e ston almain cuntegnair il champ "name".
		In u plirs da las duandantas clavs po plinavant vegnir utilisada:
		<ul>
		<li><strong>default</strong> - la valur da standard dad utilisar, sch\'ina tala valur è disponibla.</li>
		<li><strong>aggregate</strong> - u "list" u "set"</li>
		<li><strong>key</strong> - metter sin 1 per leger la clav dad arrays (dovra aggregate set).</li>
		<li><strong>recursive</strong> - metter sin 1 per leger recursivamain ils arrays (dovra aggregate set).</li>
		</ul>
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Exempel 3<br />utilisar funtaunas da parameters cumplexas';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT id
	FROM {{table}}
	WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
		<p>
		Quai è il medem sco l\'exempel 2, ma ussa vegn utilisà in parameter dal string "query".
		</p>
		<p>
		S\'imaginescha che l\'URL da la pagina è: "http://example.org/list/category/demo.html"<br />
		la dumonda resultanta vegn ad esser: "SELECT id FROM mm_demo WHERE catname=\'demo\'"
		</p>
		<p>
		Sche l\'URL duess esser: "http://example.org/list.html",<br />
		vegn la dumonda resultanta esser: "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
		</p>
		';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['0'] = 'OR';

