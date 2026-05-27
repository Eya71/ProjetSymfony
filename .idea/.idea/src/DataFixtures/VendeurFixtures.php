<?php

namespace App\DataFixtures;

use App\Entity\Vendeur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class VendeurFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $vendeur = new Vendeur();

        $vendeur->setUsername('mohamedabbes');
        $vendeur->setEmail('mohamedabbes@gmail.com');
        $vendeur->setAdresse('sfax');
        $vendeur->setNumTel('55667788');
        $vendeur->setIdPhoto('files_profil/mohamed.png');
        $vendeur->setPwd('mohamed01');
        $vendeur->setCreatedAt(new \DateTimeImmutable('2026-03-30'));

        $manager->persist($vendeur);

        $vendeur1 = new Vendeur();

        $vendeur1->setUsername('farahzayeni');
        $vendeur1->setEmail('farahzayeni@gmail.com');
        $vendeur1->setAdresse('sfax');
        $vendeur1->setNumTel('554256968');
        $vendeur1->setIdPhoto('files_profil/farah.png');
        $vendeur1->setPwd('farah01');
        $vendeur1->setCreatedAt(new \DateTimeImmutable('2026-04-12'));

        $manager->persist($vendeur1);

        $vendeur2 = new Vendeur();

        $vendeur2->setUsername('mahdiabbes');
        $vendeur2->setEmail('mahdiabbes@gmail.com');
        $vendeur2->setAdresse('tunis');
        $vendeur2->setNumTel('28595907');
        $vendeur2->setIdPhoto('files_profil/mahdi.png');
        $vendeur2->setPwd('mahdi01');
        $vendeur2->setCreatedAt(new \DateTimeImmutable('2026-04-22'));

        $manager->persist($vendeur2);


        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['vendeur'];
    }
}