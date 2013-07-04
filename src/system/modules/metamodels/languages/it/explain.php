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
 * last-updated: 2013-07-04T16:13:02+02:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0'] = 'Anteprima';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1'] = 'Digitare una query SQL che deve essere eseguita.<br />
		E \'necessario che questa query restituisce almeno una colonna denominata "id".
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0'] = 'Esempio 1<br />query semplice';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1'] = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
⇥⇥Questo seleziona tutti gli ID dalla tabella <em> mm_mymetamodel </ em> che hanno il valore <em> page_id = 1 </ em> ⏎
⇥ ⇥
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0'] = 'Esempio 2<br />Riferimento nome tabella';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1'] = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>⏎
⇥⇥Questa è una query identica a quella dell\'esempio 1, ma il nome della tabella è quello del MetaModel corrente (<em>mm_mymetamodel </ em> come da esempio precedente) che verrà inserito nella query.⏎
⇥⇥
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0'] = 'Insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0'] = 'Secure insert tags';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1'] = 'I Secure insert tags sono simili ai classici insert tags, ma i loro valori mantenuti all\'interno della query.<br />⏎
⇥⇥Pertanto si potrebbe essere meglio utilizzare l\'equivalente sicuro se non si sa esattamente cosa si sta facendo.<br />⏎
⇥⇥La modalità di utilizzo è:⏎
⇥⇥<pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0'] = 'Sorgente dei parametri';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0'] = 'Esempio 3<br />Utilizzo di filtri di sorgenti parametri complessi ';
