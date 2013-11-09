<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/el/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2013-05-13T22:11:20+02:00
 */


$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0'] = 'Περίληψη ';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1'] = 'Πληκτρολογήστε οποιοδήποτε ερώτημα SQL που εκτελείται.
⏎ »» Απαιτείται ότι αυτό το ερώτημα επιστρέφει τουλάχιστον μία στήλη ονομάζει "αναγνωριστικό". ⏎ »» ';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0'] = 'Παραδειγμα 1<br />απλό ερώτημα ';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1'] = ' SELECT id ΑΠΟ ΟΠΟΥ mm_mymetamodel page_id = 1 < / pre> ⏎
 »» Αυτό επιλέγει όλα τα αναγνωριστικά από τον πίνακα  mm_mymetamodel < / em > που έχει την τιμή  page_id = 1 < / em > ⏎
 »»';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0'] = 'Παραδειγμα 2<br />εισαγωγη ονοματος ταμπελας';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1'] = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
		This is merely the same as example 1 but the table name of the current MetaModel (i.e.: the <em>mm_mymetamodel</em> from above) will get inserted into the query.
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0'] = 'Εισαγωγη ετικετων';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1'] = 'Ετικέτες υποστηρίζονται, αλλά δεν είναι όλες οι ετικέτες διαθεσιμες⏎ »» όταν η ρύθμιση φίλτρου χρησιμοποιείται (για παράδειγμα το {{page::id}} is⏎ »» διαθέσιμη μόνο όταν χρησιμοποιείται από ένα μπροστινό άκρο σελίδας και όχι από τα RSS-feeds).';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0'] = 'Ασφαλης εισαγωγη ετικετων';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1'] = 'Ενθετο ασφαλων ετικετων είναι ακριβώς όπως το απλό ένθετο, αλλά η αξία τους δραπέτευσε.
⏎ »»Ως εκ τούτου θα μπορούσε να είναι σε καλύτερη θέση με το ασφαλή ισοδύναμο αν ξέρετε ακριβώς τι κάνετε.<br />⏎
⏎ »» Ο συμβολισμός είναι σαν:⏎ 
»»{{ασφαλή::σελίδα::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0'] = 'Πηγες παραμετρων<br />';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1'] = 'Parameter sources have the normal layout of:
		<pre>{{param::[source]?[query string]}}</pre>
		Where the source may be any of:
		<ul>
		<li><strong>get</strong> - HTTP GET query string</li>
		<li><strong>post</strong> - HTTP POST fields</li>
		<li><strong>session</strong> - any field in the Contao session</li>
		<li><strong>filter</strong> - any of the passed filter parameters (for sharing paramters between filter settings).</li>
		</ul>
		The Query string is built like a normal HTTP query string as "name=value" pairs which are combined using the & char and must at least contain the field "name".
		One or more of the following optional keys may be used in addition:
		<ul>
		<li><strong>default</strong> - the default value to use, if there is no value available.</li>
		<li><strong>aggregate</strong> - either "list" or "set"</li>
		<li><strong>key</strong> - set to 1 to read the key of arrays (needs aggregate set).</li>
		<li><strong>recursive</strong> - set to 1 to read arrays recursive (needs aggregate set).</li>
		</ul>
		';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0'] = 'Παραδειγμα 3<br />χρησιμοποιηση φιλτρων των παραμετρων πηγων';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1'] = '<pre>SELECT id
	FROM {{table}}
	WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
		<p>
		This is the same as example 2 but now we use a parameter from the "query" string.
		</p>
		<p>
		Imagine the page URL as of: "http://example.org/list/category/demo.html"<br />
		the resulting Query will then be: "SELECT id FROM mm_demo WHERE catname=\'demo\'"
		</p>
		<p>
		If the URL should be: "http://example.org/list.html",<br />
		the resulting Query will then be: "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
		</p>
		';
