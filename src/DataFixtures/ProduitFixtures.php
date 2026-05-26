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
        $produit1->setDecription('ceinture une seule pièce');
        $produit1->setImagePath('files_produit/coach.png');
        $manager->persist($produit1);

        $produit2 = new Produit();
        $produit2->setVendeurUsername($vendeur);
        $produit2->setNomProduit('dyson');
        $produit2->setPrix('2450.000');
        $produit2->setQuantite(2);
        $produit2->setCategorie('femme');
        $produit2->setDecription('dyson airwrap limited edition edition valentine');
        $produit2->setImagePath('files_produit/dyson.png');
        $manager->persist($produit2);

        $produit3 = new Produit();
        $produit3->setVendeurUsername($vendeur);
        $produit3->setNomProduit('dior backstage');
        $produit3->setPrix('320.000');
        $produit3->setQuantite(10);
        $produit3->setCategorie('femme');
        $produit3->setDecription('dior backstage limted edition unique et des couleurs magnifiques');
        $produit3->setImagePath('files_produit/diorbackstage.png');
        $manager->persist($produit3);

        $produit4 = new Produit();
        $produit4->setVendeurUsername($vendeur);
        $produit4->setNomProduit('gloss-sephora');
        $produit4->setPrix('70.000');
        $produit4->setQuantite(20);
        $produit4->setCategorie('beaute');
        $produit4->setDecription('gloss sephora reference 05');
        $produit4->setImagePath('files_produit/gucci.png');
        $manager->persist($produit4);

        $produit5 = new Produit();
        $produit5->setVendeurUsername($vendeur);
        $produit5->setNomProduit('chanel5');
        $produit5->setPrix('565.000');
        $produit5->setQuantite(4);
        $produit5->setCategorie('beaute');
        $produit5->setDecription('parfum incroyable a ne pas rater');
        $produit5->setImagePath('files_produit/chanel5.png');
        $manager->persist($produit5);

        $vendeur1 = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'farahzayeni',
        ]);

        $produit6 = new Produit();
        $produit6->setVendeurUsername($vendeur1);
        $produit6->setNomProduit('medicube');
        $produit6->setPrix('110.000');
        $produit6->setQuantite(8);
        $produit6->setCategorie('beaute');
        $produit6->setDecription('mask collagene medicube korean skin care en promotion');
        $produit6->setImagePath('files_produit/medicube.png');
        $manager->persist($produit6);

        $produit7 = new Produit();
        $produit7->setVendeurUsername($vendeur1);
        $produit7->setNomProduit('dior-lipstick');
        $produit7->setPrix('220.000');
        $produit7->setQuantite(6);
        $produit7->setCategorie('beaute');
        $produit7->setDecription('dior lipstick nouveaute chez dior reference 306 rose charms ');
        $produit7->setImagePath('files_produit/dior-lipstick.png');
        $manager->persist($produit7);


        $produit8 = new Produit();
        $produit8->setVendeurUsername($vendeur1);
        $produit8->setNomProduit('prada-intense');
        $produit8->setPrix('580.000');
        $produit8->setQuantite(4);
        $produit8->setCategorie('beaute');
        $produit8->setDecription('parfum prada paradoxe intense qui a fait le trend ces jours a prix tres interessant 50ml');
        $produit8->setImagePath('files_produit/prada-intense.png');
        $manager->persist($produit8);



        $produit9 = new Produit();
        $produit9->setVendeurUsername($vendeur1);
        $produit9->setNomProduit('sac tory burch');
        $produit->setPrix('1200.000');
        $produit9->setQuantite(1);
        $produit9->setCategorie('femme');
        $produit9->setDecription('sac tory burch mini tres joli couleur rouge pour l ete');
        $produit9->setImagePath('files_produit/tory.png');
        $manager->persist($produit9);

        $vendeur2 = $manager->getRepository(Vendeur::class)->findOneBy([
            'username' => 'mahdiabbes',
        ]);

        $produit10 = new Produit();
        $produit10->setVendeurUsername($vendeur2);
        $produit10->setNomProduit('libre-cherry');
        $produit10->setPrix('485.000');
        $produit10->setQuantite(3);
        $produit10->setCategorie('femme');
        $produit10->setDecription('parfum nouveaut ysl cherry parfait pour la saison');
        $produit10->setImagePath('files_produit/libre-cherry.png');
        $manager->persist($produit10);


        $produit11 = new Produit();
        $produit11->setVendeurUsername($vendeur2);
        $produit11->setNomProduit('ceinture coach ');
        $produit11->setPrix('500.000');
        $produit11->setQuantite(2);
        $produit11->setCategorie('homme');
        $produit11->setDecription('ceinture coach noir pour homme tres classy');
        $produit11->setImagePath('files_produit/coach-homme.png');
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