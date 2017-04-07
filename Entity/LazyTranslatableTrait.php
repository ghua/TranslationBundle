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
        if (is_callable(array($this->translation, '__fetch'))) {

            $this->translation = $this->translation->__fetch();
        }

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
