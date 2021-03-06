<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Jan Malte Gerth <anmeldungen@malte-gerth.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\Input;
use Contao\InsertTags;
use Contao\Session;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Render\Setting\ICollection as IRenderSettings;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceSubscriberInterface;

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 */
class CustomSql implements ISimple, ServiceSubscriberInterface
{
    /**
     * The parenting filter setting container this setting belongs to.
     *
     * @var ICollection
     */
    private $collection = null;

    /**
     * The attributes of this filter setting.
     *
     * @var array
     */
    private $data = [];

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
     * The service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor - initialize the object and store the parameters.
     *
     * @param ICollection        $collection   The parenting filter settings object.
     * @param array              $data         The attributes for this filter setting.
     * @param ContainerInterface $container    The service container.
     *
     * @throws \InvalidArgumentException When a service is missing.
     */
    public function __construct($collection, $data, ContainerInterface $container)
    {
        $this->collection = $collection;
        $this->data       = $data;

        $missing = [];
        foreach (array_keys(self::getSubscribedServices()) as $serviceId) {
            if (!$container->has($serviceId)) {
                $missing[] = $serviceId;
            }
        }
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'The service container is missing the following services: ' . implode(', ', $missing)
            );
        }
        $this->container = $container;
    }

    /**
     * Get the needed services.
     *
     * @return array
     */
    public static function getSubscribedServices()
    {
        return [
            Connection::class => Connection::class,
            Input::class      => Input::class,
            InsertTags::class => InsertTags::class,
            Session::class    => Session::class,
            // This one is deprecated.
            IMetaModelsServiceContainer::class => IMetaModelsServiceContainer::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function get($strKey)
    {
        return isset($this->data[$strKey]) ? $this->data[$strKey] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $this->filterParameters = $arrFilterUrl;
        $this->queryString      = $this->get('customsql');
        $this->queryParameter   = [];

        $objFilter->addFilterRule($this->getFilterRule());

        unset($this->filterParameters);
        unset($this->queryString);
        unset($this->queryParameter);
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        $arrParams = [];

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
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        return [];
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
            $this->container->get(Connection::class)
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
        $this->queryString = str_replace(
            '{{table}}',
            $this->collection->getMetaModel()->getTableName(),
            $this->queryString
        );
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

        $service = $this->container->get(IMetaModelsServiceContainer::class)->getService($serviceName);
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
                return $this->container->get(Input::class)->get($valueName);

            case 'post':
                return $this->container->get(Input::class)->post($valueName);

            case 'cookie':
                return $this->container->get(Input::class)->cookie($valueName);

            case 'session':
                return $this->container->get(Session::class)->get($valueName);

            case 'filter':
                if (is_array($this->filterParameters)) {
                    if (array_key_exists($valueName, $this->filterParameters)) {
                        return $this->filterParameters[$valueName];
                    }

                    return null;
                }
                break;

            case 'container':
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Getting filter values from the service container is deprecated, the container will get removed.',
                    E_USER_DEPRECATED
                );
                // @codingStandardsIgnoreEnd
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
            [$this, 'convertParameter'],
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
        return $this->container->get(InsertTags::class)->replace($queryString, false);
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
            [$this, 'parseAndAddSecureInsertTagAsParameter'],
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
