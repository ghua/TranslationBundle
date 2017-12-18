<?php
namespace VKR\TranslationBundle\Services;

use VKR\TranslationBundle\Entity\GoogleTranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;
use VKR\TranslationBundle\Interfaces\TranslationAlgorithmInterface;
use VKR\TranslationBundle\Services\Algorithms\DefaultAlgorithm;

class TranslationManager
{
    /** @var LocaleRetrieverInterface */
    private $localeRetriever;

    /** @var TranslatedFieldSetter */
    private $translatedFieldSetter;

    /** @var TranslationAlgorithmInterface */
    private $algorithm;

    /**
     * @var TranslationCreator
     */
    private $translationCreator;

    public function __construct(
        LocaleRetrieverInterface $localeRetriever,
        TranslatedFieldSetter $translatedFieldSetter,
        DefaultAlgorithm $defaultAlgorithm
    ) {
        $this->localeRetriever = $localeRetriever;
        $this->translatedFieldSetter = $translatedFieldSetter;
        $this->algorithm = $defaultAlgorithm;
    }

    /**
     * @return TranslationCreator
     */
    public function getTranslationCreator()
    {
        return $this->translationCreator;
    }

    /**
     * @param TranslationCreator $translationCreator
     */
    public function setTranslationCreator(TranslationCreator $translationCreator)
    {
        $this->translationCreator = $translationCreator;
    }

    /**
     * @param TranslationAlgorithmInterface $algorithm
     */
    public function setAlgorithm(TranslationAlgorithmInterface $algorithm)
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @param TranslatableEntityInterface|TranslatableEntityInterface[] $result
     * @param string $locale
     * @param string $orderBy
     * @param Options|null $options
     * @return TranslatableEntityInterface|TranslatableEntityInterface[]
     * @throws TranslationException
     */
    public function translate($result, $locale = '', $orderBy = '', Options $options = null)
    {
        $exception = 'Argument of translate() must be either ' . TranslatableEntityInterface::class . ' object or array of such objects';
        if (!$locale) {
            $locale = $this->localeRetriever->getCurrentLocale();
        }
        $fallbackLocale = $this->localeRetriever->getDefaultLocale();
        if ($result instanceof TranslatableEntityInterface) {
            return $this->translateSingleRecord($result, $locale, $fallbackLocale, $options);
        }
        if (!is_array($result)) {
            throw new TranslationException($exception);
        }
        $translatedResult = [];
        foreach ($result as $key => $row) {
            if (!$row instanceof TranslatableEntityInterface) {
                throw new TranslationException($exception);
            }
            $translatedResult[$key] = $this->translateSingleRecord($row, $locale, $fallbackLocale, $options);
        }
        if ($orderBy) {
            $methodName = 'get' . ucfirst($orderBy);
            usort($translatedResult, function ($a, $b) use ($methodName) {
                if (!method_exists($a, $methodName) || !method_exists($b, $methodName)) {
                    throw new TranslationException('Objects of type ' . get_class($a) . ' must have ' . $methodName . ' method');
                }
                return strcasecmp($a->$methodName(), $b->$methodName());
            });
        }
        return $translatedResult;
    }

    /**
     * @param TranslatableEntityInterface $result
     * @param string $locale
     * @param string|null $fallbackLocale
     * @return TranslationEntityInterface
     */
    public function getTranslation(TranslatableEntityInterface $result, $locale = '', $fallbackLocale = '')
    {
        // for backward capability
        if (!$locale) {
            $locale = $this->localeRetriever->getCurrentLocale();
        }
        if (!$fallbackLocale) {
            $fallbackLocale = $this->localeRetriever->getDefaultLocale();
        }

        return $this->algorithm->getTranslation($result, $locale, $fallbackLocale);
    }

    /**
     * @param TranslatableEntityInterface $result
     * @param string $locale
     * @param string|null $fallbackLocale
     * @param Options|null $options
     * @return TranslatableEntityInterface
     * @throws \Exception
     */
    private function translateSingleRecord(TranslatableEntityInterface $result, $locale, $fallbackLocale, Options $options = null)
    {
        $translation = $this->getTranslation($result, $locale, $fallbackLocale);
        $result = $this->translatedFieldSetter->setTranslatedFields($result, $translation);

        if ($options && $options->isForcedSave()) {
            $this->translationCreator->createTranslations($result, $locale, $options->getFieldsToTranslate());
        }

        if ($options && $options->isForcedSaveByGoogle()
        && $translation instanceof GoogleTranslatableEntityInterface
        && $translation->isTranslatedByGoogle()) {
            $this->translationCreator->createTranslations($result, $locale, $options->getFieldsToTranslate());
        }

        return $result;
    }

}
