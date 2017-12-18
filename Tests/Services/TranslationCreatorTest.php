<?php
/**
 * Created by anonymous
 * Date: 16/12/17
 * Time: 11:22
 */

namespace VKR\TranslationBundle\Tests\Services;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManager;
use VKR\TranslationBundle\Entity\LanguageEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use Mockery as m;
use VKR\TranslationBundle\Services\TranslationCreator;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

/**
 * Class TranslationCreatorTest
 */
class TranslationCreatorTest extends TestCase
{
    /**
     * @var EntityManager|m\MockInterface
     */
    private $entityManager;

    /**
     * @var TranslationClassChecker|m\MockInterface
     */
    private $translationClassChecker;

    /**
     * @var DummyTranslations[]
     */
    private $persistedTranslations;

    /**
     * @var TranslationCreator
     */
    private $translationCreator;

    /**
     * @return void
     */
    public function setUp()
    {
        $this
            ->setEntityManager()
            ->setTranslationClassChecker();

        $this->translationCreator = new TranslationCreator(
            $this->getEntityManager(),
            $this->getTranslationClassChecker()
        );
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testCreateTranslationsWithEntityAndSingleRowValuesReturnVoid()
    {
        $languageEn = new DummyLanguageEntity();
        $languageEn->setCode('en');
        $translationEn = new DummyTranslations();
        $translationEn
            ->setLanguage($languageEn)
            ->setField1('value1')
            ->setField2('value2');
        $existingEntity = new Dummy();
        $existingEntity
            ->addTranslation($translationEn);

        $this->translationCreator->createTranslations($existingEntity, 'en', ['field1']);
        $this->assertEquals(1, sizeof($this->persistedTranslations));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testCreateTranslationsWithEntityAndTwoRowValuesReturnVoid()
    {
        $languageEn = new DummyLanguageEntity();
        $languageEn->setCode('en');
        $translationEn = new DummyTranslations();
        $translationEn
            ->setLanguage($languageEn)
            ->setField1('value1')
            ->setField2('value2');
        $existingEntity = new Dummy();
        $existingEntity
            ->addTranslation($translationEn);

        $this->translationCreator->createTranslations($existingEntity, 'en', ['field1', 'field2']);
        $this->assertEquals(2, sizeof($this->persistedTranslations));
    }

    /**
     * @return void
     *
     * @throws \Exception
     */
    public function testCreateTranslationsWithEntityAndTwoRowValuesThrowsException()
    {
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage("Locale foo not found in database");
        $languageEn = new DummyLanguageEntity();
        $languageEn->setCode('en');
        $translationEn = new DummyTranslations();
        $translationEn
            ->setLanguage($languageEn)
            ->setField1('value1')
            ->setField2('value2');
        $existingEntity = new Dummy();
        $existingEntity
            ->addTranslation($translationEn);

        $this->translationCreator->createTranslations($existingEntity, 'en', ['field1', 'field2']);
        $this->translationCreator->createTranslations($existingEntity, 'foo', ['field1', 'field2']);
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return TranslationCreatorTest
     */
    private function setEntityManager()
    {
        $this->entityManager = m::mock(EntityManager::class);
        $language =  m::mock(LanguageEntityInterface::class);
        $repo = m::mock(ServiceEntityRepositoryInterface::class);
        $repo
            ->shouldReceive('findOneBy')
            ->between(0, 1)
            ->andReturn($language);
        $repo
            ->shouldReceive('findOneBy')
            ->between(1, 2)
            ->andReturn(null);
        $this->entityManager
            ->shouldReceive('getRepository')
            ->atLeast()
            ->times(1)
            ->andReturn($repo);
        $this->entityManager
            ->shouldReceive('flush')
            ->atLeast()
            ->andReturnSelf();
        $this->entityManager
            ->shouldReceive('persist')
            ->atLeast()
            ->andReturnUsing(function ($entity) {
                $this->persistedTranslations[] = $entity;
            });

        return $this;
    }

    /**
     * @return TranslationClassChecker
     */
    private function getTranslationClassChecker()
    {
        return $this->translationClassChecker;
    }

    /**
     * @return TranslationCreatorTest
     */
    private function setTranslationClassChecker()
    {
        $this->translationClassChecker = m::mock(TranslationClassChecker::class);
        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->atLeast()
            ->andReturn(DummyTranslations::class);

        return $this;
    }
}
