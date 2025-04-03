<?php

declare(strict_types=1);

namespace MetaModels\CoreBundle\Contao\Picker;

use Contao\CoreBundle\Picker\PickerConfig;
use Contao\CoreBundle\Picker\PickerProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Picker\IdTranscoderInterface;
use ContaoCommunityAlliance\DcGeneral\Picker\IdTranscodingPickerProviderInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

use function strtr;

class MetaModelsJumpToPickerProvider implements PickerProviderInterface, IdTranscodingPickerProviderInterface
{
    public function __construct(
        private readonly FactoryInterface $menuFactory,
        private readonly RouterInterface $router,
        private readonly TranslatorInterface $translator,
        private readonly string $tableName,
        private readonly string $renderSettingId,
        private readonly ?string $linkIcon,
    ) {
    }

    public function getName(): string
    {
        return 'metamodelPicker_' . $this->tableName . '_' . $this->renderSettingId;
    }

    public function getUrl(PickerConfig $config): ?string
    {
        return $this->generateUrl($config);
    }

    public function createMenuItem(PickerConfig $config): ItemInterface
    {
        $label = $this->translator->trans('name', [], $this->tableName);

        $attributes = ['class' => $this->tableName];
        if (null !== $this->linkIcon) {
            $attributes['style'] = strtr('background-image: url(:icon-url:)', [':icon-url:' => $this->linkIcon]);
        }

        return $this->menuFactory->createItem($this->tableName, [
            'label'          => ('name' !== $label) ? $label : $this->tableName,
            'linkAttributes' => $attributes,
            'current'        => $this->isCurrent($config),
            'uri'            => $this->generateUrl($config),
        ]);
    }

    public function supportsContext($context): bool
    {
        return 'link' === $context;
    }

    public function supportsValue(PickerConfig $config): bool
    {
        try {
            $this->createIdTranscoder($config)->decode($config->getValue());
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function isCurrent(PickerConfig $config): bool
    {
        return $config->getCurrent() === $this->getName();
    }

    public function createIdTranscoder(PickerConfig $config): IdTranscoderInterface
    {
        return new InsertTagIdTranscoder($this->tableName, $this->renderSettingId);
    }

    private function generateUrl(PickerConfig $config): ?string
    {
        $newConfig = $config->cloneForCurrent($this->getName());
        $newConfig->setExtra('sourceName', $this->tableName);
        $params = [
            'fieldType'    => 'radio',
            'picker'       => $newConfig->urlEncode(),
            'propertyName' => 'id',
        ];

        return $this->router->generate('cca_dc_general_picker_tree', $params);
    }
}
