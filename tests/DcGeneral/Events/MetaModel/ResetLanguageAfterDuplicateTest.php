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

namespace MetaModels\Test\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\SessionStorageInterface;
use MetaModels\DcGeneral\Events\MetaModel\ResetLanguageAfterDuplicate;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MetaModels\DcGeneral\Events\MetaModel\ResetLanguageAfterDuplicate
 */
class ResetLanguageAfterDuplicateTest extends TestCase
{
    /**
     * Build a PostDuplicateModelEvent for the given provider name.
     *
     * @param string $providerName The MetaModel table name.
     * @param SessionStorageInterface $sessionStorage The session storage mock.
     *
     * @return PostDuplicateModelEvent
     */
    private function buildEvent(string $providerName, SessionStorageInterface $sessionStorage): PostDuplicateModelEvent
    {
        /** @var SessionStorageInterface&MockObject $sessionStorage */
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);
        $environment->method('getSessionStorage')->willReturn($sessionStorage);

        $sourceModel = $this->getMockForAbstractClass(ModelInterface::class);
        $sourceModel->method('getId')->willReturn('1');

        $newModel = $this->getMockForAbstractClass(ModelInterface::class);
        $newModel->method('getId')->willReturn('2');
        $newModel->method('getProviderName')->willReturn($providerName);

        return new PostDuplicateModelEvent($environment, $newModel, $sourceModel);
    }

    /**
     * Build a ResetLanguageAfterDuplicate listener that returns the given MetaModel from the factory.
     *
     * @param IMetaModel|null $metaModel The MetaModel to return, or null.
     * @param string          $providerName The provider name used for factory lookup.
     *
     * @return ResetLanguageAfterDuplicate
     */
    private function buildListener(
        ?IMetaModel $metaModel,
        string $providerName = 'mm_test'
    ): ResetLanguageAfterDuplicate {
        /** @var IFactory&MockObject $factory */
        $factory = $this->getMockForAbstractClass(IFactory::class);
        $factory->method('getMetaModel')->with($providerName)->willReturn($metaModel);

        return new ResetLanguageAfterDuplicate($factory);
    }

    /**
     * Nothing happens when the MetaModel is not a translated MetaModel.
     */
    public function testHandleDoesNothingForNonTranslatedMetaModel(): void
    {
        /** @var SessionStorageInterface&MockObject $sessionStorage */
        $sessionStorage = $this->getMockForAbstractClass(SessionStorageInterface::class);
        $sessionStorage->expects(self::never())->method('set');

        /** @var IMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('mm_test', $sessionStorage));
    }

    /**
     * Nothing happens when the MetaModel cannot be found.
     */
    public function testHandleDoesNothingWhenMetaModelNotFound(): void
    {
        /** @var SessionStorageInterface&MockObject $sessionStorage */
        $sessionStorage = $this->getMockForAbstractClass(SessionStorageInterface::class);
        $sessionStorage->expects(self::never())->method('set');

        $listener = $this->buildListener(null);
        $listener->handle($this->buildEvent('mm_test', $sessionStorage));
    }

    /**
     * The fallback language is written into the session for the provider name.
     */
    public function testHandleWritesFallbackLanguageToSession(): void
    {
        $providerName    = 'mm_test';
        $fallbackLanguage = 'de';

        /** @var SessionStorageInterface&MockObject $sessionStorage */
        $sessionStorage = $this->getMockForAbstractClass(SessionStorageInterface::class);
        $sessionStorage->method('get')->with('dc_general')->willReturn([]);
        $sessionStorage
            ->expects(self::once())
            ->method('set')
            ->with(
                'dc_general',
                self::callback(static function (array $session) use ($providerName, $fallbackLanguage): bool {
                    return ($session['ml_support'][$providerName] ?? null) === $fallbackLanguage;
                })
            );

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getMainLanguage')->willReturn($fallbackLanguage);

        $listener = $this->buildListener($metaModel, $providerName);
        $listener->handle($this->buildEvent($providerName, $sessionStorage));
    }

    /**
     * Existing session data is preserved; only the ml_support entry for the provider is overwritten.
     */
    public function testHandlePreservesExistingSessionData(): void
    {
        $providerName    = 'mm_news';
        $fallbackLanguage = 'en';

        /** @var SessionStorageInterface&MockObject $sessionStorage */
        $sessionStorage = $this->getMockForAbstractClass(SessionStorageInterface::class);
        $sessionStorage->method('get')->with('dc_general')->willReturn([
            'ml_support' => ['mm_other' => 'fr'],
            'some_key'   => 'some_value',
        ]);
        $sessionStorage
            ->expects(self::once())
            ->method('set')
            ->with(
                'dc_general',
                self::callback(
                    static function (array $session) use ($providerName, $fallbackLanguage): bool {
                        return ($session['ml_support'][$providerName] ?? null) === $fallbackLanguage
                            && ($session['ml_support']['mm_other'] ?? null) === 'fr'
                            && ($session['some_key'] ?? null) === 'some_value';
                    }
                )
            );

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getMainLanguage')->willReturn($fallbackLanguage);

        $listener = $this->buildListener($metaModel, $providerName);
        $listener->handle($this->buildEvent($providerName, $sessionStorage));
    }
}
