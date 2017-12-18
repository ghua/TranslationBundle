<?php
/**
 * Created by anonymous
 * Date: 18/12/17
 * Time: 12:36
 */

namespace VKR\TranslationBundle\Entity;

/**
 * Trait GoogleTranslatableEntityTrait
 */
trait GoogleTranslatableEntityTrait
{
    /**
     * @var bool
     */
    protected $translatedByGoogle = false;

    /**
     * @return bool
     */
    public function isTranslatedByGoogle()
    {
        return $this->translatedByGoogle;
    }

    /**
     * @param bool $translatedByGoogle
     *
     * @return GoogleTranslatableEntityTrait
     */
    public function setTranslatedByGoogle($translatedByGoogle)
    {
        $this->translatedByGoogle = $translatedByGoogle;

        return $this;
    }
}
