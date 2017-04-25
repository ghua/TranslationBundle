<?php


namespace Acme\Service;

use Doctrine\ORM\EntityManager;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;

class LocaleRetriever implements LocaleRetrieverInterface
{

    const DEFAULT_LOCALE = 'en';

    private $currentLocale = self::DEFAULT_LOCALE;

    public function getCurrentLocale()
    {
        return $this->currentLocale;
    }

    public function getDefaultLocale()
    {
        return self::DEFAULT_LOCALE;
    }

    /**
     * @param string $currentLocale
     *
     * @return $this;
     */
    public function setCurrentLocale($currentLocale)
    {
        $this->currentLocale = $currentLocale;

        return $this;
    }

    public function __construct(EntityManager $entityManager)
    {
    }

}