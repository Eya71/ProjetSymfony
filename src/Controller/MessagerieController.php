<?php

namespace App\Controller;

use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use App\Service\NotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessagerieController extends AbstractController
{
    private function currentUser(): array
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException();
        }
        return [$user->getUsername(), $user->getLegacyRole()];
    }

    #[Route('/messagerie', name: 'app_messagerie_index')]
    public function index(Connection $connection, LegacyImagePathResolver $imagePathResolver, NotificationService $notif): Response
    {
        [$username, $role] = $this->currentUser();

        $sql = $role === 'client'
            ? 'SELECT dr.id_deal, dr.client_username, dr.vendeur_username, dr.status, dr.prix_propose,
                      d.nom_produit, d.id_photo,
                      (SELECT MAX(created_at) FROM message m WHERE m.id_deal = dr.id_deal) AS last_msg_at,
                      (SELECT contenu FROM message m WHERE m.id_deal = dr.id_deal ORDER BY id_message DESC LIMIT 1) AS last_msg,
                      (SELECT COUNT(*) FROM message m WHERE m.id_deal = dr.id_deal AND m.receiver_username = :u AND m.is_read = 0) AS unread
               FROM deal_request dr
               JOIN demande d ON d.id_demande = dr.id_demande
               WHERE dr.client_username = :u
               ORDER BY last_msg_at DESC, dr.id_deal DESC'
            : 'SELECT dr.id_deal, dr.client_username, dr.vendeur_username, dr.status, dr.prix_propose,
                      d.nom_produit, d.id_photo,
                      (SELECT MAX(created_at) FROM message m WHERE m.id_deal = dr.id_deal) AS last_msg_at,
                      (SELECT contenu FROM message m WHERE m.id_deal = dr.id_deal ORDER BY id_message DESC LIMIT 1) AS last_msg,
                      (SELECT COUNT(*) FROM message m WHERE m.id_deal = dr.id_deal AND m.receiver_username = :u AND m.is_read = 0) AS unread
               FROM deal_request dr
               JOIN demande d ON d.id_demande = dr.id_demande
               WHERE dr.vendeur_username = :u
               ORDER BY last_msg_at DESC, dr.id_deal DESC';

        $threads = $connection->fetchAllAssociative($sql, ['u' => $username]);
        foreach ($threads as &$t) {
            $t['photo_url'] = $imagePathResolver->product($t['id_photo'] ?? '');
        }
        unset($t);

        $userInfo = $connection->fetchAssociative(
            'SELECT idphoto FROM '.($role === 'vendeur' ? 'vendeur' : 'client').' WHERE username = :u',
            ['u' => $username]
        ) ?: [];

        return $this->render('messagerie/index.html.twig', [
            'username' => $username,
            'role' => $role,
            'threads' => $threads,
            'photoUrl' => $imagePathResolver->profile($userInfo['idphoto'] ?? ''),
            'notifCount' => $notif->unreadCount($username, $role),
            'messageCount' => $this->totalUnreadMessages($connection, $username),
        ]);
    }

    #[Route('/messagerie/{idDeal}', name: 'app_messagerie_thread', requirements: ['idDeal' => '\d+'])]
    public function thread(int $idDeal, Connection $connection, LegacyImagePathResolver $imagePathResolver, NotificationService $notif): Response
    {
        [$username, $role] = $this->currentUser();
        $deal = $this->loadDeal($connection, $idDeal, $username, $role);

        $otherUsername = $role === 'client' ? $deal['vendeur_username'] : $deal['client_username'];
        $otherRole = $role === 'client' ? 'vendeur' : 'client';
        $otherInfo = $connection->fetchAssociative(
            'SELECT idphoto FROM '.($otherRole === 'vendeur' ? 'vendeur' : 'client').' WHERE username = :u',
            ['u' => $otherUsername]
        ) ?: [];

        $userInfo = $connection->fetchAssociative(
            'SELECT idphoto FROM '.($role === 'vendeur' ? 'vendeur' : 'client').' WHERE username = :u',
            ['u' => $username]
        ) ?: [];

        return $this->render('messagerie/thread.html.twig', [
            'username' => $username,
            'role' => $role,
            'deal' => $deal,
            'otherUsername' => $otherUsername,
            'otherPhoto' => $imagePathResolver->profile($otherInfo['idphoto'] ?? ''),
            'demandePhoto' => $imagePathResolver->product($deal['id_photo'] ?? ''),
            'photoUrl' => $imagePathResolver->profile($userInfo['idphoto'] ?? ''),
            'notifCount' => $notif->unreadCount($username, $role),
            'messageCount' => $this->totalUnreadMessages($connection, $username),
        ]);
    }

    #[Route('/messagerie/{idDeal}/messages', name: 'app_messagerie_fetch', requirements: ['idDeal' => '\d+'], methods: ['GET'])]
    public function fetchMessages(int $idDeal, Request $request, Connection $connection): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $this->loadDeal($connection, $idDeal, $username, $role);

        $since = (int) $request->query->get('since', 0);

        $rows = $connection->fetchAllAssociative(
            'SELECT id_message, sender_username, receiver_username, contenu, created_at, is_read
             FROM message
             WHERE id_deal = :d AND id_message > :since
             ORDER BY id_message ASC',
            ['d' => $idDeal, 'since' => $since]
        );

        $connection->executeStatement(
            'UPDATE message SET is_read = 1
             WHERE id_deal = :d AND receiver_username = :u AND is_read = 0',
            ['d' => $idDeal, 'u' => $username]
        );

        $connection->executeStatement(
            "UPDATE notification SET is_read = 1
             WHERE recipient_username = :u AND recipient_role = :r AND type = 'new_message' AND related_id = :d AND is_read = 0",
            ['u' => $username, 'r' => $role, 'd' => $idDeal]
        );

        return new JsonResponse([
            'messages' => array_map(fn(array $m) => [
                'id' => (int) $m['id_message'],
                'sender' => $m['sender_username'],
                'receiver' => $m['receiver_username'],
                'contenu' => $m['contenu'],
                'created_at' => $m['created_at'],
                'is_read' => (bool) $m['is_read'],
                'mine' => $m['sender_username'] === $username,
            ], $rows),
        ]);
    }

    #[Route('/messagerie/{idDeal}/send', name: 'app_messagerie_send', requirements: ['idDeal' => '\d+'], methods: ['POST'])]
    public function send(int $idDeal, Request $request, Connection $connection, NotificationService $notif): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $deal = $this->loadDeal($connection, $idDeal, $username, $role);

        $payload = json_decode((string) $request->getContent(), true) ?: [];
        $contenu = trim((string) ($payload['contenu'] ?? $request->request->get('contenu', '')));
        if ($contenu === '') {
            return new JsonResponse(['error' => 'empty'], 400);
        }

        $receiver = $role === 'client' ? $deal['vendeur_username'] : $deal['client_username'];
        $receiverRole = $role === 'client' ? 'vendeur' : 'client';
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $connection->insert('message', [
            'id_deal' => $idDeal,
            'sender_username' => $username,
            'receiver_username' => $receiver,
            'contenu' => $contenu,
            'created_at' => $now,
            'is_read' => 0,
        ]);
        $id = (int) $connection->lastInsertId();

        $notif->notifyNewMessage($username, $receiver, $receiverRole, $idDeal, $contenu);

        return new JsonResponse([
            'message' => [
                'id' => $id,
                'sender' => $username,
                'receiver' => $receiver,
                'contenu' => $contenu,
                'created_at' => $now,
                'is_read' => false,
                'mine' => true,
            ],
        ]);
    }

    #[Route('/messagerie/start/{idDeal}', name: 'app_messagerie_start', requirements: ['idDeal' => '\d+'])]
    public function startFromDeal(int $idDeal): Response
    {
        return $this->redirectToRoute('app_messagerie_thread', ['idDeal' => $idDeal]);
    }

    private function loadDeal(Connection $connection, int $idDeal, string $username, string $role): array
    {
        $deal = $connection->fetchAssociative(
            'SELECT dr.id_deal, dr.id_demande, dr.client_username, dr.vendeur_username, dr.status, dr.prix_propose,
                    d.nom_produit, d.id_photo, d.categorie
             FROM deal_request dr
             JOIN demande d ON d.id_demande = dr.id_demande
             WHERE dr.id_deal = :d',
            ['d' => $idDeal]
        );
        if (!$deal) {
            throw $this->createNotFoundException('Conversation introuvable.');
        }
        $authorized = ($role === 'client' && $deal['client_username'] === $username)
            || ($role === 'vendeur' && $deal['vendeur_username'] === $username);
        if (!$authorized) {
            throw $this->createAccessDeniedException();
        }
        return $deal;
    }

    private function totalUnreadMessages(Connection $connection, string $username): int
    {
        return (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM message WHERE receiver_username = :u AND is_read = 0',
            ['u' => $username]
        );
    }
}
