<?php
/**
 * Translations are managed using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * @link http://help.transifex.com/intro/translating.html
 * @link https://www.transifex.com/projects/p/metamodels/language/ru/
 *
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *
 * last-updated: 2013-05-13T06:40:07+02:00
 */


$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0'] = 'Аннотация';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0'] = 'Параметр источников';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1'] = 'Параметр источники имеет нормальный макет:
		<pre>{{param::[source]?[query string]}}</pre>
		Где источник может быть любой из:
		<ul>
		<li><strong>get</strong> - Строка запроса HTTP GET</li>
		<li><strong>post</strong> - Поля HTTP POST</li>
		<li><strong>session</strong> - любое поле в сессии Contao</li>
		<li><strong>filter</strong> - любой из переданных параметров фильтра (для обмена параметрами между настройками фильтров).</li>
		</ul>
		Строка запроса построена как обычная HTTP-строка запроса с парой "name=value", которые объединяются с помощью & char и должны содержать минимум поле "name".
		Кроме того, могут использоваться один или несколько из следующих дополнительных ключей:
		<ul>
		<li><strong>default</strong> - значение по умолчанию, для использования, если нет значения.</li>
		<li><strong>aggregate</strong> - либо "list", либо "set"</li>
		<li><strong>key</strong> - установите 1 для чтения ключа массивов (набора агрегата потребностей).</li>
		<li><strong>recursive</strong> - установите 1 для чтения рекурсивных массивов (набора агрегата потребностей).</li>
		</ul>
		';

