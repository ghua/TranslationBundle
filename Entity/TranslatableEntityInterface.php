<?php
namespace VKR\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

interface TranslatableEntityInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param TranslationEntityInterface $translation
     * @return $this
     */
    public function addTranslation(TranslationEntityInterface $translation);

    /**
     * @param TranslationEntityInterface $translation
     */
    public function removeTranslation(TranslationEntityInterface $translation);

    /**
     * @return ArrayCollection|TranslationEntityInterface[]
     */
    public function getTranslations();

    /**
     * @param string $field
     * @return string|bool
     */
    public function getTranslationFallback($field = '');

    /**
     * @return array
     */
    public function getTranslatableFields();
}
