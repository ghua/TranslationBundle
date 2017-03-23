<?php
namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslatedFieldSetter;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallbackTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallback;

class TranslatedFieldSetterTest extends TestCase
{
    /**
     * @var TranslatedFieldSetter
     */
    private $translatedFieldSetter;

    public function setUp()
    {
        $this->translatedFieldSetter = new TranslatedFieldSetter();
    }

    public function testSetTranslatedFields()
    {
        $entity = new DummyWithFallback();
        $entity->translatableFields = ['name'];
        $translation = new DummyWithFallbackTranslations();
        $translation->setName('translated name');
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
        $this->assertEquals('translated name', $entity->getName());
    }

    public function testWithoutTranslatableFields()
    {
        $entity = new DummyWithFallback();
        $translation = new DummyWithFallbackTranslations();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('getTranslatableFields() must return a non-empty array');
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }

    public function testWithoutSetter()
    {
        $entity = new DummyWithFallback();
        $entity->translatableFields = ['foo'];
        $translation = new DummyWithFallbackTranslations();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Method setFoo must exist in class ' . DummyWithFallback::class);
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }

    public function testWithoutGetter()
    {
        $entity = new DummyWithFallback();
        $entity->translatableFields = ['bar'];
        $translation = new DummyWithFallbackTranslations();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Method getBar must exist in class ' . DummyWithFallbackTranslations::class);
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }
}
