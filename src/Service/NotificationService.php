<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

final class NotificationService
{
    public function __construct(private readonly Connection $connection)
    {
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
        $this->connection->insert('notification', [
            'recipient_username' => $recipientUsername,
            'recipient_role' => $recipientRole,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'link' => $link,
            'actor_username' => $actorUsername,
            'related_id' => $relatedId,
            'is_read' => 0,
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
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
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM notification WHERE recipient_username = :u AND recipient_role = :r AND is_read = 0',
            ['u' => $username, 'r' => $role]
        );
    }

    public function recent(string $username, string $role, int $limit = 20): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT id_notif, type, title, body, link, actor_username, related_id, is_read, created_at
             FROM notification
             WHERE recipient_username = :u AND recipient_role = :r
             ORDER BY id_notif DESC
             LIMIT '.$limit,
            ['u' => $username, 'r' => $role]
        );
    }

    public function markRead(int $id, string $username, string $role): void
    {
        $this->connection->executeStatement(
            'UPDATE notification SET is_read = 1 WHERE id_notif = :id AND recipient_username = :u AND recipient_role = :r',
            ['id' => $id, 'u' => $username, 'r' => $role]
        );
    }

    public function markAllRead(string $username, string $role): void
    {
        $this->connection->executeStatement(
            'UPDATE notification SET is_read = 1 WHERE recipient_username = :u AND recipient_role = :r AND is_read = 0',
            ['u' => $username, 'r' => $role]
        );
    }
}
