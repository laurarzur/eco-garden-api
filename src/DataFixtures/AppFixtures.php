<?php

namespace App\DataFixtures;

use App\Entity\Month;
use App\Entity\User;
use App\Factory\AdviceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'un user simple
        $user = new User();
        $user->setEmail("user@example.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->hasher->hashPassword($user, "password"));
        $user->setCity("Toulouse");
        $user->setZipcode("31000");
        $manager->persist($user);

        // Création d'un user admin
        $userAdmin = new User();
        $userAdmin->setEmail("admin@ecogarden.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->hasher->hashPassword($userAdmin, "password"));
        $userAdmin->setCity("Saint-Malo");
        $userAdmin->setZipcode("35400");
        $manager->persist($userAdmin);

        // Création des mois
        $months = [];

        foreach (self::MONTHS as $id => $name) {
            $month = new Month();
            $month->setId($id);
            $month->setName($name);

            $manager->persist($month);
            $months[] = $month;
        }


        // Création des conseils
        AdviceFactory::createMany(30, function () use ($months) {
            $randomMonths = (array) array_rand($months, random_int(1, 3));

            return [
                'months' => array_map(fn($key) => $months[$key], (array) $randomMonths),
            ];
        });

        $manager->flush();
    }
}
