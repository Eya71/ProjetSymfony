<?php

namespace App\Controller;

use App\Service\LegacyImagePathResolver;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Connection $connection, LegacyImagePathResolver $imagePathResolver): Response
    {
        $produits = $connection->fetchAllAssociative('SELECT * FROM produit ORDER BY id_produit DESC LIMIT 12');
        foreach ($produits as &$produit) {
            $produit['resolved_image'] = $imagePathResolver->product($produit['image_path'] ?? '');
        }
        unset($produit);

        return $this->render('index.html.twig', [
            'produits' => $produits,
        ]);
    }
}
