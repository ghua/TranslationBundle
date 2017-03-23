<?php
namespace VKR\TranslationBundle\Services\Algorithms;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;

class OnDemandAlgorithm implements TranslationAlgorithmInterface
{
    /** @var DoctrineTranslationDriver */
    private $doctrineTranslationDriver;

    /** @var GoogleTranslationDriver */
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
     * @param string|null $fallbackLocale
     * @return TranslationEntityInterface
     * @throws TranslationException
     */
    public function getTranslation(TranslatableEntityInterface $record, $locale, $fallbackLocale = null)
    {
        $currentLocaleTranslation = $this->doctrineTranslationDriver
            ->getTranslation($record, $locale);
        if ($currentLocaleTranslation) {
            return $currentLocaleTranslation;
        }
        $firstTranslation = $this->doctrineTranslationDriver->getFirstTranslation($record);
        if (!$firstTranslation) {
            throw new TranslationException(
                'Translations do not exist or cannot be loaded for ID ' . $record->getId() . ' of entity ' . get_class($record)
            );
        }
        if (!method_exists($record, 'isGoogleTranslatable') || !$record->isGoogleTranslatable()) {
            return $firstTranslation;
        }
        try {
            $googleTranslation = $this->googleTranslationDriver
                ->getTranslation($record, $locale, $firstTranslation);
        } catch (GoogleTranslationException $e) {
            return $firstTranslation;
        }
        return $googleTranslation;
    }
}
