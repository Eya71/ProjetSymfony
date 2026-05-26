<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Commande;
use App\Entity\Vendeur;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\VendeurFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CommandeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $client = $manager->getRepository(Client::class)->findOneBy([
            'username' => 'amira',
        ]);

        $vendeur = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'mohamedabbes',
        ]);

        if (!$client) {
            throw new \Exception('Client amira introuvable. Charge d’abord ClientFixtures.');
        }

        if (!$vendeur) {
            throw new \Exception('Vendeur mohamedabbes introuvable. Charge d’abord VendeurFixtures.');
        }

        $commande1 = new Commande();
        $commande1->setClient($client);
        $commande1->setVendeur($vendeur);
        $commande1->setStatut('terminée');
        $commande1->setSource('panier');
        $commande1->setTotal('500.00');
        $manager->persist($commande1);

        $commande2 = new Commande();
        $commande2->setClient($client);
        $commande2->setVendeur($vendeur);
        $commande2->setStatut('terminée');
        $commande2->setSource('deal');
        $commande2->setTotal('95.00');
        $manager->persist($commande2);

        $commande3 = new Commande();
        $commande3->setClient($client);
        $commande3->setVendeur($vendeur);
        $commande3->setStatut('en cours');
        $commande3->setSource('panier');
        $commande3->setTotal('2100.00');
        $manager->persist($commande3);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            VendeurFixtures::class,
        ];
    }
}