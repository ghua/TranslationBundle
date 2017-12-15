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

            $entityReflected = new \ReflectionClass(get_class($entity));

            foreach ($values as $field) {
                $getterName = 'get'.ucfirst($field);
                if ($entityReflected->hasMethod($getterName)) {
                    $setterName = 'set'.ucfirst($field);
                    if (!method_exists($entity, $getterName)) {
                        throw new TranslationException(sprintf("Method %s not found in class %s", $getterName, get_class($entity)));
                    }
                    if (!method_exists($translation, $setterName)) {
                        throw new TranslationException(sprintf("Method %s not found in class %s", $setterName, get_class($translation)));
                    }

                    $translation->$setterName($entity->$getterName());

                    $this->entityManager->persist($translation);
                }
            }

            $this->entityManager->flush();
        }
    }
}
