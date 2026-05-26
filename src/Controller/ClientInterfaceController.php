<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\DealRequestRepository;
use App\Repository\MessageRepository;
use App\Repository\ProduitRepository;
use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientInterfaceController extends AbstractController
{
    #[Route('/client-interface', name: 'app_client_interface')]
    public function index(
        ClientRepository $clientRepository,
        DealRequestRepository $dealRequestRepository,
        MessageRepository $messageRepository,
        ProduitRepository $produitRepository,
        LegacyImagePathResolver $imagePathResolver,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';
        $client = $clientRepository->findOneBy(['username' => $username]);

        $notifCount = 0;
        $messageCount = 0;

        if ($client !== null) {
            $notifCount = $dealRequestRepository->countUnseenForClient($client);
        }

        if ($username !== '') {
            $messageCount = $messageRepository->countUnreadForReceiver($username);
        }

        $produits = $produitRepository->findLatestWithVendeur();

        return $this->render('client/index.html.twig', [
            'username' => $username,
            'photoUrl' => $imagePathResolver->profile($client?->getIdPhoto()),
            'notifCount' => $notifCount,
            'messageCount' => $messageCount,
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
