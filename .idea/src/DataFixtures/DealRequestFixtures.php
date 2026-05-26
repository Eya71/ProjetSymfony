<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\DealRequest;
use App\Entity\Vendeur;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\VendeurFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class DealRequestFixtures extends Fixture implements DependentFixtureInterface
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

        /*
         * Deal 1 accepté
         * Celui-ci peut être noté si aucun avis n'existe encore pour ce deal.
         */
        $deal1 = new DealRequest();
        $deal1->setClientUsername($client);
        $deal1->setVendeurUsername($vendeur);
        $deal1->setPrixPropose('480.00');
        $deal1->setMessage('Je suis intéressée par la ceinture coach.');
        $deal1->setStatus('accepté');
        $deal1->setCreatedAt(new \DateTimeImmutable());
        $deal1->setClientSeenAt(new \DateTimeImmutable());
        $deal1->setVendeurSeenAt(new \DateTimeImmutable());

        $manager->persist($deal1);

        /*
         * Deal 2 accepté
         * Celui-ci permet de tester que le formulaire affiche plusieurs deals.
         */
        $deal2 = new DealRequest();
        $deal2->setClientUsername($client);
        $deal2->setVendeurUsername($vendeur);
        $deal2->setPrixPropose('2000.00');
        $deal2->setMessage('Je souhaite négocier le prix du Dyson Airwrap.');
        $deal2->setStatus('accepté');
        $deal2->setCreatedAt(new \DateTimeImmutable());
        $deal2->setClientSeenAt(new \DateTimeImmutable());
        $deal2->setVendeurSeenAt(new \DateTimeImmutable());

        $manager->persist($deal2);

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