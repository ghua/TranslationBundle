<?php
namespace VKR\TranslationBundle\Interfaces;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

interface TranslationAlgorithmInterface
{
    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @param string|null $fallbackLocale
     * @return TranslationEntityInterface
     * @throws TranslationException
     */
    public function getTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale = null);
}
