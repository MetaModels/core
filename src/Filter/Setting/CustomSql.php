<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
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
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
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
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_reduce;
use function array_shift;
use function array_values;
use function call_user_func;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_callable;
use function iterator_to_array;
use function parse_str;
use function preg_match_all;
use function rtrim;
use function sprintf;
use function str_repeat;
use function strtolower;
use function unserialize;

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class CustomSql implements ISimple, ServiceSubscriberInterface
{
    /**
     * The parenting filter setting container this setting belongs to.
     *
     * @var ICollection
     */
    private ICollection $collection;

    /**
     * The attributes of this filter setting.
     *
     * @var array<string, mixed>
     */
    private array $data;

    /**
     * The filter params.
     *
     * @var array<string, mixed>
     */
    private array $filterParameters = [];

    /**
     * The query string.
     *
     * @var string
     */
    private string $queryString;

    /**
     * The query parameters.
     *
     * @var list<mixed>
     */
    private array $queryParameter;

    /**
     * The service container.
     *
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Constructor - initialize the object and store the parameters.
     *
     * @param ICollection          $collection The parenting filter settings object.
     * @param array<string, mixed> $data       The attributes for this filter setting.
     * @param ContainerInterface   $container  The service container.
     *
     * @throws InvalidArgumentException When a service is missing.
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
            throw new InvalidArgumentException(
                'The service container is missing the following services: ' . implode(', ', $missing)
            );
        }
        $this->container = $container;
    }

    /**
     * Get the needed services.
     *
     * @return array<string, class-string>
     */
    public static function getSubscribedServices(): array
    {
        return [
            Connection::class                  => Connection::class,
            InsertTagParser::class             => InsertTagParser::class,
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
        return $this->data[$strKey] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $scopeDeterminator = System::getContainer()?->get('cca.dc-general.scope-matcher');
        assert($scopeDeterminator instanceof RequestScopeDeterminator);

        $useOnlyAtEnv = $this->get('use_only_in_env') ?? false;

        if (!$useOnlyAtEnv
            || (
                ('only_backend' === $useOnlyAtEnv && $scopeDeterminator->currentScopeIsBackend())
                || ('only_frontend' === $useOnlyAtEnv && $scopeDeterminator->currentScopeIsFrontend())
            )
        ) {
            $this->filterParameters = $arrFilterUrl;
            $this->queryString      = $this->get('customsql');
            $this->queryParameter   = [];

            $objFilter->addFilterRule($this->getFilterRule());

            $this->filterParameters = [];
            $this->queryString      = '';
            $this->queryParameter   = [];

            return;
        }

        $this->filterParameters = [];
        $this->queryString      = '';
        $this->queryParameter   = [];
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

        preg_match_all('@\{\{param::filter\?([^}]*)}}@', $this->get('customsql'), $arrMatches);
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
     *
     * @SuppressWarnings(PHPMD.LongVariable)
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
     * @param list<mixed> $parameters The parameters to add.
     *
     * @return void
     */
    private function addParameters(array $parameters): void
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
    private function addParameter(string $parameter): void
    {
        $this->queryParameter[] = $this->parseInsertTagsInternal($parameter);
    }

    /**
     * Replace the table name in the query string.
     *
     * @return void
     */
    private function parseTable(): void
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
    private function getValueFromServiceContainer(string $valueName, array $arguments): mixed
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
     * @param string $valueName The name of the value in the source to retrieve.
     * @param array  $arguments The arguments of the parameter.
     *
     * @return mixed
     */
    private function getValueFromSource(string $source, string $valueName, array $arguments): mixed
    {
        if (strtolower($source) === 'container') {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Getting filter values from the service container is deprecated, the container will get removed.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            return $this->getValueFromServiceContainer($valueName, $arguments);
        }

        return match (strtolower($source)) {
            'get', 'post', 'cookie', 'session' => $this->executeInsertTagReplaceParam($source, $arguments),
            'filter' => $this->filterParameters[$valueName] ?? null,
            // Unknown sources always resort to null.
            default => null
        };
    }

    /**
     * Execute the insert tag for replace parameters.
     *
     * @param string $source    The source.
     * @param array  $arguments The arguments.
     *
     * @return mixed|string
     */
    private function executeInsertTagReplaceParam(string $source, array $arguments): mixed
    {
        $filteredArguments = array_intersect_key($arguments, array_flip(['name', 'default']));
        $imploded          = array_reduce(
            array_keys($filteredArguments),
            static function ($carry, $item) use ($filteredArguments) {
                return $carry . ($carry ? '&' : '') . $item . '=' . $filteredArguments[$item];
            },
            ''
        );

        $result = $this->container->get(ReplaceParam::class)
            ->replace(sprintf('{{param::%s?%s}}', $source, $imploded));

        // @codingStandardsIgnoreStart
        return (($results = @unserialize($result, ['allowed_classes' => false])) ? $results : $result);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Convert a parameter using an aggregate function.
     *
     * @param array $var       The parameter value.
     * @param array $arguments The arguments of the parameter.
     *
     * @return string
     */
    private function convertParameterAggregate(array $var, array $arguments): string
    {
        if (!empty($arguments['recursive'])) {
            $var = iterator_to_array(new RecursiveIteratorIterator(new RecursiveArrayIterator($var)));
        }

        if ([] === $var) {
            return 'NULL';
        }

        if ($arguments['aggregate'] === 'list') {
            $var = array_merge(
                ...array_map(static fn (string $value): array => explode(',', $value), array_values($var))
            );
        }

        if (!empty($arguments['key'])) {
            $var = array_keys($var);
        } else {
            // Use values.
            $var = array_values($var);
        }

        if (!in_array($arguments['aggregate'], ['set', 'list'], true)) {
            $this->addParameter(implode(',', $var));

            return '?';
        }

        $this->addParameters($var);

        return rtrim(str_repeat('?,', count($var)), ',');
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
        [$strSource, $strQuery] = explode('?', $strMatch, 2) + ['', ''];
        parse_str($strQuery, $arrArgs);
        $arrName = (array) $arrArgs['name'];

        $var = $this->getValueFromSource($strSource, array_shift($arrName), $arrArgs);

        $index = 0;
        $count = count($arrName);
        while ($index < $count && is_array($var)) {
            $var = $var[$arrName[$index++]];
        }

        if ($index !== $count || $var === null) {
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
        return $this->container->get(InsertTagParser::class)->replace($queryString, false);
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
        $parts = explode('::', $tag, 2);
        if (!array_key_exists(1, $parts)) {
            return $this->parseInsertTagsInternal('{{' . $tag . '}}');
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
