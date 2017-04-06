<?php

namespace VKR\TranslationBundle\Services;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class TranslationEventSubscriber implements EventSubscriber
{

    /**
     * @var TranslationProxyFactory
     */
    private $translationProxyFactory;

    /**
     * @param TranslationProxyFactory $translationProxyFactory
     *
     * @return $this
     */
    public function setTranslationProxyFactory($translationProxyFactory)
    {
        $this->translationProxyFactory = $translationProxyFactory;

        return $this;
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
            $this->translationProxyFactory->initialize($entity);
        } catch (TranslationException $e) {
        }
    }

}
