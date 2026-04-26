<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Car;
use App\Entity\Colour;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $colours = $this->createColours($manager);
        $this->createCars($manager, $colours);

        $manager->flush();
    }

    /** @return Colour[] */
    private function createColours(ObjectManager $manager): array
    {
        $names = ['Red', 'Blue', 'Black', 'White'];
        $colours = [];

        foreach ($names as $name) {
            $colour = new Colour();
            $colour->setName($name);
            $manager->persist($colour);
            $colours[] = $colour;
        }

        return $colours;
    }

    /** @param Colour[] $colours */
    private function createCars(ObjectManager $manager, array $colours): void
    {
        $cars = [
            ['Toyota', 'Corolla', '2020-03-15'],
            ['Ford', 'Focus', '2019-07-22'],
            ['BMW', '3 Series', '2021-11-01'],
            ['Honda', 'Civic', '2018-05-30'],
            ['Audi', 'A4', '2022-01-10'],
            ['Mercedes', 'C-Class', '2020-09-05'],
            ['Volkswagen', 'Golf', '2017-12-18'],
            ['Nissan', 'Qashqai', '2021-06-25'],
            ['Hyundai', 'i30', '2019-02-14'],
            ['Kia', 'Sportage', '2023-04-08'],
        ];

        foreach ($cars as $index => [$make, $model, $date]) {
            $car = new Car();
            $car->setMake($make);
            $car->setModel($model);
            $car->setBuildDate(new \DateTime($date));
            $car->setColour($colours[$index % count($colours)]);
            $manager->persist($car);
        }
    }
}
