<?php
namespace VKR\TranslationBundle\Entity;

interface TranslationEntityInterface
{
    /**
     * @param LanguageEntityInterface $language
     * @return $this
     */
    public function setLanguage(LanguageEntityInterface $language);

    /**
     * @return LanguageEntityInterface
     */
    public function getLanguage();

    /**
     * @param TranslatableEntityInterface $entity
     * @return $this
     */
    public function setEntity(TranslatableEntityInterface $entity);

    /**
     * @return TranslatableEntityInterface
     */
    public function getEntity();
}
