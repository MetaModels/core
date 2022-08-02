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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Translator;

use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class Translator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface, LegacyTranslatorInterface
{
    /**
     * @var TranslatorInterface|TranslatorBagInterface|LegacyTranslatorInterface
     */
    private $translator;

    /**
     * @internal Do not inherit from this class; decorate the "contao.translation.translator" service instead
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * Gets the translation from Contao’s $GLOBALS['TL_LANG'] array if the message
     * domain starts with "contao_". The locale parameter is ignored in this case.
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null): string
    {
        // Cut off the contao_ prefix for mm_ domains as they are already loaded via symfony.
        if (0 === strncmp($domain, 'contao_mm_', 10)) {
            $domain = substr($domain, 7);
            dump('REDIRECTED TO: ' . $domain);
        }
        // Forward to Contao translator
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null): string
    {
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    public function setLocale($locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }

    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }
}
