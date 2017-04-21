<?php


namespace Acme\DataFixtures\ORM;

use Acme\Entity\Dummy;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDummyData extends AbstractFixture implements OrderedFixtureInterface
{
    public function getOrder()
    {
        return 1;
    }

    public function load(ObjectManager $manager)
    {
        $dummy = new Dummy();
        $dummy->setName('original');

        $manager->persist($dummy);
        $manager->flush();

        $this->addReference('dummy', $dummy);
    }
}