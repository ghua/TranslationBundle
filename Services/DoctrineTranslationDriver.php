<?php
namespace VKR\TranslationBundle\Services;

use Doctrine\ORM\EntityManager;
use VKR\TranslationBundle\Entity\LanguageEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

class DoctrineTranslationDriver
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $languageEntityName;

    public function __construct(EntityManager $entityManager, $languageEntityName)
    {
        $this->entityManager = $entityManager;
        $this->languageEntityName = $languageEntityName;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @return TranslationEntityInterface|null
     */
    public function getFirstTranslation(TranslatableEntityInterface $record)
    {
        $translations = $record->getTranslations();
        if (isset($translations[0])) {
            return $translations[0];
        }
        return null;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @return null|TranslationEntityInterface
     */
    public function getTranslation(TranslatableEntityInterface $record, $locale)
    {
        $this->checkLocale($locale);
        $translations = $record->getTranslations();
        foreach ($translations as $translation) {
            if ($translation->getLanguage()->getCode() == $locale) {
                return $translation;
            }
        }
        return null;
    }

    /**
     * @param string $locale
     * @throws TranslationException
     */
    private function checkLocale($locale)
    {
        /** @var LanguageEntityInterface|null $language */
        $language = $this->entityManager->getRepository($this->languageEntityName)
            ->findOneBy(['code' => $locale]);
        if (!$language) {
            throw new TranslationException("Locale $locale not found in the DB");
        }
    }
}
