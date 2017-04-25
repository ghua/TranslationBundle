<?php

namespace VKR\TranslationBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Repository\RepositoryFactory;
use Symfony\Component\DependencyInjection\Container;

class TranslatableRepositoryFactory implements RepositoryFactory
{

    /**
     * @var DefaultRepositoryFactory
     */
    private $defaultRepositoryFactory;

    /**
     * @var string
     */
    private $localeRetrieverServiceName;

    /**
     * @var Container
     */
    private $container;

    public function __construct()
    {
        $this->defaultRepositoryFactory = new DefaultRepositoryFactory();
    }

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
     * @param string $localeRetrieverServiceName
     *
     * @return $this
     */
    public function setLocaleRetrieverServiceName($localeRetrieverServiceName)
    {
        $this->localeRetrieverServiceName = $localeRetrieverServiceName;

        return $this;
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repository = $this->defaultRepositoryFactory->getRepository($entityManager, $entityName);

        if ($repository instanceof TranslatableEntityRepository) {
            $repository->setLocaleRetriever($this->container->get($this->localeRetrieverServiceName));
        }

        return $repository;
    }

}
