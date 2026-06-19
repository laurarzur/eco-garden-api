<?php

namespace App\DataFixtures;

use App\Factory\AdviceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        AdviceFactory::createMany(30);
    }
}
