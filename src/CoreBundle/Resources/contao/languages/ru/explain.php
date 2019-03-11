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
 * last-updated: 2018-11-26T23:36:42+01:00
 */

$GLOBALS['TL_LANG']['XPL']['customsql']['0']['0']            = 'Аннотация';
$GLOBALS['TL_LANG']['XPL']['customsql']['0']['1']            = 'Введите SQL-запрос, который должен быть выполнен.<br />
        Необходимо, чтобы запрос возвратил хотя бы один столбец с именем "id".
        ';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['0']            = 'Пример 1<br />простой запрос';
$GLOBALS['TL_LANG']['XPL']['customsql']['1']['1']            = '<pre>SELECT id FROM mm_mymetamodel WHERE page_id=1</pre>
        Это выберет все ID из таблицы <em>mm_mymetamodel</em> которые имеют значение <em>page_id=1</em>';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['0']            = 'Пример 2<br />вставка имени таблицы';
$GLOBALS['TL_LANG']['XPL']['customsql']['2']['1']            = '<pre>SELECT id FROM {{table}} WHERE page_id=1</pre>
        Это просто то же, что и пример 1, но имя таблицы текущего MetaModel (то есть: <em>mm_mymetamodel</em> сверху) будет вставлено в запрос.';
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
$GLOBALS['TL_LANG']['XPL']['customsql']['6']['1']            = '<pre>SELECT id
    FROM {{table}}
    WHERE catname={{param::get?name=category&default=defaultcat}}</pre>
        <p>
        Это то же самое, что и пример 2, но теперь мы используем параметр из строки запроса.
        </p>
        <p>
        Представьте URL-адрес страницы: "http://example.org/list/category/demo.html"<br />
        в результате запроса будет: "SELECT id FROM mm_demo WHERE catname=\'demo\'"
        </p>
        <p>
        Если URL-адрес должен быть: "http://example.org/list.html",<br />
        в результате запроса будет: "SELECT id FROM mm_demo WHERE catname=\'defaultcat\'"
        </p>
        ';
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

