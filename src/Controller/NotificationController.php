<?php

namespace App\Controller;

use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use App\Service\NotificationService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NotificationController extends AbstractController
{
    private function currentUser(): array
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException();
        }
        return [$user->getUsername(), $user->getLegacyRole()];
    }

    #[Route('/notifications', name: 'app_notifications_index')]
    public function index(NotificationService $notif, Connection $connection, LegacyImagePathResolver $imagePathResolver): Response
    {
        [$username, $role] = $this->currentUser();
        $items = $notif->recent($username, $role, 50);

        $userInfo = $connection->fetchAssociative(
            'SELECT id_photo FROM '.($role === 'vendeur' ? 'vendeur' : 'client').' WHERE username = :u',
            ['u' => $username]
        ) ?: [];

        $messageCount = (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM message WHERE receiver_username = :u AND is_read = 0',
            ['u' => $username]
        );

        return $this->render('notifications/index.html.twig', [
            'username' => $username,
            'role' => $role,
            'items' => $items,
            'photoUrl' => $imagePathResolver->profile($userInfo['id_photo'] ?? ''),
            'notifCount' => $notif->unreadCount($username, $role),
            'messageCount' => $messageCount,
        ]);
    }

    #[Route('/notifications/feed', name: 'app_notifications_feed', methods: ['GET'])]
    public function feed(NotificationService $notif, Connection $connection): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $items = $notif->recent($username, $role, 10);
        $messageCount = (int) $connection->fetchOne(
            'SELECT COUNT(*) FROM message WHERE receiver_username = :u AND is_read = 0',
            ['u' => $username]
        );

        return new JsonResponse([
            'unread_count' => $notif->unreadCount($username, $role),
            'message_count' => $messageCount,
            'items' => array_map(fn(array $n) => [
                'id' => (int) $n['id_notif'],
                'type' => $n['type'],
                'title' => $n['title'],
                'body' => $n['body'],
                'link' => $n['link'],
                'actor' => $n['actor_username'],
                'related_id' => (int) $n['related_id'],
                'is_read' => (bool) $n['is_read'],
                'created_at' => $n['created_at'],
            ], $items),
        ]);
    }

    #[Route('/notifications/{id}/read', name: 'app_notifications_mark_read', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function markRead(int $id, NotificationService $notif): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $notif->markRead($id, $username, $role);
        return new JsonResponse(['unread_count' => $notif->unreadCount($username, $role)]);
    }

    #[Route('/notifications/read-all', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllRead(NotificationService $notif): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $notif->markAllRead($username, $role);
        return new JsonResponse(['unread_count' => 0]);
    }
}
