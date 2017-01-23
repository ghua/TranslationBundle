<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;

class TranslationRetriever
{
    /**
     * @var DoctrineTranslationDriver
     */
    private $doctrineTranslationDriver;

    /**
     * @var GoogleTranslationDriver
     */
    private $googleTranslationDriver;

    public function __construct(
        DoctrineTranslationDriver $doctrineTranslationDriver,
        GoogleTranslationDriver $googleTranslationDriver
    ) {
        $this->doctrineTranslationDriver = $doctrineTranslationDriver;
        $this->googleTranslationDriver = $googleTranslationDriver;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @param string $fallbackLocale
     * @return null|TranslationEntityInterface
     */
    public function getActiveTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale)
    {
        $currentLocaleTranslation = $this->doctrineTranslationDriver->getTranslation($record, $locale);
        if ($currentLocaleTranslation) {
            return $currentLocaleTranslation;
        }
        $fallbackTranslation = $this->getFallbackTranslation($record, $fallbackLocale);
        if (!$fallbackTranslation) {
            return null;
        }
        if (method_exists($record, 'isGoogleTranslatable') && $record->isGoogleTranslatable()) {
            $googleTranslation = $this->googleTranslationDriver
                ->getTranslation($record, $locale, $fallbackTranslation);
            if (!$googleTranslation) {
                $googleTranslation = $this->googleTranslationDriver
                    ->getTranslation($record, $fallbackLocale, $fallbackTranslation);
            }
            if ($googleTranslation) {
                return $googleTranslation;
            }
        }
        return $fallbackTranslation;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $fallbackLocale
     * @return null|TranslationEntityInterface
     */
    private function getFallbackTranslation(TranslatableEntityInterface $record, $fallbackLocale)
    {
        $fallbackLocaleTranslation = $this->doctrineTranslationDriver->getTranslation($record, $fallbackLocale);
        if ($fallbackLocaleTranslation) {
            return $fallbackLocaleTranslation;
        }
        $firstTranslation = $this->doctrineTranslationDriver->getFirstTranslation($record);
        if ($firstTranslation) {
            return $firstTranslation;
        }
        return null;
    }
}
