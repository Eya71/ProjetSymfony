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

        $deal = new DealRequest();
        $deal->setClientUsername($client);
        $deal->setVendeurUsername($vendeur);
        $deal->setPrixPropose('480.00');
        $deal->setMessage('Je suis intéressée par ce produit.');
        $deal->setCreatedAt(new \DateTimeImmutable());
        $deal->setClientSeenAt(new \DateTimeImmutable());
        $deal->setVendeurSeenAt(new \DateTimeImmutable());

        $manager->persist($deal);
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