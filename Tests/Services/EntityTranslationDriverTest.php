<?php

namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\EntityTranslationDriver;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallback;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallbackTranslations;

class EntityTranslationDriverTest extends TestCase
{
    /** @var EntityTranslationDriver */
    private $entityTranslationDriver;

    public function setUp()
    {
        $translationClassChecker = $this->mockTranslationClassChecker();
        $this->entityTranslationDriver = new EntityTranslationDriver($translationClassChecker);
    }

    public function testTranslate()
    {
        $record = new DummyWithFallback();
        $record->translatableFields = ['name'];
        $record->setSlug('slug1');
        /** @var DummyWithFallbackTranslations $translation */
        $translation = $this->entityTranslationDriver->getTranslation($record);
        $this->assertInstanceOf(DummyWithFallbackTranslations::class, $translation);
        $this->assertEquals('slug1', $translation->getName());
    }

    public function testWithoutFallback()
    {
        $record = new Dummy();
        $translation = $this->entityTranslationDriver->getTranslation($record);
        $this->assertNull($translation);
    }

    public function testWithoutSetter()
    {
        $record = new DummyWithFallback();
        $record->translatableFields = ['foo'];
        $record->setSlug('slug1');
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage("Method setFoo not found in class " . DummyWithFallbackTranslations::class);
        $this->entityTranslationDriver->getTranslation($record);
    }

    private function mockTranslationClassChecker()
    {
        $translationClassChecker = $this->createMock(TranslationClassChecker::class);
        $translationClassChecker->method('checkTranslationClass')
            ->willReturnCallback([$this, 'checkTranslationClassCallback']);
        return $translationClassChecker;
    }

    public function checkTranslationClassCallback($entity)
    {
        $className = get_class($entity);
        return $className . 'Translations';
    }
}
