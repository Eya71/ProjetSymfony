<?php

namespace App\Controller;

use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClientInterfaceController extends AbstractController
{
    #[Route('/client-interface', name: 'app_client_interface')]
    public function index(Connection $connection, LegacyImagePathResolver $imagePathResolver): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        $user = $this->getUser();
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';
        $userInfo = $connection->fetchAssociative('SELECT idphoto FROM client WHERE username = :username', [
            'username' => $username,
        ]) ?: [];

        $notifCount = 0;
        $messageCount = 0;

        try {
            $notifCount = (int) $connection->fetchOne(
                'SELECT COUNT(*) FROM deal_request WHERE client_username = :username AND (client_seen_at IS NULL OR created_at > client_seen_at)',
                ['username' => $username]
            );
        } catch (\Throwable) {
        }

        try {
            $messageCount = (int) $connection->fetchOne(
                'SELECT COUNT(*) FROM message WHERE receiver_username = :username AND is_read = 0',
                ['username' => $username]
            );
        } catch (\Throwable) {
        }

        $produits = $connection->fetchAllAssociative('SELECT * FROM produit ORDER BY id_produit DESC LIMIT 12');
        foreach ($produits as &$produit) {
            $produit['resolved_image'] = $imagePathResolver->product($produit['image_path'] ?? '');
        }
        unset($produit);

        return $this->render('client/index.html.twig', [
            'username' => $username,
            'photoUrl' => $imagePathResolver->profile($userInfo['idphoto'] ?? ''),
            'notifCount' => $notifCount,
            'messageCount' => $messageCount,
            'produits' => $produits,
        ]);
    }
}
