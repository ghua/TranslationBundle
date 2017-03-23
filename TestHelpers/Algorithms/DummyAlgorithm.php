<?php
namespace VKR\TranslationBundle\TestHelpers\Algorithms;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

class DummyAlgorithm implements TranslationAlgorithmInterface
{
    public function getTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale = null)
    {
        $translation = new DummyTranslations();
        $translation->setField1('foo');
        return $translation;
    }
}
