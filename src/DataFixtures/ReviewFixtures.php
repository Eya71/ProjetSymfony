<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\DealRequest;
use App\Entity\Review;
use App\Entity\Vendeur;
use App\DataFixtures\ClientFixtures;
use App\DataFixtures\DealRequestFixtures;
use App\DataFixtures\VendeurFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $client = $manager->getRepository(Client::class)->findOneBy([
            'username' => 'amira',
        ]);

        $vendeur = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'mohamedabbes',
        ]);

        $deal = $manager->getRepository(DealRequest::class)->findOneBy([
            'client_username' => $client,
            'vendeur_username' => $vendeur,
        ]);

        if (!$client) {
            throw new \Exception('Client amira introuvable.');
        }

        if (!$vendeur) {
            throw new \Exception('Vendeur mohamedabbes introuvable.');
        }

        if (!$deal) {
            throw new \Exception('DealRequest introuvable.');
        }

        $review = new Review();
        $review->setIdDeal($deal);
        $review->setClientUsername($client);
        $review->setVendeurUsername($vendeur);
        $review->setRating(5);
        $review->setCommentaire('Excellent vendeur, très sérieux et livraison rapide.');
        $review->setCreatedAt(new \DateTimeImmutable());

        $manager->persist($review);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            VendeurFixtures::class,
            DealRequestFixtures::class,
        ];
    }
}