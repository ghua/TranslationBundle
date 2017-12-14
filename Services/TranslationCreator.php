<?php
/**
 * Created by anonymous
 * Date: 14/12/17
 * Time: 12:26
 */

namespace VKR\TranslationBundle\Services;

use Doctrine\ORM\EntityManager;
use VKR\TranslationBundle\Entity\LanguageEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;

/**
 * Class TranslationCreator
 */
class TranslationCreator
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
     * TranslationCreator constructor.
     * @param EntityManager           $entityManager
     * @param TranslationClassChecker $translationClassChecker
     */
    public function __construct(
        EntityManager $entityManager,
        TranslationClassChecker $translationClassChecker
    ) {
        $this->entityManager = $entityManager;
        $this->translationClassChecker = $translationClassChecker;
    }

    /**
     * @param TranslatableEntityInterface $entity
     * @param string                      $locale
     * @param array                       $values
     *
     * @return void
     *
     * @throws \Exception
     */
    public function createTranslations(
        TranslatableEntityInterface $entity,
        $locale,
        array $values
    ) {
        $translationClass = $this->translationClassChecker->checkTranslationClass($entity);
        /** @var LanguageEntityInterface|null $language */
        $language = $this->entityManager->getRepository(LanguageEntityInterface::class)
            ->findOneBy(['code' => $locale]);

        if (!$language) {
            throw new TranslationException("Locale $locale not found in the DB");
        }

        /** @var TranslationEntityInterface|null $translation */
        $translation = $this->entityManager->getRepository($translationClass)
            ->findOneBy(['entity' => $entity, 'language' => $language]);

        if (!$translation) {
            $translation = new $translationClass();
            $translation
                ->setEntity($entity)
                ->setLanguage($language);

            $isUpdated = false;

            foreach ($values as $field) {
                if ($this->setValue($translation, $field)) {
                    $isUpdated = true;
                }
            }

            if ($isUpdated) {
                $this->entityManager->persist($translation);
                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param TranslationEntityInterface $translation
     *
     * @param string                     $field
     * @param string|null                $value
     *
     * @return bool
     *
     * @throws TranslationException
     */
    private function setValue(TranslationEntityInterface $translation, $field, $value = null)
    {
        $setterName = 'set' . ucfirst($field);
        if (!method_exists($translation, $setterName)) {
            throw new TranslationException("Method $setterName not found in class " . get_class($translation));
        }
        $getterName = 'get' . ucfirst($field);
        if (!method_exists($translation, $getterName)) {
            throw new TranslationException("Method $getterName not found in class " . get_class($translation));
        }
        if (!$value || $translation->$getterName() != $value) {
            $translation->$setterName($value);
            return true;
        }
        return false;
    }
}
