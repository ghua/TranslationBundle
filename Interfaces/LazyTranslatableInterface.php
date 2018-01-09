<?php
namespace VKR\TranslationBundle\Interfaces;

use VKR\TranslationBundle\Entity\TranslationEntityInterface;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
interface LazyTranslatableInterface
{
    /**
     * @return TranslationEntityInterface
     */
    public function getTranslation();

    /**
     * @param TranslationEntityInterface $translation
     */
    public function setTranslation($translation);
}