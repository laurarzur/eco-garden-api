<?php

namespace App\DataFixtures;

use App\Entity\Month;
use App\Factory\AdviceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    private const MONTHS = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre',
    ];

    public function load(ObjectManager $manager): void
    {
        $months = [];

        foreach (self::MONTHS as $id => $name) {
            $month = new Month();
            $month->setId($id);
            $month->setName($name);

            $manager->persist($month);
            $months[] = $month;
        }

        $manager->flush();

        AdviceFactory::createMany(30, function () use ($months) {
            $randomMonths = (array) array_rand($months, random_int(1, 3));

            return [
                'months' => array_map(fn($key) => $months[$key], (array) $randomMonths),
            ];
        });
    }
}
