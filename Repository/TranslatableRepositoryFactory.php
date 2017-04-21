<?php

namespace VKR\TranslationBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Repository\RepositoryFactory;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;

class TranslatableRepositoryFactory implements RepositoryFactory
{

    /**
     * @var DefaultRepositoryFactory
     */
    private $defaultRepositoryFactory;

    /**
     * @var LocaleRetrieverInterface
     */
    private $localeRetriever;

    public function __construct()
    {
        $this->defaultRepositoryFactory = new DefaultRepositoryFactory();
    }

    /**
     * @param LocaleRetrieverInterface $localeRetriever
     *
     * @return $this;
     */
    public function setLocaleRetriever($localeRetriever)
    {
        $this->localeRetriever = $localeRetriever;

        return $this;
    }

    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $repository = $this->defaultRepositoryFactory->getRepository($entityManager, $entityName);

        if ($repository instanceof TranslatableEntityRepository) {
            $repository->setLocaleRetriever($this->localeRetriever);
        }

        return $repository;
    }

}
