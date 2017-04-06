<?php


namespace VKR\TranslationBundle\Entity;

trait LazyTranslatableTrait
{

    /**
     * @var TranslationEntityInterface
     */
    protected $translation;

    /**
     * @return TranslationEntityInterface
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * @param TranslationEntityInterface $translation
     *
     * @return $this
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

}
