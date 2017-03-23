<?php
namespace VKR\TranslationBundle\Services\Algorithms;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\Services\EntityTranslationDriver;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;

class DefaultAlgorithm implements TranslationAlgorithmInterface
{
    /** @var DoctrineTranslationDriver */
    private $doctrineTranslationDriver;

    /** @var GoogleTranslationDriver */
    private $googleTranslationDriver;

    /** @var EntityTranslationDriver */
    private $entityTranslationDriver;

    public function __construct(
        DoctrineTranslationDriver $doctrineTranslationDriver,
        GoogleTranslationDriver $googleTranslationDriver,
        EntityTranslationDriver $entityTranslationDriver
    ) {
        $this->doctrineTranslationDriver = $doctrineTranslationDriver;
        $this->googleTranslationDriver = $googleTranslationDriver;
        $this->entityTranslationDriver = $entityTranslationDriver;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @param string|null $fallbackLocale
     * @return TranslationEntityInterface
     * @throws TranslationException
     */
    public function getTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale = null)
    {
        if ($fallbackLocale === null) {
            throw new TranslationException('Fallback locale must be set in this algorithm');
        }
        $activeTranslation = $this->getActiveTranslation($record, $locale, $fallbackLocale);
        if (!$activeTranslation) {
            $activeTranslation = $this->entityTranslationDriver->getTranslation($record);
            if (!$activeTranslation) {
                throw new TranslationException(
                    'Translations do not exist or cannot be loaded for ID ' . $record->getId() . ' of entity ' . get_class($record)
                );
            }
        }
        return $activeTranslation;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @param string $fallbackLocale
     * @return null|TranslationEntityInterface
     */
    private function getActiveTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale)
    {
        $currentLocaleTranslation = $this->doctrineTranslationDriver->getTranslation($record, $locale);
        if ($currentLocaleTranslation) {
            return $currentLocaleTranslation;
        }
        $fallbackTranslation = $this->getFallbackTranslation($record, $fallbackLocale);
        if (!$fallbackTranslation) {
            return null;
        }
        if (!method_exists($record, 'isGoogleTranslatable') || !$record->isGoogleTranslatable()) {
            return $fallbackTranslation;
        }
        try {
            $googleTranslation = $this->googleTranslationDriver
                ->getTranslation($record, $locale, $fallbackTranslation);
            if (!$googleTranslation) {
                $googleTranslation = $this->googleTranslationDriver
                    ->getTranslation($record, $fallbackLocale, $fallbackTranslation);
            }
        } catch (GoogleTranslationException $e) {
            return $fallbackTranslation;
        }
        return $googleTranslation;
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
