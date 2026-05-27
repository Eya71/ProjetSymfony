<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificationRepository $notifications,
        private readonly Connection $connection,
    ) {
    }

    public function create(
        string $recipientUsername,
        string $recipientRole,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null,
        ?string $actorUsername = null,
        ?int $relatedId = null
    ): void {
        $notification = (new Notification())
            ->setRecipientUsername($recipientUsername)
            ->setRecipientRole($recipientRole)
            ->setType($type)
            ->setTitle($title)
            ->setBody($body)
            ->setLink($link)
            ->setActorUsername($actorUsername)
            ->setRelatedId($relatedId)
            ->setIsRead(false)
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function notifyNewMessage(string $senderUsername, string $receiverUsername, string $receiverRole, int $idDeal, string $contenu): void
    {
        $preview = mb_strimwidth($contenu, 0, 80, '...');
        $this->create(
            $receiverUsername,
            $receiverRole,
            'new_message',
            'Nouveau message de '.$senderUsername,
            $preview,
            '/messagerie/'.$idDeal,
            $senderUsername,
            $idDeal
        );
    }

    public function notifyNewDemande(int $demandeId, string $clientUsername, string $nomProduit, string $prix): void
    {
        $vendeurs = $this->connection->fetchFirstColumn('SELECT username FROM vendeur');
        $title = 'Nouvelle demande de '.$clientUsername;
        $body = $clientUsername.' demande "'.$nomProduit.'" (budget '.$prix.' DT).';
        $link = '/demande/'.$demandeId;
        foreach ($vendeurs as $vendeurUsername) {
            $this->create(
                $vendeurUsername,
                'vendeur',
                'new_demande',
                $title,
                $body,
                $link,
                $clientUsername,
                $demandeId
            );
        }
    }

    public function notifyNewProduit(int $produitId, string $vendeurUsername, string $nomProduit, string $prix): void
    {
        $clients = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT sender_username FROM message WHERE receiver_username = :v
             UNION
             SELECT DISTINCT receiver_username FROM message WHERE sender_username = :v',
            ['v' => $vendeurUsername]
        );

        if (!$clients) {
            return;
        }

        $clientUsernames = $this->connection->fetchFirstColumn(
            'SELECT username FROM client WHERE username IN (:list)',
            ['list' => $clients],
            ['list' => \Doctrine\DBAL\ArrayParameterType::STRING]
        );

        $title = 'Nouveau produit de '.$vendeurUsername;
        $body = $vendeurUsername.' a ajouté "'.$nomProduit.'" ('.$prix.' DT).';
        $link = '/produit/'.$produitId;
        foreach ($clientUsernames as $clientUsername) {
            $this->create(
                $clientUsername,
                'client',
                'new_product',
                $title,
                $body,
                $link,
                $vendeurUsername,
                $produitId
            );
        }
    }

    public function unreadCount(string $username, string $role): int
    {
        return $this->notifications->countUnreadForRecipient($username, $role);
    }

    public function recent(string $username, string $role, int $limit = 20): array
    {
        $entities = $this->notifications->findFeedForRecipient($username, $role, $limit);

        return array_map(static fn(Notification $n): array => [
            'id_notif' => $n->getId(),
            'type' => $n->getType(),
            'title' => $n->getTitle(),
            'body' => $n->getBody(),
            'link' => $n->getLink(),
            'actor_username' => $n->getActorUsername(),
            'related_id' => $n->getRelatedId(),
            'is_read' => $n->isRead() ? 1 : 0,
            'created_at' => $n->getCreatedAt()?->format('Y-m-d H:i:s'),
        ], $entities);
    }

    public function markRead(int $id, string $username, string $role): void
    {
        $this->notifications->markOneReadFor($id, $username, $role);
    }

    public function markAllRead(string $username, string $role): void
    {
        $this->notifications->markAllReadFor($username, $role);
    }
}
