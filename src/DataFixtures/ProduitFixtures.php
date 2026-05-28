<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use App\Entity\Vendeur;
use App\DataFixtures\VendeurFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProduitFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $vendeur = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'mohamedabbes',
        ]);

        if (!$vendeur) {
            throw new \Exception('Vendeur mohamedabbes introuvable. Charge d’abord VendeurFixtures.');
        }

        $produit1 = new Produit();
        $produit1->setVendeurUsername($vendeur);
        $produit1->setNomProduit('ceinture coach');
        $produit1->setPrix('500.00');
        $produit1->setQuantite(1);
        $produit1->setCategorie('homme');
        $produit1->setDescription('ceinture une seule pièce');
        $produit1->setImagePath('files_produit/coach.png');
        $produit1->setCreatedAt(new \DateTimeImmutable('2026-01-05 10:15:00'));
        $manager->persist($produit1);

        $produit2 = new Produit();
        $produit2->setVendeurUsername($vendeur);
        $produit2->setNomProduit('dyson');
        $produit2->setPrix('2450.000');
        $produit2->setQuantite(2);
        $produit2->setCategorie('femme');
        $produit2->setDescription('dyson airwrap limited edition edition valentine');
        $produit2->setImagePath('files_produit/dyson.png');
        $produit2->setCreatedAt(new \DateTimeImmutable('2026-01-12 14:30:00'));
        $manager->persist($produit2);

        $produit3 = new Produit();
        $produit3->setVendeurUsername($vendeur);
        $produit3->setNomProduit('dior backstage');
        $produit3->setPrix('320.000');
        $produit3->setQuantite(10);
        $produit3->setCategorie('femme');
        $produit3->setDescription('dior backstage limted edition unique et des couleurs magnifiques');
        $produit3->setImagePath('files_produit/diorbackstage.png');
        $produit3->setCreatedAt(new \DateTimeImmutable('2026-01-20 09:45:00'));
        $manager->persist($produit3);

        $produit4 = new Produit();
        $produit4->setVendeurUsername($vendeur);
        $produit4->setNomProduit('gloss-sephora');
        $produit4->setPrix('70.000');
        $produit4->setQuantite(20);
        $produit4->setCategorie('beaute');
        $produit4->setDescription('gloss sephora reference 05');
        $produit4->setImagePath('files_produit/gloss-sephora.png');
        $produit4->setCreatedAt(new \DateTimeImmutable('2026-02-03 16:20:00'));
        $manager->persist($produit4);

        $produit5 = new Produit();
        $produit5->setVendeurUsername($vendeur);
        $produit5->setNomProduit('chanel5');
        $produit5->setPrix('565.000');
        $produit5->setQuantite(4);
        $produit5->setCategorie('beaute');
        $produit5->setDescription('parfum incroyable a ne pas rater');
        $produit5->setImagePath('files_produit/chanel5.png');
        $produit5->setCreatedAt(new \DateTimeImmutable('2026-02-15 11:10:00'));
        $manager->persist($produit5);

        $vendeur1 = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'farahzayeni',
        ]);

        if (!$vendeur1) {
            throw new \Exception('Vendeur farahzayeni introuvable. Charge d’abord VendeurFixtures.');
        }

        $produit6 = new Produit();
        $produit6->setVendeurUsername($vendeur1);
        $produit6->setNomProduit('medicube');
        $produit6->setPrix('110.000');
        $produit6->setQuantite(8);
        $produit6->setCategorie('beaute');
        $produit6->setDescription('mask collagene medicube korean skin care en promotion');
        $produit6->setImagePath('files_produit/medicube.png');
        $produit6->setCreatedAt(new \DateTimeImmutable('2026-03-01 13:25:00'));
        $manager->persist($produit6);

        $produit7 = new Produit();
        $produit7->setVendeurUsername($vendeur1);
        $produit7->setNomProduit('dior-lipstick');
        $produit7->setPrix('220.000');
        $produit7->setQuantite(6);
        $produit7->setCategorie('beaute');
        $produit7->setDescription('dior lipstick nouveaute chez dior reference 306 rose charms');
        $produit7->setImagePath('files_produit/dior-lipstick.png');
        $produit7->setCreatedAt(new \DateTimeImmutable('2026-03-10 17:40:00'));
        $manager->persist($produit7);

        $produit8 = new Produit();
        $produit8->setVendeurUsername($vendeur1);
        $produit8->setNomProduit('prada-intense');
        $produit8->setPrix('580.000');
        $produit8->setQuantite(4);
        $produit8->setCategorie('beaute');
        $produit8->setDescription('parfum prada paradoxe intense qui a fait le trend ces jours a prix tres interessant 50ml');
        $produit8->setImagePath('files_produit/prada-intense.png');
        $produit8->setCreatedAt(new \DateTimeImmutable('2026-03-22 08:55:00'));
        $manager->persist($produit8);

        $produit9 = new Produit();
        $produit9->setVendeurUsername($vendeur1);
        $produit9->setNomProduit('sac tory burch');
        $produit9->setPrix('1200.000');
        $produit9->setQuantite(1);
        $produit9->setCategorie('femme');
        $produit9->setDescription('sac tory burch mini tres joli couleur rouge pour l ete');
        $produit9->setImagePath('files_produit/tory.png');
        $produit9->setCreatedAt(new \DateTimeImmutable('2026-04-04 12:05:00'));
        $manager->persist($produit9);

        $vendeur2 = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'mahdiabbes',
        ]);

        if (!$vendeur2) {
            throw new \Exception('Vendeur mahdiabbes introuvable. Charge d’abord VendeurFixtures.');
        }

        $produit10 = new Produit();
        $produit10->setVendeurUsername($vendeur2);
        $produit10->setNomProduit('libre-cherry');
        $produit10->setPrix('485.000');
        $produit10->setQuantite(3);
        $produit10->setCategorie('femme');
        $produit10->setDescription('parfum nouveaut ysl cherry parfait pour la saison');
        $produit10->setImagePath('files_produit/libre-cherry.png');
        $produit10->setCreatedAt(new \DateTimeImmutable('2026-04-18 15:35:00'));
        $manager->persist($produit10);

        $produit11 = new Produit();
        $produit11->setVendeurUsername($vendeur2);
        $produit11->setNomProduit('ceinture coach');
        $produit11->setPrix('500.000');
        $produit11->setQuantite(2);
        $produit11->setCategorie('homme');
        $produit11->setDescription('ceinture coach noir pour homme tres classy');
        $produit11->setImagePath('files_produit/coach-homme.png');
        $produit11->setCreatedAt(new \DateTimeImmutable('2026-05-02 19:00:00'));
        $manager->persist($produit11);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VendeurFixtures::class,
        ];
    }
}