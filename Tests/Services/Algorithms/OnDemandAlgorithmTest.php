<?php
namespace VKR\TranslationBundle\Tests\Services\Algorithms;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\Algorithms\OnDemandAlgorithm;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummy;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummyTranslations;

class OnDemandAlgorithmTest extends TestCase
{
    private $shouldThrowException = false;

    /** @var DummyTranslations */
    private $currentLocaleTranslation;

    /** @var DummyTranslations */
    private $firstTranslation;

    /** @var OnDemandAlgorithm */
    private $onDemandAlgorithm;

    public function setUp()
    {
        $this->firstTranslation = new DummyTranslations();
        $this->firstTranslation->setField1('first translation');

        $doctrineTranslationDriver = $this->mockDoctrineTranslationDriver();
        $googleTranslationDriver = $this->mockGoogleTranslationDriver();
        $this->onDemandAlgorithm = new OnDemandAlgorithm(
            $doctrineTranslationDriver, $googleTranslationDriver
        );
    }

    public function testWithCurrentLocaleTranslation()
    {
        $this->currentLocaleTranslation = new DummyTranslations();
        $this->currentLocaleTranslation->setField1('current locale');

        $record = new Dummy();
        /** @var DummyTranslations $translation */
        $translation = $this->onDemandAlgorithm->getTranslation($record, 'en');
        $this->assertEquals('current locale', $translation->getField1());
    }

    public function testWithGoogleTranslation()
    {
        $record = new GoogleTranslatableDummy();
        $this->firstTranslation = new GoogleTranslatableDummyTranslations();
        $this->firstTranslation->setField1('first translation');
        /** @var DummyTranslations $translation */
        $translation = $this->onDemandAlgorithm->getTranslation($record, 'en');
        $this->assertEquals('google translation', $translation->getField1());
    }

    public function testWithNonGoogleTranslatable()
    {
        $record = new Dummy();
        /** @var DummyTranslations $translation */
        $translation = $this->onDemandAlgorithm->getTranslation($record, 'en');
        $this->assertEquals('first translation', $translation->getField1());
    }

    public function testWithGoogleTranslationException()
    {
        $this->shouldThrowException = true;
        $record = new GoogleTranslatableDummy();
        $this->firstTranslation = new GoogleTranslatableDummyTranslations();
        $this->firstTranslation->setField1('first translation');
        /** @var DummyTranslations $translation */
        $translation = $this->onDemandAlgorithm->getTranslation($record, 'en');
        $this->assertEquals('first translation', $translation->getField1());
    }

    public function testWithoutTranslations()
    {
        $this->firstTranslation = null;
        $record = new Dummy();
        $record->setId(1);
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Translations do not exist or cannot be loaded for ID 1 of entity ' . Dummy::class);
        $this->onDemandAlgorithm->getTranslation($record, 'en');
    }

    private function mockDoctrineTranslationDriver()
    {
        $doctrineTranslationDriver = $this->createMock(DoctrineTranslationDriver::class);
        $doctrineTranslationDriver->method('getTranslation')
            ->willReturnCallback([$this, 'getDoctrineTranslationCallback']);
        $doctrineTranslationDriver->method('getFirstTranslation')
            ->willReturnCallback([$this, 'getFirstTranslationCallback']);
        return $doctrineTranslationDriver;
    }

    private function mockGoogleTranslationDriver()
    {
        $googleTranslationDriver = $this->createMock(GoogleTranslationDriver::class);
        $googleTranslationDriver->method('getTranslation')
            ->willReturnCallback([$this, 'getGoogleTranslationCallback']);
        return $googleTranslationDriver;
    }

    public function getDoctrineTranslationCallback()
    {
        return $this->currentLocaleTranslation;
    }

    public function getFirstTranslationCallback()
    {
        return $this->firstTranslation;
    }

    public function getGoogleTranslationCallback($record, $locale, GoogleTranslatableDummyTranslations $translation)
    {
        if ($this->shouldThrowException) {
            throw new GoogleTranslationException();
        }
        $translation->setField1('google translation');
        return $translation;
    }
}
