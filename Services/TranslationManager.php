<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;

class TranslationManager
{
    /**
     * @var LocaleRetrieverInterface
     */
    private $localeRetriever;

    /**
     * @var TranslationRetriever
     */
    private $translationRetriever;

    /**
     * @var TranslatedFieldSetter
     */
    private $translatedFieldSetter;

    public function __construct(
        LocaleRetrieverInterface $localeRetriever,
        TranslationRetriever $translationRetriever,
        TranslatedFieldSetter $translatedFieldSetter
    ) {
        $this->localeRetriever = $localeRetriever;
        $this->translationRetriever = $translationRetriever;
        $this->translatedFieldSetter = $translatedFieldSetter;
    }

    /**
     * @param TranslatableEntityInterface|TranslatableEntityInterface[] $result
     * @param string $locale
     * @param string $orderBy
     * @return TranslatableEntityInterface|TranslatableEntityInterface[]
     * @throws TranslationException
     */
    public function translate($result, $locale = '', $orderBy = '')
    {
        $exception = 'Argument of translate() must be either TranslatableEntityInterface object or array of such objects';
        if (!$locale) {
            $locale = $this->localeRetriever->getCurrentLocale();
        }
        $fallbackLocale = $this->findFallbackLocale();
        if ($result instanceof TranslatableEntityInterface) {
            return $this->translateSingleRecord($result, $locale, $fallbackLocale);
        }
        if (!is_array($result)) {
            throw new TranslationException($exception);
        }
        $translatedResult = [];
        foreach ($result as $key => $row) {
            if (!$row instanceof TranslatableEntityInterface) {
                throw new TranslationException($exception);
            }
            $translatedResult[$key] = $this->translateSingleRecord($row, $locale, $fallbackLocale);
        }
        if ($orderBy) {
            $methodName = 'get' . ucfirst($orderBy);
            usort($translatedResult, function ($a, $b) use ($methodName) {
                if (!method_exists($a, $methodName) || !method_exists($b, $methodName)) {
                    throw new TranslationException('Objects of type ' . get_class($a) . 'must have ' . $methodName . ' method');
                }
                return strcasecmp($a->$methodName(), $b->$methodName());
            });
        }
        return $translatedResult;
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string|null $locale
     * @param string $fallbackLocale
     * @return TranslatableEntityInterface
     * @throws TranslationException
     */
    private function translateSingleRecord(
        TranslatableEntityInterface $record,
        $locale,
        $fallbackLocale
    ) {
        $activeTranslation = $this->translationRetriever
            ->getActiveTranslation($record, $locale, $fallbackLocale);
        if ($activeTranslation) {
            $this->translatedFieldSetter->setTranslatedFields($record, $activeTranslation);
            return $record;
        }
        if ($record->getTranslationFallback() !== false) {
            $this->translatedFieldSetter->setTranslatedFieldsWithFallback($record);
            return $record;
        }
        throw new TranslationException(
            'Translations do not exist or cannot be loaded for ID ' . $record->getId() . ' of entity ' . get_class($record)
        );
    }

    /**
     * @return string
     * @throws TranslationException
     */
    private function findFallbackLocale()
    {
        $locale = $this->localeRetriever->getDefaultLocale();
        if (!$locale) {
            throw new TranslationException('Default locale must be set before translating');
        }
        return $locale;
    }
}
