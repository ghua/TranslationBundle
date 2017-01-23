<?php
namespace VKR\TranslationBundle\Tests\Services;

use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslatedFieldSetter;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallbackTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallback;

class TranslatedFieldSetterTest extends \PHPUnit_Framework_TestCase
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

    public function testSetTranslatedFieldsWithFallback()
    {
        $entity = new DummyWithFallback();
        $entity->setSlug('my_slug');
        $entity->translatableFields = ['name'];
        $this->translatedFieldSetter->setTranslatedFieldsWithFallback($entity);
        $this->assertEquals('my_slug', $entity->getName());
    }

    public function testWithoutTranslatableFields()
    {
        $entity = new DummyWithFallback();
        $translation = new DummyWithFallbackTranslations();
        $this->setExpectedException(TranslationException::class, 'getTranslatableFields() must return a non-empty array');
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }

    public function testWithoutSetter()
    {
        $entity = new DummyWithFallback();
        $entity->translatableFields = ['foo'];
        $translation = new DummyWithFallbackTranslations();
        $this->setExpectedException(TranslationException::class, 'Method setFoo must exist in class ' . DummyWithFallback::class);
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }

    public function testWithoutGetter()
    {
        $entity = new DummyWithFallback();
        $entity->translatableFields = ['bar'];
        $translation = new DummyWithFallbackTranslations();
        $this->setExpectedException(TranslationException::class, 'Method getBar must exist in class ' . DummyWithFallbackTranslations::class);
        $this->translatedFieldSetter->setTranslatedFields($entity, $translation);
    }

    public function testWithFallbackButWithoutSetter()
    {
        $entity = new DummyWithFallback();
        $entity->setSlug('my_slug');
        $entity->translatableFields = ['foo'];
        $this->setExpectedException(TranslationException::class, 'Method setFoo must exist in class ' . DummyWithFallback::class);
        $this->translatedFieldSetter->setTranslatedFieldsWithFallback($entity);
    }
}
