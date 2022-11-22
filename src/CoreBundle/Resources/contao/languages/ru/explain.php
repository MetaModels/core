<?php
/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * Translations are managed automatically using Transifex. To create a new translation
 * or to help to maintain an existing one, please register at transifex.com.
 *
 * Last-updated: 2022-11-02T22:34:16+01:00
 *
 * @copyright 2012-2022 The MetaModels team.
 * @license   https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @link      https://www.transifex.com/metamodels/public/
 * @link      https://www.transifex.com/signup/?join_project=metamodels
 */


$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Аннотация';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Пример 1<br />простой запрос';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Пример 2<br />вставка имени таблицы';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['0']            = 'Вставка тегов';
$GLOBALS['TL_LANG']['XPL']['customsql']['3']['1']            = 'Вставка тегов поддерживается, но имейте в виду, что не все теги могут 
    быть доступны когда используется параметр фильтра 
    (например, <em>{{page::id}}</em> доступен только при использовании 
    с передней страницы, а не из RSS-каналов).';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['0']            = 'Безопасная вставка тегов';
$GLOBALS['TL_LANG']['XPL']['customsql']['4']['1']            = 'Безопасные теги вставки похожи на теги простой вставки, но их значение ускользает в запросе.<br />
        Поэтому, возможно, вам лучше использовать безопасный эквивалент, если вы точно не знаете, что делаете.<br />
        Обозначения:
        <pre>{{secure::page::id}}</pre>';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['0']            = 'Параметр источников';
$GLOBALS['TL_LANG']['XPL']['customsql']['5']['1']            = 'Источники параметров имеют нормальную компоновку:
        <pre>{{param::[source]?[query string]}}</pre>
        Где источником может быть любой из:
        <ul>
        <li><strong>get</strong> - строка запроса HTTP GET</li>
        <li><strong>post</strong> - HTTP POST поля</li>
        <li><strong>cookie</strong> - HTTP COOKIE значения</li>
        <li><strong>session</strong> - любое поле в the Contao сессии</li>
        <li><strong>filter</strong> - любой из переданных параметров фильтра (для обмена параметрами между настройками фильтра).</li>
        <li><strong>container</strong> - имя вызываемой службы в контейнере службы MetaModels (для этого требуется дополнительное PHP-кодирование с вашей стороны).</li>
        </ul>
        Строка запроса построена как обычная строка запроса HTTP как пары «имя = значение», которые объединены с помощью символа & char и должны содержать по крайней мере поле «имя».
        Можно использовать одну или несколько следующих дополнительных клавиш:
        <ul>
        <li><strong>default</strong> - значение по умолчанию для использования, если нет доступного значения.</li>
        <li><strong>aggregate</strong> - либо «список», либо «набор».</li>
        <li><strong>key</strong> -  установить 1, чтобы прочитать ключ массивов (требуется совокупность с «set»).</li>
        <li><strong>recursive</strong> - устанавливается в 1 для чтения рекурсивных массивов (требуется совокупность с «set»).</li>
        <li><strong>service</strong>  - имя службы для извлечения (требуется источник «service»).</li>
        </ul>
        ';
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['0']            = 'Пример 3<br />использовать сложный фильтр параметров источников';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['0']['0'] = 'Значение атрибута...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['0']['1'] = 'Условие выполняется, когда значение атрибута равно заданному значению.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['1']['0'] = 'Значения атрибута содержат любое из...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['1']['1'] = 'Условие выполняется, когда любое из значений атрибута соответствует хотя бы одному из указанных значений (установить пересечение).';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['2']['0'] = 'Является ли атрибут видимым...';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['2']['1'] = 'Условие выполняется, когда выполняется условие указанного атрибута. Другими словами, атрибут отображается, если и только если указанный атрибут также виден.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['0'] = 'ИЛИ';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['3']['1'] = 'Любое дополнительное условие должно быть выполнено.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['4']['0'] = 'И';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['4']['1'] = 'Все вспомогательные условия должны быть выполнены.';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['5']['0'] = 'НЕ';
$GLOBALS['TL_LANG']['XPL']['dcasetting_condition']['5']['1'] = 'Инвертировать результат содержащегося условия.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['2']['0']             = 'clr';
$GLOBALS['TL_LANG']['XPL']['tl_class']['2']['1']             = 'Очистить все floats.';
$GLOBALS['TL_LANG']['XPL']['tl_class']['3']['0']             = 'clx';
$GLOBALS['TL_LANG']['XPL']['tl_class']['3']['1']             = 'Чтобы удалить только раздражающее переполнение, используйте его вместе с «clr».';
$GLOBALS['TL_LANG']['XPL']['tl_class']['4']['0']             = 'w50';
$GLOBALS['TL_LANG']['XPL']['tl_class']['4']['1']             = 'Установите ширину поля на 50% и поместите ее (float: left).';
$GLOBALS['TL_LANG']['XPL']['tl_class']['5']['0']             = 'w50x';
$GLOBALS['TL_LANG']['XPL']['tl_class']['5']['1']             = 'Чтобы удалить только раздражающую фиксированную высоту, используйте ее вместе с «w50».';
$GLOBALS['TL_LANG']['XPL']['tl_class']['6']['0']             = 'm12';
$GLOBALS['TL_LANG']['XPL']['tl_class']['6']['1']             = 'Добавьте верхний край 12 пикселей к элементу (используется для отдельных флажков).';
$GLOBALS['TL_LANG']['XPL']['tl_class']['7']['0']             = 'мастер';
$GLOBALS['TL_LANG']['XPL']['tl_class']['7']['1']             = 'Сократите поле ввода, чтобы было достаточно места для кнопки мастера (например, поля выбора даты).';
$GLOBALS['TL_LANG']['XPL']['tl_class']['8']['0']             = 'длинный';

