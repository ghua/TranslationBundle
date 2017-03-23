<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class EntityTranslationDriver
{
    /** @var TranslationClassChecker */
    private $translationClassChecker;

    public function __construct(TranslationClassChecker $translationClassChecker)
    {
        $this->translationClassChecker = $translationClassChecker;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @return TranslationEntityInterface
     * @throws TranslationException
     */
    public function getTranslation(TranslatableEntityInterface $record)
    {
        if ($record->getTranslationFallback() === false) {
            return null;
        }
        $translationClass = $this->translationClassChecker->checkTranslationClass($record);
        $newTranslation = new $translationClass();
        $translatableFields = $record->getTranslatableFields();
        foreach ($translatableFields as $field) {
            $setterName = 'set' . ucfirst($field);
            if (!method_exists($newTranslation, $setterName)) {
                throw new TranslationException("Method $setterName not found in class $translationClass");
            }
            $fallback = $record->getTranslationFallback($field);
            $newTranslation->$setterName($fallback);
        }
        return $newTranslation;
    }
}
