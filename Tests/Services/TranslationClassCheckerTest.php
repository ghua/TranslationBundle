<?php
namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Entity\TranslationEntityInterface;
use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\TestHelpers\Entity\AbstractDummy;
use VKR\TranslationBundle\TestHelpers\Entity\AbstractDummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithArgs;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithArgsTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\NonTranslatableDummy;
use VKR\TranslationBundle\TestHelpers\Entity\SecondDummy;
use VKR\TranslationBundle\TestHelpers\Entity\SecondDummyTranslations;

class TranslationClassCheckerTest extends TestCase
{
    /**
     * @var TranslationClassChecker
     */
    private $translationClassChecker;

    public function setUp()
    {
        $this->translationClassChecker = new TranslationClassChecker();
    }

    public function testWithCorrectClass()
    {
        $entity = new Dummy();
        $translationClass = $this->translationClassChecker->checkTranslationClass($entity);
        $this->assertEquals(DummyTranslations::class, $translationClass);
    }

    public function testWithoutTranslationClass()
    {
        $entity = new NonTranslatableDummy();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Class VKR\TranslationBundle\TestHelpers\Entity\NonTranslatableDummyTranslations does not exist');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithAbstractTranslationClass()
    {
        $entity = new AbstractDummy();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Class ' . AbstractDummyTranslations::class . ' cannot be instantiated');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithTranslationClassRequiringArgs()
    {
        $entity = new DummyWithArgs();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Class ' . DummyWithArgsTranslations::class . ' requires constructor arguments');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithClassNotExtendingAbstractTranslation()
    {
        $entity = new SecondDummy();
        $this->expectException(TranslationException::class);
        $this->expectExceptionMessage('Class ' . SecondDummyTranslations::class . ' does not implement ' . TranslationEntityInterface::class);
        $this->translationClassChecker->checkTranslationClass($entity);
    }
}
