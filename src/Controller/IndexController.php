<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Service\LegacyImagePathResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProduitRepository $produitRepository, LegacyImagePathResolver $imagePathResolver): Response
    {
        $produits = $produitRepository->findLatestWithVendeur();

        return $this->render('index.html.twig', [
            'produits' => $produits,
            'productImages' => $this->resolveProductImages($produits, $imagePathResolver),
        ]);
    }

    private function resolveProductImages(array $produits, LegacyImagePathResolver $imagePathResolver): array
    {
        $images = [];
        foreach ($produits as $produit) {
            $images[$produit->getId()] = $imagePathResolver->product($produit->getImagePath());
        }

        return $images;
    }
}
