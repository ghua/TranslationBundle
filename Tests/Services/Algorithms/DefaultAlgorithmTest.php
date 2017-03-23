<?php
namespace VKR\TranslationBundle\Tests\Services\Algorithms;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\GoogleTranslationException;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\Algorithms\DefaultAlgorithm;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\Services\EntityTranslationDriver;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummy;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummyTranslations;

class DefaultAlgorithmTest extends TestCase
{
    const FALLBACK_LOCALE = 'en';

    private $googleException = false;

    /**
     * @var TranslatableEntityInterface
     */
    private $record;

    /**
     * @var TranslationEntityInterface|null
     */
    private $currentLocaleTranslation = null;

    /**
     * @var TranslationEntityInterface|null
     */
    private $fallbackTranslation = null;

    /**
     * @var TranslationEntityInterface|null
     */
    private $entityTranslation = null;

    /**
     * @var TranslationEntityInterface|null
     */
    private $firstTranslation = null;

    /**
     * @var TranslationEntityInterface|null
     */
    private $googleLocaleTranslation = null;

    /**
     * @var TranslationEntityInterface|null
     */
    private $googleFallbackTranslation = null;

    /**
     * @var DefaultAlgorithm
     */
    private $defaultAlgorithm;

    public function setUp()
    {
        $this->record = new Dummy();

        $doctrineTranslationDriver = $this->mockDoctrineTranslationDriver();
        $googleTranslationDriver = $this->mockGoogleTranslationDriver();
        $entityTranslationDriver = $this->mockEntityTranslationDriver();
        $this->defaultAlgorithm = new DefaultAlgorithm(
            $doctrineTranslationDriver, $googleTranslationDriver, $entityTranslationDriver
        );
    }

    public function testWithCurrentLocaleTranslation()
    {
        $this->currentLocaleTranslation = new DummyTranslations();
        $this->currentLocaleTranslation->setField1('foo');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(DummyTranslations::class, $translation);
        $this->assertEquals('foo', $translation->getField1());
    }

    public function testWithoutFallbackLocale()
    {
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Fallback locale must be set in this algorithm');
        $this->defaultAlgorithm->getTranslation($this->record, 'de');
    }

    public function testWithoutFallbackTranslation()
    {
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Translations do not exist or cannot be loaded for ID 1 of entity ' . Dummy::class);
        $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
    }

    public function testWithFallbackTranslation()
    {
        $this->fallbackTranslation = new DummyTranslations();
        $this->fallbackTranslation->setField1('bar');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(DummyTranslations::class, $translation);
        $this->assertEquals('bar', $translation->getField1());
    }

    public function testWithFirstTranslation()
    {
        $this->firstTranslation = new DummyTranslations();
        $this->firstTranslation->setField1('baz');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(DummyTranslations::class, $translation);
        $this->assertEquals('baz', $translation->getField1());
    }

    public function testWithGoogleTranslation()
    {
        $this->record = new GoogleTranslatableDummy();
        $this->fallbackTranslation = new GoogleTranslatableDummyTranslations();
        $this->fallbackTranslation->setField1('bar');
        $this->googleLocaleTranslation = new GoogleTranslatableDummyTranslations();
        $this->googleLocaleTranslation->setField1('bar-de');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(GoogleTranslatableDummyTranslations::class, $translation);
        $this->assertEquals('bar-de', $translation->getField1());
    }

    public function testWithGoogleFallbackTranslation()
    {
        $this->record = new GoogleTranslatableDummy();
        $this->fallbackTranslation = new GoogleTranslatableDummyTranslations();
        $this->fallbackTranslation->setField1('bar');
        $this->googleFallbackTranslation = new GoogleTranslatableDummyTranslations();
        $this->googleFallbackTranslation->setField1('bar-en');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(GoogleTranslatableDummyTranslations::class, $translation);
        $this->assertEquals('bar-en', $translation->getField1());
    }

    public function testWithGoogleException()
    {
        $this->googleException = true;
        $this->record = new GoogleTranslatableDummy();
        $this->fallbackTranslation = new GoogleTranslatableDummyTranslations();
        $this->fallbackTranslation->setField1('bar');
        $this->googleLocaleTranslation = new GoogleTranslatableDummyTranslations();
        $this->googleLocaleTranslation->setField1('bar-de');
        /** @var DummyTranslations|null $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(GoogleTranslatableDummyTranslations::class, $translation);
        $this->assertEquals('bar', $translation->getField1());
    }

    public function testWithEntityTranslation()
    {
        $this->entityTranslation = new DummyTranslations();
        $this->entityTranslation->setField1('fallback');
        /** @var DummyTranslations $translation */
        $translation = $this->defaultAlgorithm->getTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertEquals('fallback', $translation->getField1());
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

    private function mockEntityTranslationDriver()
    {
        $entityTranslationDriver = $this->createMock(EntityTranslationDriver::class);
        $entityTranslationDriver->method('getTranslation')
            ->willReturnCallback([$this, 'getEntityTranslationCallback']);
        return $entityTranslationDriver;
    }

    public function getDoctrineTranslationCallback($record, $locale)
    {
        if ($locale == self::FALLBACK_LOCALE) {
            return $this->fallbackTranslation;
        }
        return $this->currentLocaleTranslation;
    }

    public function getFirstTranslationCallback()
    {
        return $this->firstTranslation;
    }

    public function getGoogleTranslationCallback($record, $locale, $fallback)
    {
        if ($this->googleException) {
            throw new GoogleTranslationException();
        }
        if ($locale == self::FALLBACK_LOCALE) {
            return $this->googleFallbackTranslation;
        }
        return $this->googleLocaleTranslation;
    }

    public function getEntityTranslationCallback()
    {
        return $this->entityTranslation;
    }
}
