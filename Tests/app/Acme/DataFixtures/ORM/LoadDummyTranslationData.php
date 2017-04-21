<?php


namespace Acme\DataFixtures\ORM;

use Acme\Entity\DummyTranslation;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDummyTranslationData extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 2;
    }

    public function load(ObjectManager $manager)
    {
        $translation = new DummyTranslation();
        $translation->setEntity($this->getReference('dummy'));
        $translation->setLanguage($this->getReference('language_en'));
        $translation->setName('translation');
        $manager->persist($translation);

        $translation = new DummyTranslation();
        $translation->setEntity($this->getReference('dummy'));
        $translation->setLanguage($this->getReference('language_de'));
        $translation->setName('Übersetzung');
        $manager->persist($translation);

        $manager->flush();
    }
}