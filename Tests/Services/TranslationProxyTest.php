<?php


namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use Mockery as m;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\Services\TranslationManager;
use VKR\TranslationBundle\Services\TranslationProxyFactory;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
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

    public function setUp()
    {
        $this->translationManager = m::mock(TranslationManager::class);
        $this->translationClassChecker = m::mock(TranslationClassChecker::class);
    }

    public function testTranslationExist()
    {
        $dummyLanguageEntity = new DummyLanguageEntity();
        $dummyLanguageEntity->setCode('en');

        $dummyEntity = new DummyLazy();
        $dummyTranslation = new DummyTranslations();
        $dummyTranslation->setLanguage($dummyLanguageEntity)
            ->setField1('value1')
            ->setField2('value2');

        $this->translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn(DummyTranslations::class);

        $this->translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($dummyEntity))
            ->once()
            ->andReturn($dummyTranslation);

        $factory = new TranslationProxyFactory($this->translationClassChecker, $this->translationManager);

        $this->assertTrue($factory->initialize($dummyEntity));

        $this->assertFalse($dummyEntity->getTranslation() instanceof TranslationEntityInterface);

        $this->assertEquals($dummyTranslation->getField1(), $dummyEntity->getTranslation()->getField1());
        $this->assertEquals($dummyTranslation->getField2(), $dummyEntity->getTranslation()->getField2());
    }

    public function tearDown()
    {
        m::close();
    }

}
