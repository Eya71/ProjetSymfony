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
        $manager->persist($produit1);

        $produit2 = new Produit();
        $produit2->setVendeurUsername($vendeur);
        $produit2->setNomProduit('medicube mask collagene');
        $produit2->setPrix('95.00');
        $produit2->setQuantite(15);
        $produit2->setCategorie('femme');
        $produit2->setDescription('mask collagene');
        $produit2->setImagePath('files_produit/mask.png');
        $manager->persist($produit2);

        $produit3 = new Produit();
        $produit3->setVendeurUsername($vendeur);
        $produit3->setNomProduit('dyson airwrap');
        $produit3->setPrix('2100.00');
        $produit3->setQuantite(1);
        $produit3->setCategorie('femme');
        $produit3->setDescription('dyson original');
        $produit3->setImagePath('files_produit/dyson.png');
        $manager->persist($produit3);

        $produit4 = new Produit();
        $produit4->setVendeurUsername($vendeur);
        $produit4->setNomProduit('parfum gucci flora');
        $produit4->setPrix('2000.00');
        $produit4->setQuantite(4);
        $produit4->setCategorie('beaute');
        $produit4->setDescription('gucci flora');
        $produit4->setImagePath('files_produit/gucci.png');
        $manager->persist($produit4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VendeurFixtures::class,
        ];
    }
}