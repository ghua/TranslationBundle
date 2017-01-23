<?php
namespace VKR\TranslationBundle\Tests\Services;

use VKR\TranslationBundle\Exception\TranslationException;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\TestHelpers\Entity\AbstractDummy;
use VKR\TranslationBundle\TestHelpers\Entity\Dummy;
use VKR\TranslationBundle\TestHelpers\Entity\DummyTranslations;
use VKR\TranslationBundle\TestHelpers\Entity\DummyWithArgs;
use VKR\TranslationBundle\TestHelpers\Entity\NonTranslatableDummy;
use VKR\TranslationBundle\TestHelpers\Entity\SecondDummy;
use VKR\TranslationBundle\TestHelpers\Entity\SecondDummyTranslations;

class TranslationClassCheckerTest extends \PHPUnit_Framework_TestCase
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
        $this->setExpectedException(TranslationException::class, 'Class VKR\TranslationBundle\TestHelpers\Entity\NonTranslatableDummyTranslations does not exist');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithAbstractTranslationClass()
    {
        $entity = new AbstractDummy();
        $this->setExpectedException(TranslationException::class, 'Class VKR\TranslationBundle\TestHelpers\Entity\AbstractDummyTranslations cannot be instantiated');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithTranslationClassRequiringArgs()
    {
        $entity = new DummyWithArgs();
        $this->setExpectedException(TranslationException::class, 'Class VKR\TranslationBundle\TestHelpers\Entity\DummyWithArgsTranslations requires constructor arguments');
        $this->translationClassChecker->checkTranslationClass($entity);
    }

    public function testWithClassNotExtendingAbstractTranslation()
    {
        $entity = new SecondDummy();
        $this->setExpectedException(TranslationException::class, 'Class ' . SecondDummyTranslations::class . ' does not implement TranslationEntityInterface');
        $this->translationClassChecker->checkTranslationClass($entity);
    }
}
