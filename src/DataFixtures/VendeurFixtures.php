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
        $vendeur->setIdPhoto('files_profil/mohamed.png');
        $vendeur->setNumTel('55667788');
        $vendeur->setPwd('123456');
        $manager->persist($vendeur);
        $manager->flush();
    }
    public static function getGroups(): array
    {
        return ['vendeur'];
    }
}