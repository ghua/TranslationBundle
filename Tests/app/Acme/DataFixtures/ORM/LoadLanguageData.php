<?php

namespace Acme\DataFixtures\ORM;

use Acme\Entity\Language;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadLanguageData extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 0;
    }

    public function load(ObjectManager $manager)
    {
        foreach (['en', 'de'] as $code) {
            $language = new Language();
            $language->setCode($code);
            $manager->persist($language);
            $this->addReference(sprintf('language_%s', $code), $language);
        }
        $manager->flush();
    }
}
