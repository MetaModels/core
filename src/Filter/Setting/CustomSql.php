<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Oliver Willmes <info@oliverwillmes.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\InsertTags;
use Doctrine\DBAL\Connection;
use MetaModels\InsertTag\Node;
use MetaModels\InsertTag\Parser;
use MetaModels\CoreBundle\Contao\InsertTag\ReplaceParam;
use MetaModels\CoreBundle\Contao\InsertTag\ReplaceTableName;
use MetaModels\CoreBundle\Contao\InsertTag\ResolveLanguageTag;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Render\Setting\ICollection as IRenderSettings;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

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
     * The filter params (should be an array or null).
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
     * @param ICollection        $collection The parenting filter settings object.
     * @param array              $data       The attributes for this filter setting.
     * @param ContainerInterface $container  The service container.
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
            Connection::class                  => Connection::class,
            InsertTags::class                  => InsertTags::class,
            ReplaceParam::class                => ReplaceParam::class,
            ReplaceTableName::class            => ReplaceTableName::class,
            ResolveLanguageTag::class          => ResolveLanguageTag::class,
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
        $this->literateQuery();
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
    private function addParameters(array $parameters)
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
    private function addParameter(string $parameter)
    {
        $this->queryParameter[] = $this->parseInsertTagsInternal($parameter);
    }

    /**
     * Replace the table name in the query string.
     *
     * @return void
     */
    private function parseTable()
    {
        $this->queryString = $this->container->get(ReplaceTableName::class)
            ->replace($this->collection->getMetaModel()->getTableName(), $this->queryString);
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
    private function getValueFromServiceContainer(string $valueName, array $arguments)
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
    private function getValueFromSource(string $source, string $valueName, array $arguments)
    {
        switch (strtolower($source)) {
            case 'get':
            case 'post':
            case 'cookie':
            case 'session':
                return $this->executeInsertTagReplaceParam($source, $arguments);

            case 'filter':
                if (is_array($this->filterParameters)) {
                    if (\array_key_exists($valueName, $this->filterParameters)) {
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
     * Execute the insert tag for replace parameters.
     *
     * @param string $source    The source.
     * @param array  $arguments The arguments.
     *
     * @return mixed|string
     */
    private function executeInsertTagReplaceParam(string $source, array $arguments)
    {
        $filteredArguments = \array_intersect_key($arguments, \array_flip(['name', 'default']));
        $imploded          = \array_reduce(
            \array_keys($filteredArguments),
            function ($carry, $item) use ($filteredArguments) {
                return $carry . ($carry ? '&' : '') . $item . '=' . $filteredArguments[$item];
            },
            ''
        );

        $result = $this->container->get(ReplaceParam::class)
            ->replace(\sprintf('{{param::%s?%s}}', $source, $imploded));

        // @codingStandardsIgnoreStart
        return (($results = @\unserialize($result, ['allowed_classes' => false])) ? $results : $result);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Convert a parameter using an aggregate function.
     *
     * @param array $var       The parameter value.
     *
     * @param array $arguments The arguments of the parameter.
     *
     * @return string
     */
    private function convertParameterAggregate(array $var, array $arguments)
    {
        if (!empty($arguments['recursive'])) {
            $var = \iterator_to_array(
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
            $var = \array_keys($var);
        } else {
            // Use values.
            $var = \array_values($var);
        }

        if ($arguments['aggregate'] == 'set') {
            $this->addParameter(implode(',', $var));

            return '?';
        }

        $this->addParameters($var);

        return \rtrim(\str_repeat('?,', \count($var)), ',');
    }

    /**
     * Convert a parameter in the query string.
     *
     * @param string $strMatch The match from the preg_replace_all call in parseRequestVars().
     *
     * @return string
     */
    private function convertParameter(string $strMatch): string
    {
        list($strSource, $strQuery) = explode('?', $strMatch, 2);
        parse_str($strQuery, $arrArgs);
        $arrName = (array) $arrArgs['name'];

        $var = $this->getValueFromSource($strSource, array_shift($arrName), $arrArgs);

        $index = 0;
        $count = count($arrName);
        while ($index < $count && is_array($var)) {
            $var = $var[$arrName[$index++]];
        }

        if ($index != $count || $var === null) {
            if (\array_key_exists('default', $arrArgs) && (null !== $arrArgs['default'])) {
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

        return $this->convertParameterAggregate((array) $var, $arrArgs);
    }

    /**
     * Replace all insert tags in the query string.
     *
     * @param string $queryString The string to replace insert tags within.
     *
     * @return string
     */
    private function parseInsertTagsInternal(string $queryString): string
    {
        return $this->container->get(InsertTags::class)->replace($queryString, false);
    }

    /**
     * Replace all insert tags in the query string.
     *
     * @param string $strMatch The parameter value.
     *
     * @return string
     */
    private function parseAndAddSecureInsertTagAsParameter(string $strMatch): string
    {
        $this->addParameter($this->parseInsertTagsInternal('{{' . $strMatch . '}}'));

        return '?';
    }

    /**
     * Literate queryString, split it in pieces and dissolve inserttags.
     *
     * @return void
     */
    private function literateQuery(): void
    {
        $newQueryString = $this->container->get(ResolveLanguageTag::class)->resolve($this->queryString);
        $tagList        = Parser::parse($newQueryString);

        $newQueryString = '';
        foreach ($tagList->getIterator() as $item) {
            if ($item instanceof Node) {
                $newQueryString .= $this->resolveNode($item);
                continue;
            }
            $newQueryString .= $item->asString();
        }

        $this->queryString = $newQueryString;
    }

    /**
     * Resolve a single node.
     *
     * @param Node $node The node to resolve.
     *
     * @return string
     */
    private function resolveNode(Node $node): string
    {
        $queryString = '';
        foreach ($node->getIterator() as $item) {
            if ($item instanceof Node) {
                $queryString .= $this->resolveNode($item);
                continue;
            }
            $queryString .= $item->asString();
        }

        return $this->resolveTag($queryString);
    }

    /**
     * Checkout insert tags and dissolve param::, secure::, other insert tags.
     *
     * @param string $tag The insert tag value to process (without the braces).
     *
     * @return string
     */
    private function resolveTag(string $tag): string
    {
        $parts = \explode('::', $tag, 2);
        if (!\array_key_exists(1, $parts)) {
            return $tag;
        }

        switch ($parts[0]) {
            case 'param':
                return $this->convertParameter($parts[1]);

            case 'secure':
                return $this->parseAndAddSecureInsertTagAsParameter($parts[1]);

            default:
                return $this->parseInsertTagsInternal('{{' . $tag . '}}');
        }
    }
}
