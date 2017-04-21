<?php


namespace VKR\TranslationBundle\Services;

use Doctrine\Common\EventSubscriber;
use VKR\TranslationBundle\Entity\LazyTranslatableTrait;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use Doctrine\ORM\Event\LifecycleEventArgs;

class TranslationProxyFactory implements EventSubscriber
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
     * @param TranslationClassChecker $translationClassChecker
     *
     * @return $this;
     */
    public function setTranslationClassChecker($translationClassChecker)
    {
        $this->translationClassChecker = $translationClassChecker;

        return $this;
    }

    /**
     * @param TranslationManager $translationManager
     *
     * @return $this;
     */
    public function setTranslationManager($translationManager)
    {
        $this->translationManager = $translationManager;

        return $this;
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

            /**
             * @var bool
             */
            private $isLoaded = false;

            public function __construct(TranslatableEntityInterface $entity, TranslationManager $translationManager)
            {
                $this->entity = $entity;
                $this->entityReflection = new \ReflectionClass($entity);
                $this->translationManager = $translationManager;
            }

            /**
             * @param bool $isLoaded
             *
             * @return $this
             */
            public function setIsLoaded($isLoaded)
            {
                $this->isLoaded = $isLoaded;

                return $this;
            }

            public function __fetch()
            {
                if (true === $this->isLoaded) {

                    return null;
                }

                $this->isLoaded = true;

                $propertyReflection = $this->entityReflection->getProperty('translation');
                $propertyReflection->setAccessible(true);

                $translation = $this->translationManager->getTranslation($this->entity);
                $propertyReflection->setValue($this->entity, $translation);

                return $translation;
            }

            public function __call($name, $arguments)
            {
                $translation =  $this->__fetch();

                if (is_callable(array($translation, $name))) {
                    return $translation->$name();
                }

                throw new \LogicException();
            }
        };

    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad'
        );
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if (!($entity instanceof TranslatableEntityInterface)) {
            return;
        }

        try {
            $this->initialize($entity);
        } catch (TranslationException $e) {
        }
    }

}
