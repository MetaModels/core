<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 * @author Carolina M Koehn <ck@kikmedia.de>
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MOD']['metamodels'] = array('MetaModels', 'Die MetaModels-Erweiterung ermöglicht die Erstellung eigener Datenbank-Modelle.');

$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord'] = 'Bearbeiten der Filtereinstellungen %%s für den Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label'] = 'Filter "%s" in MetaModel "%s"';
$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'] = '"%s" für Item %%s bearbeiten';
//Select Options
/* $GLOBALS['TL_LANG']['MSC']['optionsTitle'] = 'Wähle %s';
$GLOBALS['TL_LANG']['MSC']['sorting'] = 'Sortierung';

$GLOBALS['TL_LANG']['MSC']['noCatalog'] = 'Der Katalog existiert nicht. Wenden Sie sich bitte an Ihren Systemadministrator.';
$GLOBALS['TL_LANG']['MSC']['removeDataConfirm'] = 'Möchten Sie wirklich vor dem Import alle bestehenden Daten aus %s before löschen?';




/**
 * Error
 */
$GLOBALS['TL_LANG']['ERR']['noHeaderFields']     = 'Die Kopfzeilen (Zeile 1) der CSV-Datei müssen exakt den im Katalog definierten entsprechen.';
$GLOBALS['TL_LANG']['ERR']['noCSVData']     = 'In der CSV-Datei befinden sich keine Daten.';
$GLOBALS['TL_LANG']['ERR']['importSuccess'] = 'CSV-Import ini den Katalog erfolgreich: %s Datensätze';
$GLOBALS['TL_LANG']['ERR']['noCSVFile']     = 'Bitte wählen Sie eine CSV-Datei aus!';
$GLOBALS['TL_LANG']['ERR']['filetype']       = 'Es ist nicht erlaubt, Daten vom Typ "%s" in das System heraufzuladen!';
$GLOBALS['TL_LANG']['ERR']['filepartial']    = 'Die Datei %s wurde nur teilweise geladen!';
$GLOBALS['TL_LANG']['ERR']['importFolder']   = 'Der Ordner "%s" kann nicht importiert werden!';

$GLOBALS['TL_LANG']['ERR']['tableExists'] = 'Die Tabelle %s ist bereits vorhanden. Bitte wählen Sie einen anderen Tabellennamen.';
$GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'] = 'Die Tabelle %s existiert nicht.';
$GLOBALS['TL_LANG']['ERR']['columnExists'] = 'Die Spalte %s ist bereits vorhanden. Bitte wählen Sie einen anderen Spaltennamen.';
$GLOBALS['TL_LANG']['ERR']['columnDoesNotExist'] = 'Die Spalte %s ist in der Tabelle %s nicht vorhanden.';
$GLOBALS['TL_LANG']['ERR']['systemColumn'] = 'Der Bezeichner %s ist für das System reserviert. Bitte wählen Sie einen anderen Bezeichnernamen.';
$GLOBALS['TL_LANG']['ERR']['invalidColumnName'] = 'Ungültiger Spaltenname. Bitte benutzen Sie ausschließlich Buchstaben, Zahlen und den Unterstrich.';
$GLOBALS['TL_LANG']['ERR']['invalidTableName'] = 'Ungültiger Tabellenname. Bitte benutzen Sie ausschließlich Buchstaben, Zahlen und den Unterstrich.';


$GLOBALS['TL_LANG']['ERR']['aliasTitleMissing'] = 'Fehler: Alias in Feldkonfiguration ist nicht korrekt. Keine Parameter für den Titel';
$GLOBALS['TL_LANG']['ERR']['aliasDuplicate'] = 'Alias-Feld `%s` wurde bereits definiert. Für jede Tabelle ist nur ein Alias möglich';

$GLOBALS['TL_LANG']['ERR']['limitMin'] = 'Dieser Wert ist kleiner als der zulässige Minimalwert: %s';
$GLOBALS['TL_LANG']['ERR']['limitMax'] = 'Dieser Wert ist größer als der zulässige Maximalwert: %s';

$GLOBALS['TL_LANG']['ERR']['calcInvalid'] = 'Ungültige SQL-Berechnungsformel: %s';
$GLOBALS['TL_LANG']['ERR']['calcError'] = 'Berechnungsfehler - %s';

$GLOBALS['TL_LANG']['ERR']['catalogItemInvalid'] = 'Katalog-Item wurde nicht gefunden';
$GLOBALS['TL_LANG']['MSC']['catalogItemEditingDenied'] = 'Dieses Katalog-Item dürfen Sie nicht bearbeiten.';

/**
 * Filter Module
 */

$GLOBALS['TL_LANG']['MSC']['catalogSearch'] = 'Los';
$GLOBALS['TL_LANG']['MSC']['catalogSearchResults'] = 'Ergebnisse %u - %u von %u';
$GLOBALS['TL_LANG']['MSC']['catalogSearchPages'] = '(Seite %u von %u)';
$GLOBALS['TL_LANG']['MSC']['catalogSearchEmpty'] = 'Ihre Suche lieferte keine Ergebnisse.';
$GLOBALS['TL_LANG']['MSC']['clearFilter'] = 'Alle Filter zurücksetzen';
$GLOBALS['TL_LANG']['MSC']['clearAll'] = '%s zurücksetzen'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['selectNone'] = '%s auswählen'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['optionselected'] 	= '%s'; // %s=field label
$GLOBALS['TL_LANG']['MSC']['invalidFilter'] = 'Ungültiger Filtertyp';
$GLOBALS['TL_LANG']['MSC']['rangeFrom'] = 'von';
$GLOBALS['TL_LANG']['MSC']['rangeTo'] = 'nach';


// Checkbox options
$GLOBALS['TL_LANG']['MSC']['true'] = 'Ja';
$GLOBALS['TL_LANG']['MSC']['false'] = 'Nein';


// Date options
$GLOBALS['TL_LANG']['MSC']['daterange']['y'] = 'Letztes Jahr';
$GLOBALS['TL_LANG']['MSC']['daterange']['h'] = 'Letzte 6 Monate';
$GLOBALS['TL_LANG']['MSC']['daterange']['m'] = 'Letzter Monat';
$GLOBALS['TL_LANG']['MSC']['daterange']['w'] = 'Letzte Woche';
$GLOBALS['TL_LANG']['MSC']['daterange']['d'] = 'Gestern';
$GLOBALS['TL_LANG']['MSC']['daterange']['t'] = 'Heute';
$GLOBALS['TL_LANG']['MSC']['daterange']['df'] = 'Morgen';
$GLOBALS['TL_LANG']['MSC']['daterange']['wf'] = 'Nächste Woche';
$GLOBALS['TL_LANG']['MSC']['daterange']['mf'] = 'Nächster Monat';
$GLOBALS['TL_LANG']['MSC']['daterange']['hf'] = 'Nächste 6 Monate';
$GLOBALS['TL_LANG']['MSC']['daterange']['yf'] = 'Nächstes Jahr';

// Sort options
$GLOBALS['TL_LANG']['MSC']['unsorted'] 	= 'Reihenfolge wählen';
$GLOBALS['TL_LANG']['MSC']['lowhigh'] = '(Niedrig nach hoch)';
$GLOBALS['TL_LANG']['MSC']['highlow'] = '(Hoch nach niedrig)';
$GLOBALS['TL_LANG']['MSC']['AtoZ'] 		= '(A-Z)';
$GLOBALS['TL_LANG']['MSC']['ZtoA'] 		= '(Z-A)';
$GLOBALS['TL_LANG']['MSC']['truefalse'] = '(True-False)';
$GLOBALS['TL_LANG']['MSC']['falsetrue'] = '(False-True)';
$GLOBALS['TL_LANG']['MSC']['dateasc'] 	= '(Ältester zuerst)';
$GLOBALS['TL_LANG']['MSC']['datedesc'] 	= '(Neuester zuerst)';


/**
 * List Module
 */

$GLOBALS['TL_LANG']['MSC']['viewCatalog']     = 'Die Item-Details ansehen';
$GLOBALS['TL_LANG']['MSC']['editCatalog']     = 'Die Item-Details bearbeiten';

/**
 * Notify Module
 */

$GLOBALS['TL_LANG']['MSC']['notifySubmit']	= 'Nachricht senden';
$GLOBALS['TL_LANG']['MSC']['notifyConfirm']	= 'Ihre Nachricht wurde gesandt.';
$GLOBALS['TL_LANG']['MSC']['notifyMessage']	= 'Nachricht';

/**
 * Miscellaneous
 */
$GLOBALS['TL_LANG']['MSC']['noItemsMsg'] = 'Kein Eintrag gefunden, der den Kriterien entspricht. Diese Meldung können Sie anpassen, indem Sie die einen Eintrag <strong>$GLOBALS[\'TL_LANG\'][\'MSC\'][\'noItemsMsg\'] = \'Meine Meldung\';</strong> in Ihrer Systemeinstellung ystem/config/langconfig.php erstellen';
$GLOBALS['TL_LANG']['MSC']['catalogCondition']	= 'Bitte wählen Sie zuerst aus den folgenden Filtern aus (Mehrfachauswahl möglich): %s';
$GLOBALS['TL_LANG']['MSC']['catalogInvalid'] 		= 'Ungültiger Katalog!';
$GLOBALS['TL_LANG']['MSC']['catalogNoFields'] 	= 'Keine Katalog-Felder definiert!';

$GLOBALS['TL_LANG']['MSC']['keywordsBlacklist'] = array(
	'der','die','das','ich','Sie','sie','er','es','wir','ihr','ihre','Ihre','sein','ein','unser','da','aus','auf','über','bei','vor','nach','als','und','oder','von','für','mit','ohne','sind','ist'
	);

$GLOBALS['TL_LANG']['MSC']['com_catalog_subject']  = 'Contao :: Neuer Kommentar in Katalog: %s [%s]';
$GLOBALS['TL_LANG']['MSC']['com_catalog_message']  = "Katalog-Name: %s\nItem-Titel: %s\n\n%s hat einen neuen Kommentar auf Ihrer Webseite erstellt.\n\n---\n\n%s\n\n---\n\nView: %s\nBearbeiten: %s\n\nWenn Sie Kommentare moderieren, dann müssen Sie ich ins Backend einloggen, um den beitrag freizuschalten.";


/*
 * Frontend editing.
 */
$GLOBALS['TL_LANG']['MSC']['removeImage'] = 'Entferne %s';

/**
 * Reporting
 */
$GLOBALS['TL_LANG']['MSC']['reportAbuse'] = 'Missbrauch melden';

 */
$GLOBALS['TL_LANG']['MSC']['no_palette'] = 'Versuch, auf MetaModel "%s" ohne Palette für den Benutzer %s zuzugreifen.';
$GLOBALS['TL_LANG']['MSC']['no_view'] = 'Versuch, auf das MetaModel "%s" ohne Ansicht für den Benutzer %s zuzugeifen.';

/**
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['no_attribute_extension'] = 'Bitte mindestens eine Erweiterung für Attribute installieren! MetaModels ohne Attribute sind sinnlos.';
$GLOBALS['TL_LANG']['ERR']['activate_extension'] = 'Bitte die benötigte Erweiterung &quot;%s&quot; (%s) aktivieren.';
$GLOBALS['TL_LANG']['ERR']['install_extension'] = 'Bitte die benötigte Erweiterung &quot;%s&quot; (%s) installieren.';
 
+$GLOBALS['TL_LANG']['MSC']['tl_class']['w50']         = array('w50', 'Die Breite auf 50% und nach links floaten.');
+$GLOBALS['TL_LANG']['MSC']['tl_class']['clr']         = array('clr', 'Alle Floats clearen.');
+$GLOBALS['TL_LANG']['MSC']['tl_class']['m12']         = array('m12', 'Dem Element einen oberen Abstand (top-margin) von 12 Pixel geben (für einzelne Checkboxen).');
+$GLOBALS['TL_LANG']['MSC']['tl_class']['wizard']      = array('wizard', 'Das Eingabefeld kürzen, so dass Platz für einen Wizard ist (z.B. einen Date-Picker).');
+$GLOBALS['TL_LANG']['MSC']['tl_class']['long']        = array('long', 'Vergrößert das Eingabefeld, so dass es zwei Spalten umfasst.');
 


?>