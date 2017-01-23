<?php
namespace VKR\TranslationBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use VKR\TranslationBundle\Entity\LanguageEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\Services\TranslationUpdater;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

class TranslationUpdaterTest extends \PHPUnit_Framework_TestCase
{
    const LANGUAGE_ENTITY_NAME = 'MyBundle:Languages';

    /**
     * @var LanguageEntityInterface
     */
    private $languageEn;

    /**
     * @var LanguageEntityInterface
     */
    private $languageRu;

    /**
     * @var DummyTranslations[]
     */
    private $translationEn;

    /**
     * @var DummyTranslations[]
     */
    private $translationRu;

    /**
     * @var Dummy
     */
    private $existingEntity;

    /**
     * @var DummyTranslations[]
     */
    private $persistedTranslations = [];

    /**
     * @var DummyTranslations[]
     */
    private $flushedTranslations = [];

    /**
     * @var TranslationUpdater
     */
    private $translationUpdater;

    public function setUp()
    {
        $this->languageEn = new DummyLanguageEntity();
        $this->languageEn->setCode('en');
        $this->languageRu = new DummyLanguageEntity();
        $this->languageRu->setCode('ru');

        $this->translationEn[0] = new DummyTranslations();
        $this->translationEn[0]
            ->setLanguage($this->languageEn)
            ->setField1('value1')
            ->setField2('value2')
        ;

        $this->translationRu[0] = new DummyTranslations();
        $this->translationRu[0]
            ->setLanguage($this->languageRu)
            ->setField1('znachenie1')
            ->setField2('znachenie2')
        ;

        $this->translationEn[1] = new DummyTranslations();
        $this->translationEn[1]
            ->setLanguage($this->languageEn)
            ->setField1('value3')
            ->setField2('value4')
        ;

        $this->translationRu[1] = new DummyTranslations();
        $this->translationRu[1]
            ->setLanguage($this->languageRu)
            ->setField1('znachenie3')
            ->setField2('znachenie4')
        ;

        $entityManager = $this->mockEntityManager();
        $translationClassChecker = $this->mockTranslationClassChecker();
        $this->translationUpdater = new TranslationUpdater(
            $entityManager, $translationClassChecker, self::LANGUAGE_ENTITY_NAME
        );
    }

    public function testUpdateTranslation()
    {
        $this->existingEntity = new Dummy();
        $this->existingEntity
            ->addTranslation($this->translationEn[0])
            ->addTranslation($this->translationRu[0])
        ;
        $data = [
            'field1' => 'znachenie1',
            'field2' => 'znachenie25',
        ];
        $this->translationUpdater->updateTranslations($this->existingEntity, 'ru', $data);
        $this->assertEquals(1, sizeof($this->flushedTranslations));
        $this->assertEquals('znachenie1', $this->flushedTranslations[0]->getField1());
        $this->assertEquals('znachenie25', $this->flushedTranslations[0]->getField2());
    }

    public function testCreateTranslation()
    {
        $this->existingEntity = new Dummy();
        $this->existingEntity
            ->addTranslation($this->translationEn[0])
        ;
        $data = [
            'field1' => 'znachenie1',
            'field2' => 'znachenie25',
        ];
        $this->translationUpdater->updateTranslations($this->existingEntity, 'ru', $data);
        $this->assertEquals(1, sizeof($this->flushedTranslations));
        $this->assertEquals('znachenie1', $this->flushedTranslations[0]->getField1());
        $this->assertEquals('znachenie25', $this->flushedTranslations[0]->getField2());
    }

    public function testUpdateWithNoNewValues()
    {
        $this->existingEntity = new Dummy();
        $this->existingEntity
            ->addTranslation($this->translationEn[0])
            ->addTranslation($this->translationRu[0])
        ;
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];
        $this->translationUpdater->updateTranslations($this->existingEntity, 'en', $data);
        $this->assertEquals(0, sizeof($this->flushedTranslations));
    }

    public function testUpdateWithoutLocale()
    {
        $entity = new Dummy();
        $this->setExpectedException(TranslationException::class, 'Locale foo not found in the DB');
        $this->translationUpdater->updateTranslations($entity, 'foo', []);
    }

    public function testUpdateWithoutSetter()
    {
        $entity = new Dummy();
        $this->setExpectedException(TranslationException::class, 'Method setFoo not found in class ' . DummyTranslations::class);
        $this->translationUpdater->updateTranslations($entity, 'en', ['foo' => 'bar']);
    }

    public function testUpdateWithoutGetter()
    {
        $entity = new Dummy();
        $this->setExpectedException(TranslationException::class, 'Method getField3 not found in class ' . DummyTranslations::class);
        $this->translationUpdater->updateTranslations($entity, 'en', ['field3' => 'bar']);
    }

    private function mockEntityManager()
    {
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()->getMock();
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($this->mockEntityRepository());
        $entityManager->expects($this->any())
            ->method('persist')
            ->willReturnCallback([$this, 'persistCallback']);
        $entityManager->expects($this->any())
            ->method('flush')
            ->willReturnCallback([$this, 'flushCallback']);
        return $entityManager;
    }

    private function mockEntityRepository()
    {
        $entityRepository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()->getMock();
        $entityRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturnCallback([$this, 'findOneByCallback']);
        return $entityRepository;
    }

    private function mockTranslationClassChecker()
    {
        $translationClassChecker = $this->getMockBuilder(TranslationClassChecker::class)
            ->disableOriginalConstructor()->getMock();
        $translationClassChecker->expects($this->any())
            ->method('checkTranslationClass')
            ->willReturn(DummyTranslations::class);
        return $translationClassChecker;
    }

    public function findOneByCallback(array $criteria)
    {
        if (isset($criteria['code'])) {
            switch ($criteria['code']) {
                case 'en':
                    return $this->languageEn;
                case 'ru':
                    return $this->languageRu;
            }
        }
        if (isset($criteria['entity'])) {
            /** @var Dummy $entity */
            $entity = $criteria['entity'];
            /** @var DummyLanguageEntity $language */
            $language = $criteria['language'];
            if ($this->existingEntity && $entity->getId() == $this->existingEntity->getId()) {
                foreach ($this->existingEntity->getTranslations() as $translation) {
                    if ($translation->getLanguage()->getCode() == $language->getCode()) {
                        return $translation;
                    }
                }
            }
        }
        return null;
    }

    public function persistCallback($entity)
    {
        $this->persistedTranslations[] = $entity;
    }

    public function flushCallback()
    {
        $this->flushedTranslations = $this->persistedTranslations;
        $this->persistedTranslations = [];
    }
}
