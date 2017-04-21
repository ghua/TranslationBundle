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
     * @var TranslationClassChecker
     */
    private $translationClassChecker;

    /**
     * @param EntityManager $entityManager
     *
     * @return $this;
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;

        return $this;
    }

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
     * @param TranslatableEntityInterface $record
     * @return TranslationEntityInterface|null
     */
    public function getFirstTranslation(TranslatableEntityInterface $record)
    {
        $translations = $record->getTranslations();
        if (!$translations->isEmpty()) {

            return $translations->first();
        } else {
            $translationClass = $this->translationClassChecker->checkTranslationClass($record);

            /**
             * @var TranslationEntityInterface $firstTranslation
             */
            $firstTranslation = $this->entityManager->getRepository($translationClass)
                ->findOneBy(['entity' => $record]);

            if ($firstTranslation) {

                return $firstTranslation;
            }
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
        $targetLanguage = $this->getLanguageByLocaleCode($locale);

        $translations = $record->getTranslations()
            ->filter(function (TranslationEntityInterface $translationEntity) use ($targetLanguage) {
                return $translationEntity->getLanguage() === $targetLanguage;
            });

        return $translations->isEmpty() ? null : $translations->first();
    }

    /**
     * @param string $locale
     * @throws TranslationException
     *
     * @return LanguageEntityInterface
     */
    private function getLanguageByLocaleCode($locale)
    {
        /** @var LanguageEntityInterface|null $language */
        $language = $this->entityManager
            ->getRepository(LanguageEntityInterface::class)
            ->findOneBy(['code' => $locale]);
        if (!$language) {
            throw new TranslationException("Locale $locale not found in the DB");
        }

        return $language;
    }
}
