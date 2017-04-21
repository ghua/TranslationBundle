<?php


namespace VKR\TranslationBundle\Tests\ServicesFunctional;

use Acme\Entity\DummyTranslation;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Acme\Entity\Dummy;
use VKR\TranslationBundle\Services\DoctrineTranslationDriver;

class DoctrineTranslationDriverTest extends WebTestCase
{

    /**
     * @var Application
     */
    static $application;
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        self::$application = new Application(static::$kernel);
        self::$application->setAutoExit(false);
        self::$application->run(new StringInput('doctrine:schema:update --quiet --force'));
        self::$application->run(
            new StringInput(
                sprintf('doctrine:fixtures:load --quiet --fixtures=%s',
                    static::$kernel->getRootDir() . '/Acme/DataFixtures/')
            )
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        self::$application->run(new StringInput('doctrine:schema:drop --quiet --force'));
    }

    /**
     * @param string $alias
     *
     * @return object
     */
    public function get($alias)
    {
        return static::$kernel->getContainer()->get($alias);
    }

    public function testGetTranslation()
    {
        $this->get('locale_retriever')->setCurrentLocale('de');

        /**
         * @var \Acme\Entity\Dummy[] $result
         */
        $result = $this->get('doctrine.orm.entity_manager')
            ->getRepository(Dummy::class)
            ->findAll();

        $this->assertNotEmpty($result);

        $dummy = $result[0];
        $this->assertEquals(1, $dummy->getTranslations()->count());

        /**
         * @var DummyTranslation $translation
         */
        $translation = $dummy->getTranslations()->first();

        $this->assertEquals('de', $translation->getLanguage()->getCode());
        $this->assertEquals('Übersetzung', $translation->getName());
        $this->assertEquals($dummy, $translation->getEntity());

        /**
         * @var DoctrineTranslationDriver $doctrineTranslationDriver
         */
        $doctrineTranslationDriver = $this->get('vkr_translation.drivers.doctrine');

        $this->assertEquals($translation, $doctrineTranslationDriver->getTranslation($dummy, 'de'));
    }

}
