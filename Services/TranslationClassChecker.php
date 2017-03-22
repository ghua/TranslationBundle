<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class TranslationClassChecker
{
    /**
     * @param TranslatableEntityInterface $entity
     * @return string
     * @throws TranslationException
     */
    public function checkTranslationClass(TranslatableEntityInterface $entity)
    {
        $translationClass = get_class($entity) . 'Translations';
        if (!class_exists($translationClass)) {
            throw new TranslationException("Class $translationClass does not exist");
        }
        $reflection = new \ReflectionClass($translationClass);
        if (!$reflection->isInstantiable()) {
            throw new TranslationException("Class $translationClass cannot be instantiated");
        }
        if ($reflection->getConstructor() && $reflection->getConstructor()->getNumberOfRequiredParameters()) {
            throw new TranslationException("Class $translationClass requires constructor arguments");
        }
        if (!$reflection->isSubclassOf(TranslationEntityInterface::class)) {
            throw new TranslationException("Class $translationClass does not implement " . TranslationEntityInterface::class);
        }
        return $translationClass;
    }
}
