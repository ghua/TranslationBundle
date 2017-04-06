<?php
namespace VKR\TranslationBundle\Services;

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
     * @return TranslatableEntityInterface|TranslatableEntityInterface[]
     * @throws TranslationException
     */
    public function translate($result, $locale = '', $orderBy = '')
    {
        $exception = 'Argument of translate() must be either ' . TranslatableEntityInterface::class . ' object or array of such objects';
        if (!$locale) {
            $locale = $this->localeRetriever->getCurrentLocale();
        }
        $fallbackLocale = $this->localeRetriever->getDefaultLocale();
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
     * @return TranslatableEntityInterface
     */
    private function translateSingleRecord(TranslatableEntityInterface $result, $locale, $fallbackLocale)
    {
        $translation = $this->getTranslation($result, $locale, $fallbackLocale);
        $result = $this->translatedFieldSetter->setTranslatedFields($result, $translation);
        return $result;
    }

}
