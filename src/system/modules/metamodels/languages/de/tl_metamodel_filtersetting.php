<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 * @translation Carolina M Koehn <ck@kikmedia.de>
 */

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['fid']      = array('Elternelement', 'Geben Sie an, zu welchem Elternelement die Filtereinstellungen gehören.');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['type']     = array('Typ', 'Einstellungstyp');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['enabled']  = array('Aktivieren', 'Diese Filtereinstellung aktivieren.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['attr_id']  = array('Attribut', 'Attribut, auf das sich diese Einstellung bezieht.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['urlparam'] = array('URL-Parameter', 'Geben Sie den URL-Parameter an, der für das ausgewählte Attribut verwendet werden soll. Der spezielle <em>"auto_item"</em>-Parameter kann ebenfalls benutzt werden. Dies ist für Aliasse nützlich.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['customsql'] = array('Eigene SQL-Abfrage', 'Geben Sie die SQL-Abfrage an, die ausgeführt werden soll. Die Verwendung von Inserttags wird unterstützt.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['label']        = array('Label', 'Label, falls nicht der Attributname genommen werden soll.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['template']     = array('Template', 'Sub-Template für dieses Filterelement. Standard: Formular-Widget.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['blankoption']  = array('Leerer Wert', 'Leere Auswahl einbinden.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlyused']     = array('Nur zugeordnete Werte', 'In den Optionen nur Werte zeigen, die einem Datensatz zugeordnet sind.');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['onlypossible'] = array('Nur verbleibende Werte', 'In den Optionen nur Werte zeigen, die mit dem aktuellen Filter weiterhin vorkommen.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['title_legend']         = 'Typ';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['config_legend']         = 'Einstellung';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['new']                  = array('Neu', 'Neue Einstellung erstellen.');

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['edit']                 = array('Einstellung bearbeiten', 'Filtereinstellung ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['copy']                 = array('Filtereinstellung kopieren', 'Die Filtereinstellung ID %s kopieren');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['delete']               = array('Filtereinstellung löschen', 'Die Filtereinstellung ID %s löschen');
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['show']                 = array('Details anzeigen', 'Die Details der Filtereinstellung ID %s anzeigen');


/**
 * Reference
 */

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['idlist']       = 'Vordefinierte Einstellung';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['simplelookup'] = 'Einfache Abfrage';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['customsql']    = 'Eigenes SQL';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionor']  = 'Oder-Bedingung (OR)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames']['conditionand'] = 'Und-Bedingung (AND)';

$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_']    = '%s <strong>%s</strong> <span title="%s"><sup>(?)</sup></span> <em>[%s]</em>';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['simplelookup'] = '%s <strong>%s</strong> <span title="%s"><sup>(?)</sup></span><br /> für Attribut <em>%s</em> (URL-Parameter: %s)';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionor']  = '%s <strong>%s</strong> <span title="%s"><sup>(?)</sup></span><br /> Datensatz/Datensätze in beliebigem Ergebnis.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionand'] = '%s <strong>%s</strong> <span title="%s"><sup>(?)</sup></span><br /> Datensatz/Datensätze in allen Ergebnissen.';
$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['fefilter']    = '%s <strong>%s</strong> <span title="%s"><sup>(?)</sup></span><br> für Attribut <em>%s</em> (URL-Parameter: %s)';

