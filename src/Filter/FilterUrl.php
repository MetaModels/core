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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

/**
 * This represents a filter URL.
 */
class FilterUrl
{
    /**
     * The page array.
     *
     * @var string[]
     */
    private $page = [];

    /**
     * All parameters to be used as GET parameters.
     *
     * @var string[]
     */
    private $getParameters = [];

    /**
     * All parameters to be used as slug.
     *
     * @var string[]
     */
    private $slugParameters = [];

    /**
     * Create a new instance.
     *
     * @param string[] $page           The page.
     * @param string[] $getParameters  The get parameters.
     * @param string[] $slugParameters The slug parameters.
     */
    public function __construct(
        array $page = [],
        array $getParameters = [],
        array $slugParameters = []
    ) {
        $this->setPage($page);
        foreach ($getParameters as $name => $value) {
            $this->setGet($name, $value);
        }
        foreach ($slugParameters as $name => $value) {
            $this->setSlug($name, $value);
        }
    }

    /**
     * Create a clone of this instance.
     *
     * @return self
     */
    public function clone(): self
    {
        return new FilterUrl(
            $this->getPage(),
            $this->getGetParameters(),
            $this->getSlugParameters()
        );
    }

    /**
     * Set the target page.
     *
     * @param array $page The page.
     *
     * @return self
     */
    public function setPage(array $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Obtain the target page.
     *
     * @return array
     */
    public function getPage(): array
    {
        return $this->page;
    }

    /**
     * Set a value in the target page.
     *
     * @param string $name  The parameter name.
     * @param mixed  $value The parameter value.
     *
     * @return self
     */
    public function setPageValue(string $name, $value): self
    {
        if (empty($value)) {
            unset($this->page[$name]);
            return $this;
        }

        $this->page[$name] = $value;

        return $this;
    }

    /**
     * Obtain a value from the target page.
     *
     * @param string $name The parameter name.
     *
     * @return mixed
     */
    public function getPageValue(string $name)
    {
        return ($this->page[$name] ?? null);
    }

    /**
     * Add a slug parameter.
     *
     * @param string $name  The slug name.
     * @param string $value The slug value.
     *
     * @return self
     */
    public function setGet(string $name, string $value): self
    {
        if (empty($value)) {
            unset($this->getParameters[$name]);
            return $this;
        }

        $this->getParameters[$name] = $value;

        return $this;
    }

    /**
     * Get a slug parameter.
     *
     * @param string $name The slug name.
     *
     * @return string
     */
    public function getGet(string $name): ?string
    {
        return ($this->getParameters[$name] ?? null);
    }

    /**
     * Test if a slug parameter exists.
     *
     * @param string $name The slug name.
     *
     * @return bool
     */
    public function hasGet(string $name): bool
    {
        return array_key_exists($name, $this->getParameters);
    }

    /**
     * Obtain the slug parameters.
     *
     * @return array
     */
    public function getGetParameters(): array
    {
        return $this->getParameters;
    }

    /**
     * Add a slug parameter.
     *
     * @param string $name  The slug name.
     * @param string $value The slug value.
     *
     * @return self
     */
    public function setSlug(string $name, string $value): self
    {
        if (empty($value)) {
            unset($this->slugParameters[$name]);
            return $this;
        }
        $this->slugParameters[$name] = $value;

        return $this;
    }

    /**
     * Get a slug parameter.
     *
     * @param string $name The slug name.
     *
     * @return string
     */
    public function getSlug(string $name): ?string
    {
        return ($this->slugParameters[$name] ?? null);
    }

    /**
     * Test if a slug parameter exists.
     *
     * @param string $name The slug name.
     *
     * @return bool
     */
    public function hasSlug(string $name): bool
    {
        return array_key_exists($name, $this->slugParameters);
    }

    /**
     * Obtain the slug parameters.
     *
     * @return array
     */
    public function getSlugParameters(): array
    {
        return $this->slugParameters;
    }
}
