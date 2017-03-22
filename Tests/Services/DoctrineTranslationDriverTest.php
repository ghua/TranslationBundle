<?php
namespace VKR\TranslationBundle\Tests\Services;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

class DoctrineTranslationDriverTest extends TestCase
{
    const LANGUAGE_ENTITY_NAME = '';

    /**
     * @var DoctrineTranslationDriver
     */
    private $doctrineTranslationDriver;

    public function setUp()
    {
        $entityManager = $this->mockEntityManager();
        $this->doctrineTranslationDriver = new DoctrineTranslationDriver(
            $entityManager, self::LANGUAGE_ENTITY_NAME
        );
    }

    public function testGetTranslation()
    {
        $record = new Dummy();
        $english = new DummyLanguageEntity();
        $english->setCode('en');
        $translation1 = new DummyTranslations();
        $translation1
            ->setLanguage($english)
            ->setField1('Dog')
        ;
        $record->addTranslation($translation1);
        $german = new DummyLanguageEntity();
        $german->setCode('de');
        $translation2 = new DummyTranslations();
        $translation2
            ->setLanguage($german)
            ->setField1('Hund')
        ;
        $record->addTranslation($translation2);
        /** @var DummyTranslations|null $result */
        $result = $this->doctrineTranslationDriver->getTranslation($record, 'en');
        $this->assertNotNull($result);
        $this->assertEquals('Dog', $result->getField1());
        $this->assertEquals('en', $result->getLanguage()->getCode());
    }

    public function testWithoutTranslations()
    {
        $record = new Dummy();
        $result = $this->doctrineTranslationDriver->getTranslation($record, 'en');
        $this->assertNull($result);
    }

    public function testWithoutTranslationInNeededLanguage()
    {
        $record = new Dummy();
        $german = new DummyLanguageEntity();
        $german->setCode('de');
        $translation1 = new DummyTranslations();
        $translation1
            ->setLanguage($german)
            ->setField1('Hund')
        ;
        $record->addTranslation($translation1);
        $result = $this->doctrineTranslationDriver->getTranslation($record, 'en');
        $this->assertNull($result);
    }

    public function testWithoutLocale()
    {
        $currentLocale = 'foo';

        $record = new Dummy();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Locale foo not found in the DB');
        $this->doctrineTranslationDriver->getTranslation($record, $currentLocale);
    }

    public function testGetFirstTranslation()
    {
        $record = new Dummy();
        $german = new DummyLanguageEntity();
        $german->setCode('de');
        $translation1 = new DummyTranslations();
        $translation1
            ->setLanguage($german)
            ->setField1('Hund')
        ;
        $record->addTranslation($translation1);
        /** @var DummyTranslations $result */
        $result = $this->doctrineTranslationDriver->getFirstTranslation($record);
        $this->assertEquals('de', $result->getLanguage()->getCode());
        $this->assertEquals('Hund', $result->getField1());
    }

    public function testGetFirstTranslationWithoutTranslations()
    {
        $record = new Dummy();
        $result = $this->doctrineTranslationDriver->getFirstTranslation($record);
        $this->assertNull($result);
    }

    private function mockEntityManager()
    {
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->method('getRepository')->willReturn($this->mockEntityRepository());
        return $entityManager;
    }

    private function mockEntityRepository()
    {
        $entityRepository = $this->createMock(EntityRepository::class);
        $entityRepository->method('findOneBy')->willReturnCallback([$this, 'findOneByCallback']);
        return $entityRepository;
    }

    public function findOneByCallback(array $criteria)
    {
        if ($criteria['code'] == 'en') {
            return true;
        }
        return false;
    }
}
