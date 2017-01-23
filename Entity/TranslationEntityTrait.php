<?php
namespace VKR\TranslationBundle\Entity;

trait TranslationEntityTrait
{
    /**
     * @var LanguageEntityInterface
     */
    protected $language;

    /**
     * @var TranslatableEntityInterface
     */
    protected $entity;

    /**
     * @param LanguageEntityInterface $language
     * @return $this
     */
    public function setLanguage(LanguageEntityInterface $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return LanguageEntityInterface
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param TranslatableEntityInterface $entity
     * @return $this
     */
    public function setEntity(TranslatableEntityInterface $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return TranslatableEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
