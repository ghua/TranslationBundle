<?php
namespace VKR\TranslationBundle\Services;

use Google\Cloud\Translate\TranslateClient;
use VKR\TranslationBundle\Decorators\GoogleClientDecorator;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;

class GoogleTranslationDriver
{
    /**
     * @var TranslateClient
     */
    private $googleClient;

    /**
     * @var TranslationClassChecker
     */
    private $translationClassChecker;

    public function __construct(
        TranslationClassChecker $translationClassChecker,
        GoogleClientDecorator $decorator,
        $googleApiKey = ''
    ) {
        $this->translationClassChecker = $translationClassChecker;

        if ($googleApiKey) {
            $this->googleClient = $decorator->createClient($googleApiKey);
        }
    }

    /**
     * @param TranslatableEntityInterface $record
     * @param string $locale
     * @param TranslationEntityInterface $translation
     * @return TranslationEntityInterface
     * @throws TranslationException
     * @throws GoogleTranslationException
     */
    public function getTranslation(
        TranslatableEntityInterface $record,
        $locale,
        TranslationEntityInterface $translation
    ) {
        if (null === $this->googleClient) {
            throw new GoogleTranslationException('Google API key must be defined (vkr_translation.google_api_key)');
        }

        $translatableFields = $record->getTranslatableFields();
        $source = $translation->getLanguage()->getCode();
        $source = $this->getShortLocaleCode($source);
        $locale = $this->getShortLocaleCode($locale);
        $options = [
            'source' => $source,
            'target' => $locale,
        ];
        $translationClass = $this->translationClassChecker->checkTranslationClass($record);
        $newTranslation = new $translationClass();
        foreach ($translatableFields as $field) {
            $getterName = 'get' . ucfirst($field);
            if (!method_exists($translation, $getterName)) {
                throw new TranslationException("Method $getterName not found in class " . get_class($translation));
            }
            $value = $translation->$getterName();
            try {
                $valueJson = $this->googleClient->translate($value, $options);
            } catch (\Exception $e) {
                throw new GoogleTranslationException('Error from Google Translate API');
            }
            if (isset($valueJson['text'])) {
                $value = $valueJson['text'];
            }
            $setterName = 'set' . ucfirst($field);
            if (!method_exists($newTranslation, $setterName)) {
                throw new TranslationException("Method $setterName not found in class $translationClass");
            }
            $newTranslation->$setterName($value);
        }
        return $newTranslation;
    }

    private function getShortLocaleCode($locale)
    {
        return substr($locale, 0, 2);
    }
}
