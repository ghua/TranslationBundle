<?php


namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use Mockery as m;
use Symfony\Component\DependencyInjection\Container;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\Services\TranslationManager;
use VKR\TranslationBundle\Services\TranslationProxyFactory;
use VKR\TranslationBundle\TestHelpers\Entity\ChildDemoLazy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLazy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;

class TranslationProxyFactoryTest extends TestCase
{

    /**
     * @var TranslationManager|m\MockInterface
     */
    private $translationManager;

    /**
     * @var TranslationClassChecker|m\MockInterface
     */
    private $translationClassChecker;

    /**
     * @var Container|m\MockInterface
     */
    private $container;

    /**
     * @var DummyLanguageEntity
     */
    private $dummyLanguageEntity;

    /**
     * @var DummyTranslations
     */
    private $dummyTranslation;

    public function setUp()
    {
        $this->translationManager = m::mock(TranslationManager::class);
        $this->translationClassChecker = m::mock(TranslationClassChecker::class);
        $this->container = m::mock(Container::class);
        $this->container->shouldReceive('get')
            ->with(m::mustBe('vkr_translation.translation_manager'))
            ->andReturn($this->translationManager);
        $this->container->shouldReceive('get')
            ->with(m::mustBe('vkr_translation.class_checker'))
            ->andReturn($this->translationClassChecker);

        $dummyLanguageEntity = new DummyLanguageEntity();
        $dummyLanguageEntity->setCode('en');
        $this->dummyLanguageEntity = $dummyLanguageEntity;

        $dummyTranslation = new DummyTranslations();
        $dummyTranslation->setLanguage($dummyLanguageEntity)
            ->setField1('value1')
            ->setField2('value2');
        $this->dummyTranslation = $dummyTranslation;
    }

    public function testTranslationExist()
    {
        $dummyEntity = new DummyLazy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn($this->dummyTranslation);

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertTrue($factory->initialize($dummyEntity));

        $this->assertEquals($this->dummyTranslation->getField1(), $dummyEntity->getTranslation()->getField1());
        $this->assertEquals($this->dummyTranslation->getField2(), $dummyEntity->getTranslation()->getField2());
        $this->assertTrue($dummyEntity->getTranslation() instanceof TranslationEntityInterface);
    }

    public function testLazyTranslationWithInheritance()
    {
        $demoLazy = new ChildDemoLazy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($demoLazy))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($demoLazy))
            ->once()
            ->andReturn($this->dummyTranslation);

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertTrue($factory->initialize($demoLazy));

        $this->assertEquals($this->dummyTranslation->getField1(), $demoLazy->getTranslation()->getField1());
        $this->assertEquals($this->dummyTranslation->getField2(), $demoLazy->getTranslation()->getField2());
        $this->assertTrue($demoLazy->getTranslation() instanceof TranslationEntityInterface);
    }

    public function testTranslationDoesNotExist()
    {
        $dummyEntity = new DummyLazy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andThrow(new TranslationException('Translations do not exist or cannot be loaded'));

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertTrue($factory->initialize($dummyEntity));

        $this->expectException(TranslationException::class);

        $dummyEntity->getTranslation()->getField1();

        $this->assertFalse($dummyEntity->getTranslation() instanceof TranslationEntityInterface);
    }

    public function testEntityWithoutLazyTranslatableTrait()
    {
        $dummyEntity = new Dummy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->never();

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertFalse($factory->initialize($dummyEntity));
    }

    public function testEntityWithoutAppropriateInterface()
    {
        $dummyEntity = new Dummy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andThrow(new TranslationException('Class $translationClass does not exist'));

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->never();

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertFalse($factory->initialize($dummyEntity));
    }

    public function testTranslationObjectReplacement()
    {
        $dummyEntity = new DummyLazy();

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn($this->dummyTranslation);

        $factory = (new TranslationProxyFactory())
            ->setContainer($this->container);

        $this->assertTrue($factory->initialize($dummyEntity));

        $translation = $dummyEntity->getTranslation();

        $this->assertTrue($translation instanceof TranslationEntityInterface);
        $this->assertEquals($this->dummyTranslation->getField1(), $translation->getField1());
        $this->assertEquals($this->dummyTranslation->getField2(), $translation->getField2());
    }

    public function tearDown()
    {
        m::close();
    }

}
