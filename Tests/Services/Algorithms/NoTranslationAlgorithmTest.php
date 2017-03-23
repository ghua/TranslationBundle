<?php
namespace VKR\TranslationBundle\Tests\Services\Algorithms;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\Algorithms\NoTranslationAlgorithm;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

class NoTranslationAlgorithmTest extends TestCase
{
    /** @var Dummy */
    private $record;

    /** @var DummyTranslations */
    private $firstTranslation;

    /** @var NoTranslationAlgorithm */
    private $noTranslationAlgorithm;

    public function setUp()
    {
        $this->record = new Dummy();
        $this->record->setId(1);
        $this->firstTranslation = new DummyTranslations();
        $this->firstTranslation->setField1('foo');

        $doctrineTranslationDriver = $this->mockDoctrineTranslationDriver();
        $this->noTranslationAlgorithm = new NoTranslationAlgorithm($doctrineTranslationDriver);
    }

    public function testGetTranslation()
    {
        /** @var DummyTranslations $translation */
        $translation = $this->noTranslationAlgorithm->getTranslation($this->record, 'en');
        $this->assertEquals('foo', $translation->getField1());
    }

    public function testWithoutTranslations()
    {
        $this->firstTranslation = null;
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage("Translations do not exist or cannot be loaded for ID 1 of entity " . Dummy::class);
        $this->noTranslationAlgorithm->getTranslation($this->record, 'en');
    }

    private function mockDoctrineTranslationDriver()
    {
        $doctrineTranslationDriver = $this->createMock(DoctrineTranslationDriver::class);
        $doctrineTranslationDriver->method('getFirstTranslation')
            ->willReturnCallback([$this, 'getFirstTranslationCallback']);
        return $doctrineTranslationDriver;
    }

    public function getFirstTranslationCallback()
    {
        return $this->firstTranslation;
    }
}
