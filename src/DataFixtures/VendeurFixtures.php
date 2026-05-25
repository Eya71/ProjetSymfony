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
        $vendeur->setPwd('123456');
        $vendeur->setCreatedAt(new \DateTimeImmutable('2026-03-30'));

        $manager->persist($vendeur);

        // Très important : enregistre vraiment dans la base
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['vendeur'];
    }
}