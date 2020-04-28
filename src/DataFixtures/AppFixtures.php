<?php

namespace App\DataFixtures;

use App\Entity\Movie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        for ($i = 0; $i < 20; $i++) {
            $movie = new Movie();
            $movie->setTitle($faker->text($maxNbChars = 20));
            $movie->setRate($faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 10));
            $movie->setStock($faker->numberBetween($min = 0, $max = 10));
            $manager->persist($movie);
        }
        $manager->flush();
    }
}
