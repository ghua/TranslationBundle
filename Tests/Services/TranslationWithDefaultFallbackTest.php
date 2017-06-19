<?php
namespace VKR\TranslationBundle\Tests\Services;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use VKR\TranslationBundle\Services\TranslationClassChecker;
use VKR\TranslationBundle\Services\TranslationManager;
use VKR\TranslationBundle\Services\TranslationProxyFactory;
use VKR\TranslationBundle\TestHelpers\Entity\DummyLanguageEntity;
use VKR\TranslationBundle\TestHelpers\Entity\EmailTemplateEntity;
use VKR\TranslationBundle\TestHelpers\Entity\EmailTemplateTranslationEntity;
use Mockery as m;
use Symfony\Component\DependencyInjection\Container;


/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class TranslationWithDefaultFallbackTest extends TestCase
{
    public function testGetTranslatedSubjectFromEntity()
    {
        $translationManager = m::mock(TranslationManager::class);
        $translationClassChecker = m::mock(TranslationClassChecker::class);
        $container = m::mock(Container::class);

        $container->shouldReceive('get')
            ->with(m::mustBe('vkr_translation.translation_manager'))
            ->andReturn($translationManager);

        $container->shouldReceive('get')
            ->with(m::mustBe('vkr_translation.class_checker'))
            ->andReturn($translationClassChecker);

        $entity = new EmailTemplateEntity();
        $entity->setHtml('<h1>Entity</h1>');
        $entity->setSubject('Subject Entity');


        $translation = new EmailTemplateTranslationEntity();
        $language = new DummyLanguageEntity();

        $language->setCode('de');
        $translation->setLanguage($language);
        $translation->setEntity($entity);

        $translation->setHtml('<h1>Translation</h1>');

        $translationClassChecker
            ->shouldReceive('checkTranslationClass')
            ->with(m::mustBe($entity))
            ->once()
            ->andReturn(EmailTemplateTranslationEntity::class);

        $translationManager
            ->shouldReceive('getTranslation')
            ->with(m::mustBe($entity))
            ->once()
            ->andReturn($translation);

        $factory = (new TranslationProxyFactory())
            ->setContainer($container);

        $this->assertTrue($factory->initialize($entity));

        /**
         * @var EmailTemplateTranslationEntity $translation
         */
        $translation = $entity->getTranslation();

        Assert::assertEquals('<h1>Translation</h1>', $translation->getHtml());
        Assert::assertEquals('Subject Entity', $translation->getSubject());
    }
}