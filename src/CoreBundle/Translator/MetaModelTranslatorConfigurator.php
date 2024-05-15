<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Translator;

use MetaModels\IFactory;
use MetaModels\ITranslatedMetaModel;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Contracts\Cache\CacheInterface;

/** @psalm-type TDomainList=iterable<string, iterable<int, string>> */
final class MetaModelTranslatorConfigurator
{
    /** @var callable|null */
    private $previous;

    /**
     * The constructor.
     *
     * @param IFactory       $factory  The factory.
     * @param CacheInterface $cache    The cache.
     * @param callable|null  $previous The previous configurator.
     */
    public function __construct(
        private readonly IFactory $factory,
        private readonly CacheInterface $cache,
        $previous = null
    ) {
        if (null !== $previous && !is_callable($previous)) {
            throw new \InvalidArgumentException('Passed value for previous must be callable or null');
        }
        $this->previous = $previous;
    }

    /**
     * @param SymfonyTranslator $translator The translator.
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function __invoke(SymfonyTranslator $translator): void
    {
        // Apply previous configurator
        if (null !== $this->previous) {
            \call_user_func($this->previous, $translator);
        }

        foreach ($this->fetchDomains() as $domain => $locales) {
            foreach ($locales as $locale) {
                $translator->addResource('metamodels', $domain, $locale, $domain);
            }
        }
    }

    /**
     * Obtain the domain names with their locales.
     *
     * @return TDomainList
     * @throws InvalidArgumentException
     */
    private function fetchDomains(): iterable
    {
        return $this->cache->get(
            'metamodels.translation-domains',
            /** @return TDomainList */
            function (): iterable {
                $result = [];
                foreach ($this->factory->collectNames() as $metamodelName) {
                    $instance = $this->factory->getMetaModel($metamodelName);
                    if (!$instance instanceof ITranslatedMetaModel) {
                        $result[$metamodelName] = ['en'];
                        continue;
                    }
                    $locales = [];
                    foreach ($instance->getLanguages() as $language) {
                        $locales[] = $language;
                    }
                    // Fix: Always add 'en' to the language domains, even if user only set 'af_NA' by quick save.
                    if (!\in_array('en', $locales, true)) {
                        array_unshift($locales, 'en');
                    }

                    $result[$metamodelName] = $locales;
                }

                return $result;
            }
        );
    }
}
