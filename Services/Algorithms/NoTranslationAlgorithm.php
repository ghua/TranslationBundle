<?php
namespace VKR\TranslationBundle\Services\Algorithms;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;

class NoTranslationAlgorithm implements TranslationAlgorithmInterface
{
    /**
     * @var DoctrineTranslationDriver
     */
    private $doctrineTranslationDriver;

    public function __construct(DoctrineTranslationDriver $doctrineTranslationDriver)
    {
        $this->doctrineTranslationDriver = $doctrineTranslationDriver;
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
        $firstTranslation = $this->doctrineTranslationDriver->getFirstTranslation($record);
        if ($firstTranslation) {
            return $firstTranslation;
        }
        throw new TranslationException(
            "Translations do not exist or cannot be loaded for ID {$record->getId()} of entity " . get_class($record)
        );
    }
}
