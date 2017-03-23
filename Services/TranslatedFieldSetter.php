<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class TranslatedFieldSetter
{
    /**
     * @param TranslatableEntityInterface $translated
     * @param TranslationEntityInterface|null $translation
     * @return TranslatableEntityInterface
     * @throws TranslationException
     */
    public function setTranslatedFields(
        TranslatableEntityInterface $translated,
        TranslationEntityInterface $translation = null
    ) {
        if (!$translation) {
            return $translated;
        }
        $translatableFields = $this->checkTranslatableFields($translated);
        foreach ($translatableFields as $field) {
            $getter = 'get' . ucfirst($field);
            if (!method_exists($translation, $getter)) {
                throw new TranslationException(
                    'Method ' . $getter . ' must exist in class ' . get_class($translation)
                );
            }
            $setter = 'set' . ucfirst($field);
            if (!method_exists($translated, $setter)) {
                throw new TranslationException(
                    'Method ' . $setter . ' must exist in class ' . get_class($translated)
                );
            }
            $translated->$setter($translation->$getter());
        }
        return $translated;
    }

    /**
     * @param TranslatableEntityInterface $translated
     * @return array
     * @throws TranslationException
     */
    public function checkTranslatableFields(TranslatableEntityInterface $translated)
    {
        $translatableFields = $translated->getTranslatableFields();
        if (!is_array($translatableFields) || !sizeof($translatableFields)) {
            throw new TranslationException('getTranslatableFields() must return a non-empty array');
        }
        return $translatableFields;
    }
}
