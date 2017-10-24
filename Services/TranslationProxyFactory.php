<?php


namespace VKR\TranslationBundle\Services;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\Container;
use VKR\TranslationBundle\Entity\LazyTranslatableTrait;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use VKR\TranslationBundle\Interfaces\LazyTranslatableInterface;

class TranslationProxyFactory implements EventSubscriber
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     *
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

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
            $this->container->get('vkr_translation.class_checker')
                ->checkTranslationClass($entity);
        } catch (TranslationException $e) {

            return false;
        }

        /**
         * @var TranslationManager $translator
         */
        $translator = $this->container->get('vkr_translation.translation_manager');

        if ($entity instanceof LazyTranslatableInterface) {

            $proxy = $this->createProxy($entity, $translator);
            $entity->setTranslation($proxy);

        } else {

            $entityReflection = new \ReflectionClass($entity);

            if (!in_array(LazyTranslatableTrait::class, $entityReflection->getTraitNames(), true)) {

                return false;
            }

            $proxy = $this->createProxy($entity, $translator);

            $propertyReflection = $entityReflection->getProperty('translation');
            $propertyReflection->setAccessible(true);
            $propertyReflection->setValue($entity, $proxy);
        }

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
