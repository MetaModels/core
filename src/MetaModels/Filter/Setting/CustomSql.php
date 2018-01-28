<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 */
class CustomSql extends Simple
{
    /**
     * The filter params (should be array or null).
     *
     * @var array
     */
    private $filterParameters;

    /**
     * The query string.
     *
     * @var string
     */
    private $queryString;

    /**
     * The query parameters.
     *
     * @var array
     */
    private $queryParameter;

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $this->filterParameters = $arrFilterUrl;
        $this->queryString      = $this->get('customsql');
        $this->queryParameter   = array();

        $objFilter->addFilterRule($this->getFilterRule());

        unset($this->filterParameters);
        unset($this->queryString);
        unset($this->queryParameter);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        $arrParams = array();

        preg_match_all('@\{\{param::filter\?([^}]*)\}\}@', $this->get('customsql'), $arrMatches);
        foreach ($arrMatches[1] as $strQuery) {
            parse_str($strQuery, $arrArgs);
            if (isset($arrArgs['name'])) {
                $arrName     = (array) $arrArgs['name'];
                $arrParams[] = $arrName[0];
            }
        }

        return $arrParams;
    }

    /**
     * Compile the query and the parameters.
     *
     * @return void
     */
    private function compile()
    {
        $this->parseTable();
        $this->parseRequestVars();
        $this->parseSecureInsertTags();
        $this->parseInsertTags();
    }

    /**
     * Retrieve the simple query.
     *
     * @return SimpleQuery
     */
    private function getFilterRule()
    {
        $this->compile();

        return new SimpleQuery(
            $this->queryString,
            $this->queryParameter,
            'id',
            $this->getMetaModel()->getServiceContainer()->getDatabase()
        );
    }

    /**
     * Add parameters to the list.
     *
     * @param array $parameters The parameters to add.
     *
     * @return void
     */
    private function addParameters($parameters)
    {
        if (empty($parameters)) {
            return;
        }

        $this->queryParameter = array_merge($this->queryParameter, $parameters);
    }

    /**
     * Add a parameter to the list.
     *
     * @param string $parameter The parameter to add.
     *
     * @return void
     */
    private function addParameter($parameter)
    {
        $this->queryParameter[] = $parameter;
    }

    /**
     * Replace the table name in the query string.
     *
     * @return void
     */
    private function parseTable()
    {
        $this->queryString = str_replace('{{table}}', $this->getMetaModel()->getTableName(), $this->queryString);
    }

    /**
     * Retrieve the value with the given name from the service container.
     *
     * @param string $valueName The name of the value in the source to retrieve.
     *
     * @param array  $arguments The arguments of the parameter.
     *
     * @return mixed
     */
    private function getValueFromServiceContainer($valueName, $arguments)
    {
        if (!empty($arguments['service'])) {
            $serviceName = $arguments['service'];
        } else {
            $serviceName = $valueName;
        }

        $service = $this->getServiceContainer()->getService($serviceName);
        if (is_callable($service)) {
            return call_user_func($service, $valueName, $arguments);
        }

        return 'NULL';
    }

    /**
     * Retrieve the value with the given name from the source with the given name.
     *
     * @param string $source    The source to retrieve the value from.
     *                          Valid values are: ('get', 'post', 'cookie', 'session', 'filter' or 'container').
     *
     * @param string $valueName The name of the value in the source to retrieve.
     *
     * @param array  $arguments The arguments of the parameter.
     *
     * @return mixed
     */
    private function getValueFromSource($source, $valueName, $arguments)
    {
        switch (strtolower($source)) {
            case 'get':
                return \Input::get($valueName);

            case 'post':
                return \Input::post($valueName);

            case 'cookie':
                return \Input::cookie($valueName);

            case 'session':
                return \Session::getInstance()->get($valueName);

            case 'filter':
                if (is_array($this->filterParameters)) {
                    if (array_key_exists($valueName, $this->filterParameters)) {
                        return $this->filterParameters[$valueName];
                    }

                    return null;
                }
                break;

            case 'container':
                return $this->getValueFromServiceContainer($valueName, $arguments);

            default:
        }

        // Unknown sources always resort to null.
        return null;
    }

    /**
     * Convert a parameter using an aggregate function.
     *
     * @param string $var       The parameter value.
     *
     * @param array  $arguments The arguments of the parameter.
     *
     * @return string
     */
    private function convertParameterAggregate($var, $arguments)
    {
        // Treat as list.
        $var = (array) $var;

        if (!empty($arguments['recursive'])) {
            $var = iterator_to_array(
                new \RecursiveIteratorIterator(
                    new \RecursiveArrayIterator(
                        $var
                    )
                )
            );
        }

        if (!$var) {
            return 'NULL';
        }

        if (!empty($arguments['key'])) {
            $var = array_keys($var);
        } else {
            // Use values.
            $var = array_values($var);
        }

        if ($arguments['aggregate'] == 'set') {
            $this->addParameter(implode(',', $var));

            return '?';
        }

        $this->addParameters($var);

        return rtrim(str_repeat('?,', count($var)), ',');
    }

    /**
     * Convert a parameter in the query string.
     *
     * @param array $arrMatch The match from the preg_replace_all call in parseRequestVars().
     *
     * @return string
     *
     * @internal Only to be used via parseRequestVars().
     */
    public function convertParameter($arrMatch)
    {
        list($strSource, $strQuery) = explode('?', $arrMatch[1], 2);
        parse_str($strQuery, $arrArgs);
        $arrName = (array) $arrArgs['name'];

        $var = $this->getValueFromSource($strSource, array_shift($arrName), $arrArgs);

        $index = 0;
        $count = count($arrName);
        while ($index < $count && is_array($var)) {
            $var = $var[$arrName[$index++]];
        }

        if ($index != $count || $var === null) {
            if (array_key_exists('default', $arrArgs) && (null !== $arrArgs['default'])) {
                $this->addParameter($arrArgs['default']);

                return '?';
            } else {
                return 'NULL';
            }
        }

        // Treat as scalar value.
        if (!isset($arrArgs['aggregate'])) {
            $this->addParameter($var);

            return '?';
        }

        return $this->convertParameterAggregate($var, $arrArgs);
    }

    /**
     * Parse a request var insert tag within the SQL.
     *
     * @return void
     */
    private function parseRequestVars()
    {
        $this->queryString = preg_replace_callback(
            '@\{\{param::([^}]*)\}\}@',
            array($this, 'convertParameter'),
            $this->queryString
        );
    }

    /**
     * Replace all insert tags in the query string.
     *
     * @param string $queryString The string to replace insert tags within.
     *
     * @return string
     */
    private function parseInsertTagsInternal($queryString)
    {
        $dispatcher = $this->getEventDispatcher();
        $event      = new ReplaceInsertTagsEvent($queryString, false);
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS, $event);

        return $event->getBuffer();
    }

    /**
     * Replace all insert tags in the query string.
     *
     * @param string $arrMatch The match from the preg_replace call.
     *
     * @return string
     *
     * @internal Only to be used internal as callback from parseSecureInsertTags().
     */
    public function parseAndAddSecureInsertTagAsParameter($arrMatch)
    {
        $this->addParameter($this->parseInsertTagsInternal('{{' . $arrMatch[1] . '}}'));

        return '?';
    }

    /**
     * Replace all secure insert tags.
     *
     * @return void
     */
    private function parseSecureInsertTags()
    {
        $this->queryString = preg_replace_callback(
            '@\{\{secure::([^}]+)\}\}@',
            array($this, 'parseAndAddSecureInsertTagAsParameter'),
            $this->queryString
        );
    }

    /**
     * Replace all insert tags in the query string.
     *
     * @return void
     */
    private function parseInsertTags()
    {
        $this->queryString = $this->parseInsertTagsInternal($this->queryString);
    }
}
