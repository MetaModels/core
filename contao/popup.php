<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

use MetaModels\Widgets\PickerWidget;

// Search the initialize.php.
$dir = dirname($_SERVER['SCRIPT_FILENAME']);

while ($dir != '.' && $dir != '/' && !is_file($dir . '/system/initialize.php')) {
    $dir = dirname($dir);
}

if (!is_file($dir . '/system/initialize.php')) {
    echo 'Could not find initialize.php, where is Contao?';
    exit;
}

define('TL_MODE', 'BE');
require_once $dir . '/system/initialize.php';

// Run the style picker.
$x = new PickerWidget();
$x->run();
