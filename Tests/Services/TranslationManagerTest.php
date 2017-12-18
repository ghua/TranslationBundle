<?php
namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Entity\LanguageEntityInterface;
use VKR\TranslationBundle\Entity\TranslatableEntityInterface;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Interfaces\LocaleRetrieverInterface;
use VKR\TranslationBundle\Services\Algorithms\DefaultAlgorithm;
use VKR\TranslationBundle\Services\Options;
use VKR\TranslationBundle\Services\TranslatedFieldSetter;
use VKR\TranslationBundle\Services\TranslationCreator;
use VKR\TranslationBundle\Services\TranslationManager;
use VKR\TranslationBundle\TestHelpers\Algorithms\DummyAlgorithm;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithFallback;
use Mockery as m;

class TranslationManagerTest extends TestCase
{
    const LANGUAGE_ENTITY = 'MyBundle:Languages';

    private $defaultLocale;

    /**
     * @var TranslationManager
     */
    private $translationManager;

    /**
     * @var LanguageEntityInterface
     */
    private $languageEn;

    /**
     * @var LanguageEntityInterface
     */
    private $languageRu;

    /**
     * @var LanguageEntityInterface
     */
    private $languageDe;

    /**
     * @var DummyTranslations[]
     */
    private $translationEn;

    /**
     * @var DummyTranslations[]
     */
    private $translationRu;

    /**
     * @var TranslationCreator|m\MockInterface
     */
    private $transaltionCreator;

    public function setUp()
    {
        $this->defaultLocale = 'en';
        $this->languageEn = new DummyLanguageEntity();
        $this->languageEn->setCode('en');
        $this->languageRu = new DummyLanguageEntity();
        $this->languageRu->setCode('ru');
        $this->languageDe = new DummyLanguageEntity();
        $this->languageDe->setCode('de');

        $this->setTranslationCreator();

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

        $localeRetriever = $this->mockLocaleRetriever();
        $translatedFieldSetter = $this->mockTranslatedFieldSetter();
        $defaultAlgorithm = $this->mockDefaultAlgorithm();
        $this->translationManager = new TranslationManager(
            $localeRetriever, $translatedFieldSetter, $defaultAlgorithm
        );
        $reflectionClass = new \ReflectionClass(get_class($this->translationManager));
        $property = $reflectionClass->getProperty('translationCreator');
        $property->setAccessible(true);
        $property->setValue($this->translationManager, $this->transaltionCreator);
    }

    public function testWithSingleResult()
    {
        $currentLocale = 'en';

        $result = new Dummy();
        $result->addTranslation($this->translationEn[0]);
        $result->addTranslation($this->translationRu[0]);

        /** @var Dummy $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale);
        $this->assertEquals('value1', $translatedResult->getField1());
        $this->assertEquals('value2', $translatedResult->getField2());
    }

    public function testWithDefaultLocale()
    {
        $result = new Dummy();
        $result->addTranslation($this->translationEn[0]);
        $result->addTranslation($this->translationRu[0]);

        /** @var Dummy $translatedResult */
        $translatedResult = $this->translationManager->translate($result);
        $this->assertEquals('znachenie1', $translatedResult->getField1());
        $this->assertEquals('znachenie2', $translatedResult->getField2());
    }

    public function testWithTranslationFallback()
    {
        $currentLocale = 'de';
        $result = new DummyWithFallback();
        $result->setSlug('value1');

        /** @var DummyWithFallback $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale);
        $this->assertEquals('value1', $translatedResult->getName());
    }

    public function testWithOrdering()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $this->translationRu[0]->setField2('foo');
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $this->translationRu[1]->setField2('boo');
        $result[1]->addTranslation($this->translationRu[1]);

        /** @var Dummy[] $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale, 'field2');
        $this->assertEquals('boo', $translatedResult[0]->getField2());
        $this->assertEquals('foo', $translatedResult[1]->getField2());
    }

    public function testWithArrayResult()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $result[1]->addTranslation($this->translationRu[1]);

        /** @var Dummy[] $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale);
        $this->assertEquals('znachenie2', $translatedResult[0]->getField2());
        $this->assertEquals('znachenie3', $translatedResult[1]->getField1());
    }

    public function testWithBadResult()
    {
        $currentLocale = 'en';
        $result = 'foo';
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Argument of translate() must be either ' . TranslatableEntityInterface::class . ' object or array of such objects');
        /** @noinspection PhpParamsInspection */
        $this->translationManager->translate($result, $currentLocale);
    }

    public function testWithBadArrayResult()
    {
        $currentLocale = 'en';
        $result = ['foo'];
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Argument of translate() must be either ' . TranslatableEntityInterface::class . ' object or array of such objects');
        $this->translationManager->translate($result, $currentLocale);
    }

    public function testWithOrderingByNonexistentColumn()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $this->translationRu[0]->setField2('foo');
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $this->translationRu[1]->setField2('boo');
        $result[1]->addTranslation($this->translationRu[1]);

        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Objects of type ' . Dummy::class . ' must have getField999 method');
        $this->translationManager->translate($result, $currentLocale, 'field999');
    }

    public function testWithCustomAlgorithm()
    {
        $algorithm = new DummyAlgorithm();
        $this->translationManager->setAlgorithm($algorithm);
        $currentLocale = 'en';

        $result = new Dummy();
        $result->addTranslation($this->translationEn[0]);
        $result->addTranslation($this->translationRu[0]);

        /** @var Dummy $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale);
        $this->assertEquals('foo', $translatedResult->getField1());
    }

    public function testWithOrderingAndOptions()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $this->translationRu[0]->setField2('foo');
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $this->translationRu[1]->setField2('boo');
        $result[1]->addTranslation($this->translationRu[1]);

        /** @var Dummy[] $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale, 'field2', new Options());
        $this->assertEquals('boo', $translatedResult[0]->getField2());
        $this->assertEquals('foo', $translatedResult[1]->getField2());
    }

    public function testWithEmptyOrderingAndOptions()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $this->translationRu[0]->setField2('foo');
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $this->translationRu[1]->setField2('boo');
        $result[1]->addTranslation($this->translationRu[1]);

        /** @var Dummy[] $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale, '', (new Options())->setForcedSave(true)->setFieldsToTranslate(['foo']));
        $this->assertEquals('foo', $translatedResult[0]->getField2());
        $this->assertEquals('boo', $translatedResult[1]->getField2());
    }

    public function testWithEmptyOrderingAndOptionsWithForcedSaveByGoogle()
    {
        $currentLocale = 'ru';

        $result = [];
        $result[0] = new Dummy();
        $result[0]->addTranslation($this->translationEn[0]);
        $this->translationRu[0]->setField2('foo');
        $result[0]->addTranslation($this->translationRu[0]);
        $result[1] = new Dummy();
        $result[1]->addTranslation($this->translationEn[1]);
        $this->translationRu[1]->setField2('boo');
        $result[1]->addTranslation($this->translationRu[1]);

        /** @var Dummy[] $translatedResult */
        $translatedResult = $this->translationManager->translate($result, $currentLocale, '', (new Options())->setForcedSaveByGoogle(true)->setFieldsToTranslate(['foo']));
        $this->assertEquals('foo', $translatedResult[0]->getField2());
        $this->assertEquals('boo', $translatedResult[1]->getField2());
    }

    private function mockLocaleRetriever()
    {
        $localeRetriever = $this->createMock(LocaleRetrieverInterface::class);
        $localeRetriever->method('getDefaultLocale')
            ->willReturnCallback([$this, 'getDefaultLocaleCallback']);
        $localeRetriever->method('getCurrentLocale')
            ->willReturnCallback([$this, 'getCurrentLocaleCallback']);
        return $localeRetriever;
    }

    private function mockTranslatedFieldSetter()
    {
        $translatedFieldSetter = $this->createMock(TranslatedFieldSetter::class);
        $translatedFieldSetter->method('setTranslatedFields')
            ->willReturnCallback([$this, 'setTranslatedFieldsCallback']);
        return $translatedFieldSetter;
    }

    private function mockDefaultAlgorithm()
    {
        $defaultAlgorithm = $this->createMock(DefaultAlgorithm::class);
        $defaultAlgorithm->method('getTranslation')->willReturnCallback([$this, 'getTranslationCallback']);
        return $defaultAlgorithm;
    }

    public function getCurrentLocaleCallback()
    {
        return 'ru';
    }

    public function getDefaultLocaleCallback()
    {
        return $this->defaultLocale;
    }

    public function getTranslationCallback(
        TranslatableEntityInterface $record,
        $locale,
        $fallbackLocale
    ) {
        foreach ($record->getTranslations() as $translation) {
            if ($translation->getLanguage()->getCode() == $locale) {
                return $translation;
            }
        }
        return null;
    }

    public function setTranslatedFieldsCallback(
        TranslatableEntityInterface $record,
        TranslationEntityInterface $translation = null
    ) {
        if ($record instanceof Dummy && $translation instanceof DummyTranslations) {
            $record->setField1($translation->getField1());
            $record->setField2($translation->getField2());
        }
        if ($record instanceof DummyWithFallback) {
            $record->setName($record->getSlug());
        }
        return $record;
    }

    /**
     * @return void
     */
    public function setTranslationCreator()
    {
        $this->transaltionCreator = m::mock(TranslationCreator::class);
        $this->transaltionCreator
            ->shouldReceive('createTranslations')
            ->atLeast()
            ->andReturn();
    }
}
