<?php
/**
 * Created by anonymous
 * Date: 18/12/17
 * Time: 12:51
 */

namespace VKR\TranslationBundle\Entity;

/**
 * Interface GoogleTranslatableEntityInterface
 */
interface GoogleTranslatableEntityInterface
{
    /**
     * @return bool
     */
    public function isTranslatedByGoogle();

    /**
     * @param bool $translatedByGoogle
     *
     * @return GoogleTranslatableEntityTrait
     */
    public function setTranslatedByGoogle($translatedByGoogle);
}
