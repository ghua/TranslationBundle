<?php
namespace VKR\TranslationBundle\Tests\Services;

use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\Services\GoogleTranslationDriver;
use VKR\TranslationBundle\Services\TranslationRetriever;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummy;
use VKR\TranslationBundle\TestHelpers\Entity\GoogleTranslatableDummyTranslations;

class TranslationRetrieverTest extends \PHPUnit_Framework_TestCase
{
    const FALLBACK_LOCALE = 'en';

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
     * @var TranslationRetriever
     */
    private $translationRetriever;

    public function setUp()
    {
        $this->record = new Dummy();

        $doctrineTranslationDriver = $this->mockDoctrineTranslationDriver();
        $googleTranslationDriver = $this->mockGoogleTranslationDriver();
        $this->translationRetriever = new TranslationRetriever(
            $doctrineTranslationDriver, $googleTranslationDriver
        );
    }

    public function testWithCurrentLocaleTranslation()
    {
        $this->currentLocaleTranslation = new DummyTranslations();
        $this->currentLocaleTranslation->setField1('foo');
        /** @var DummyTranslations|null $translation */
        $translation = $this->translationRetriever->getActiveTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(DummyTranslations::class, $translation);
        $this->assertEquals('foo', $translation->getField1());
    }

    public function testWithoutFallbackTranslation()
    {
        /** @var DummyTranslations|null $translation */
        $translation = $this->translationRetriever->getActiveTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertNull($translation);
    }

    public function testWithFallbackTranslation()
    {
        $this->fallbackTranslation = new DummyTranslations();
        $this->fallbackTranslation->setField1('bar');
        /** @var DummyTranslations|null $translation */
        $translation = $this->translationRetriever->getActiveTranslation(
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
        $translation = $this->translationRetriever->getActiveTranslation(
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
        $translation = $this->translationRetriever->getActiveTranslation(
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
        $translation = $this->translationRetriever->getActiveTranslation(
            $this->record, 'de', self::FALLBACK_LOCALE
        );
        $this->assertInstanceOf(GoogleTranslatableDummyTranslations::class, $translation);
        $this->assertEquals('bar-en', $translation->getField1());
    }

    private function mockDoctrineTranslationDriver()
    {
        $doctrineTranslationDriver = $this->getMockBuilder(DoctrineTranslationDriver::class)
            ->disableOriginalConstructor()->getMock();
        $doctrineTranslationDriver->expects($this->any())
            ->method('getTranslation')
            ->willReturnCallback([$this, 'getDoctrineTranslationCallback']);
        $doctrineTranslationDriver->expects($this->any())
            ->method('getFirstTranslation')
            ->willReturnCallback([$this, 'getFirstTranslationCallback']);
        return $doctrineTranslationDriver;
    }

    private function mockGoogleTranslationDriver()
    {
        $googleTranslationDriver = $this->getMockBuilder(GoogleTranslationDriver::class)
            ->disableOriginalConstructor()->getMock();
        $googleTranslationDriver->expects($this->any())
            ->method('getTranslation')
            ->willReturnCallback([$this, 'getGoogleTranslationCallback']);
        return $googleTranslationDriver;
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
        if ($locale == self::FALLBACK_LOCALE) {
            return $this->googleFallbackTranslation;
        }
        return $this->googleLocaleTranslation;
    }
}
