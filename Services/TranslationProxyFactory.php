<?php


namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\LazyTranslatableTrait;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class TranslationProxyFactory
{
    /**
     * @var TranslationClassChecker
     */
    private $translationClassChecker;

    /**
     * @var TranslationManager
     */
    private $translationManager;

    /**
     * TranslationProxy constructor.
     *
     * @param TranslationClassChecker     $translationClassChecker
     * @param TranslationManager          $translationManager
     */
    public function __construct(TranslationClassChecker $translationClassChecker, TranslationManager $translationManager)
    {
        $this->translationClassChecker = $translationClassChecker;
        $this->translationManager = $translationManager;
    }

    /**
     * @param TranslatableEntityInterface $entity
     *
     * @return bool
     */
    public function initialize(TranslatableEntityInterface $entity)
    {
        try {
            $this->translationClassChecker->checkTranslationClass($entity);
        } catch (TranslationException $e) {

            return false;
        }

        $entityReflection = new \ReflectionClass($entity);

        if (!in_array(LazyTranslatableTrait::class, $entityReflection->getTraitNames(), true)) {

            return false;
        }

        $proxy = $this->createProxy($entity, $this->translationManager);

        $propertyReflection = $entityReflection->getProperty('translation');
        $propertyReflection->setAccessible(true);
        $propertyReflection->setValue($entity, $proxy);

        return true;
    }

    private function createProxy(TranslatableEntityInterface $entity, TranslationManager $translationManager) {

        return new class($entity, $translationManager) {

            /**
             * @var TranslatableEntityInterface
             */
            private $entity;

            /**
             * @var \ReflectionClass
             */
            private $entityReflection;

            /**
             * @var TranslationManager
             */
            private $translationManager;

            public function __construct(TranslatableEntityInterface $entity, TranslationManager $translationManager)
            {
                $this->entity = $entity;
                $this->entityReflection = new \ReflectionClass($entity);
                $this->translationManager = $translationManager;
            }

            public function __call($name, $arguments)
            {
                $propertyReflection = $this->entityReflection->getProperty('translation');
                $propertyReflection->setAccessible(true);

                $translation = $this->translationManager->getTranslation($this->entity);
                $propertyReflection->setValue($this->entity, $translation);

                return $translation->$name();
            }
        };

    }

}
