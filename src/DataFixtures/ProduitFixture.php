<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProduitFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $produits = [

            [
                'nom' => 'ceinture coach',
                'prix' => 500,
                'quantite' => 1,
                'categorie' => 'homme',
                'description' => 'ceinture une seule piece',
                'image' => 'files_produit/coach.png'
            ],

            [
                'nom' => 'tissout',
                'prix' => 1220,
                'quantite' => 2,
                'categorie' => 'homme',
                'description' => 'tissout disponible',
                'image' => 'files_produit/tissout.png'
            ],

            [
                'nom' => 'medicube mask collagene',
                'prix' => 95,
                'quantite' => 15,
                'categorie' => 'femme',
                'description' => 'mask collagene',
                'image' => 'files_produit/mask.png'
            ],

            [
                'nom' => 'serum collagene',
                'prix' => 105,
                'quantite' => 9,
                'categorie' => 'femme',
                'description' => 'serum koreen',
                'image' => 'files_produit/serum.png'
            ],

            [
                'nom' => 'parfum prada paradox',
                'prix' => 520,
                'quantite' => 1,
                'categorie' => 'beaute',
                'description' => 'parfum original',
                'image' => 'files_produit/prada.png'
            ],

            [
                'nom' => 'dyson airwrap',
                'prix' => 2100,
                'quantite' => 1,
                'categorie' => 'femme',
                'description' => 'dyson original',
                'image' => 'files_produit/dyson.png'
            ],

            [
                'nom' => 'parfum gucci flora',
                'prix' => 2000,
                'quantite' => 4,
                'categorie' => 'beaute',
                'description' => 'gucci flora',
                'image' => 'files_produit/gucci.png'
            ]

        ];

        foreach ($produits as $p) {

            $produit = new Produit();

            $produit->setNomProduit($p['nom']);

            $produit->setPrix($p['prix']);

            $produit->setQuantite($p['quantite']);

            $produit->setCategorie($p['categorie']);

            $produit->setDecription($p['description']);

            $produit->setImagePath($p['image']);

            $manager->persist($produit);

        }

        $manager->flush();
    }
}