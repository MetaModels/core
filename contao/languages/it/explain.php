<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/it/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2015-08-04T04:10:59+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Anteprima';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Digitare una query SQL che deve essere eseguita.<br />
		E\' necessario che questa query restituisce almeno una colonna denominata "id".
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Esempio 1<br />query semplice';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
⇥⇥Questo seleziona tutti gli ID dalla tabella <em> mm_mymetamodel </ em> che hanno il valore <em> page_id = 1 </ em> ⏎
⇥ ⇥
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Esempio 2<br />Riferimento nome tabella';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>⏎
⇥⇥Questa è una query identica a quella dell\'esempio 1, ma il nome della tabella è quello del MetaModel corrente (<em>mm_mymetamodel </ em> come da esempio precedente) che verrà inserito nella query.⏎
⇥⇥
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Gli insert tags sono supportati, ma occorre tenere presente che non tutti i tags potrebbero essere disponibili⏎
⇥⇥quando si utilizza l\'impostazione del filtro ad esempio, il tag  <em>{{page::id}}</em> è⏎
⇥⇥disponibile solamente in una pagina di frontend mentre non lo è nel feed RSS.';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Secure insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'I Secure insert tags sono simili ai classici insert tags, ma i loro valori mantenuti all\'interno della query.<br />⏎
⇥⇥Pertanto si potrebbe essere meglio utilizzare l\'equivalente sicuro se non si sa esattamente cosa si sta facendo.<br />⏎
⇥⇥La modalità di utilizzo è:⏎
⇥⇥<pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Sorgente dei parametri';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'I sorgenti dei parametri sono normalmente del tipo:
		<pre>{{param::[source]?[query string]}}</pre>
		Dove il sorgente può essere un qualsiasi:
		<ul>
		<li><strong>get</strong> - HTTP GET query string</li>
		<li><strong>post</strong> - HTTP POST fields</li>
		<li><strong>sessione</strong> - any field in the Contao session</li>
		<li><strong>filtro</strong> - uno qualsiasi dei parametri di filtro passati (per la condivisione dei parametri tra le impostazioni del filtro).</li>
		</ul>
		La Query string è costruita come una Query string HTTP normale del tipo "nome=valore" che vengono combinati utilizzando il carattere & e deve almeno contenere il campo "nome".
		One or more of the following optional keys may be used in addition:
		Uno o più delle seguenti opzioni può essere aggiunta:
		<ul>
		<li><strong>default</strong> - il valore di default da utilizzare, se non vi è alcun valore disponibile.</li>
		<li><strong>aggregate</strong> - oppure "list" o "set"</li>
		<li><strong>key</strong> - impostato a 1 per leggere la chiave di array (è necessario un set aggregate).</li>
		<li><strong>recursive</strong> - impostato a 1 per leggere l\'array in modo ricorsivo (è necessario un set aggregate).</li>
		</ul>
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Esempio 3<br />Utilizzo di filtri di sorgenti parametri complessi ';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT id
	FROM {{table}}
	WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
		<p>
		Questa è la stessa dell\'esempio 2, ma qui viene usato un parametro dalla Query string.
		</p>
		<p>
		Si pensi all\'url della pagina partire da questo: "http://example.org/list/category/demo.html"<br />
		La query risultante sarà quindi: "SELECT id FROM mm_demo WHERE catname=\'demo\'"
		</p>
		<p>
		Se l\'url fosse questo: "http://example.org/list.html",<br />
		la query risultante sarebbe: "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
		</p>
		';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['0'] = 'OR';

