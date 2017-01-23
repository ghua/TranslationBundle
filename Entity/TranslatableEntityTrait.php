<?php
namespace VKR\TranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

trait TranslatableEntityTrait
{
    /**
     * @var ArrayCollection|TranslationEntityInterface[]
     */
    protected $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * @param TranslationEntityInterface $translation
     * @return $this
     */
    public function addTranslation(TranslationEntityInterface $translation)
    {
        $this->translations[] = $translation;
        if ($this instanceof TranslatableEntityInterface) {
            $translation->setEntity($this);
        }
        return $this;
    }

    /**
     * @param TranslationEntityInterface $translation
     */
    public function removeTranslation(TranslationEntityInterface $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * @return ArrayCollection|TranslationEntityInterface[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $field
     * @return string|bool
     */
    public function getTranslationFallback($field = '')
    {
        return false;
    }
}
