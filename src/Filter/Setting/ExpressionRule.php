<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\Message;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\ExpressionRule as FilterRuleExpression;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;
use Override;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_merge;
use function count;

/**
 * This filter condition generates a filter rule, that represents a simple "if this then that else that".
 */
final class ExpressionRule implements IWithChildren
{
    /** @var list<ISimple> */
    private array $children = [];

    public function __construct(
        private readonly array $data,
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly RequestStack $requestStack,
        private readonly IMetaModel $metaModel,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Override]
    public function prepareRules(IFilter $objFilter, $arrFilterUrl): void
    {
        $ifTrue = null;
        if ($this->children[0] ?? null) {
            $ifTrue = $this->metaModel->getEmptyFilter();
            $this->children[0]->prepareRules($ifTrue, $arrFilterUrl);
        }

        $ifFalse = null;
        if ($child = ($this->children[1] ?? null)) {
            $ifFalse = $this->metaModel->getEmptyFilter();
            $child->prepareRules($ifFalse, $arrFilterUrl);
        }

        $filterRule = new FilterRuleExpression(
            $this->getExpression(),
            $this->getExpressionParameters($arrFilterUrl),
            $this->expressionLanguage,
            $ifTrue,
            $ifFalse,
        );

        $objFilter->addFilterRule($filterRule);
    }

    #[Override]
    public function addChild(ISimple $objFilterSetting): void
    {
        if (count($this->children) >= 2) {
            // FIXME: call getTypeName() for name.
            $typeName = 'expression_rule';
            Message::addInfo(
                $this->translator->trans(
                    'error.condition_max_children',
                    ['%name%' => $typeName, '%max%' => '2'],
                    'tl_metamodel_filtersetting'
                )
            );

            return;
        }

        $this->children[] = $objFilterSetting;
    }

    #[Override]
    public function get($strKey): mixed
    {
        return $this->data[$strKey] ?? null;
    }

    #[Override]
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting): array
    {
        $result = [];
        foreach ($this->children as $child) {
            $result[] = $child->generateFilterUrlFrom($objItem, $objRenderSetting);
        }

        return array_merge(...$result);
    }

    #[Override]
    public function getParameters(): array
    {
        $parameters = [];
        foreach ($this->children as $child) {
            $parameters[] = $child->getParameters();
        }

        return array_merge(...$parameters);
    }

    #[Override]
    public function getParameterDCA(): array
    {
        $parameters = [];
        foreach ($this->children as $child) {
            $parameters[] = $child->getParameterDCA();
        }

        return array_merge(...$parameters);
    }

    #[Override]
    public function getParameterFilterNames(): array
    {
        $parameters = [];
        foreach ($this->children as $objSetting) {
            $parameters[] = $objSetting->getParameterFilterNames();
        }

        return array_merge(...$parameters);
    }

    /** @SuppressWarnings(PHPMD.LongVariable) */
    #[Override]
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ): array {
        if (null !== ($child = ($this->children[0] ?? null))) {
            return $child->getParameterFilterWidgets(
                $this->isConditionFulfilled($arrFilterUrl) && ((bool) $this->get('onlypossible')) ? $arrIds : null,
                $arrFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions
            );
        }
        // TODO: Add the option to hide or show filter widgets on the front end.
        // $child = $this->getFilterForConditionState($arrFilterUrl);
        // if ($child) {
        //     return $child->getParameterFilterWidgets(
        //         ((bool) $this->get('onlypossible')) ? $arrIds : null,
        //         $arrFilterUrl,
        //         $arrJumpTo,
        //         $objFrontendFilterOptions
        //     );
        // }

        return [];
    }

    #[Override]
    public function getReferencedAttributes(): array
    {
        $attributes = [];
        foreach ($this->children as $child) {
            $attributes[] = $child->getReferencedAttributes();
        }

        return array_merge(...$attributes);
    }

// TODO: Add the option to hide or show filter widgets on the front end - see above.
/*    private function getFilterForConditionState(array $filterUrl): ?ISimple
    {
        if ($this->isConditionFulfilled($filterUrl)) {
            return $this->children[0] ?? null;
        }

        return $this->children[1] ?? null;
    }
*/

    private function isConditionFulfilled(array $filterUrl): bool
    {
        return (bool) $this->expressionLanguage->evaluate(
            $this->getExpression(),
            $this->getExpressionParameters($filterUrl)
        );
    }

    private function getExpression(): string
    {
        return (string) $this->data['expression_rule'];
    }

    private function getExpressionParameters(array $arrFilterUrl): array
    {
        return [
            'filterUrl' => $arrFilterUrl,
            'request'   => $this->requestStack->getCurrentRequest(),
        ];
    }
}
