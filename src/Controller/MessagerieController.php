<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\DealRequest;
use App\Repository\ClientRepository;
use App\Repository\DealRequestRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\VendeurRepository;
use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MessagerieController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DealRequestRepository $dealRepo,
        private readonly MessageRepository $messageRepo,
        private readonly NotificationRepository $notificationRepo,
        private readonly ClientRepository $clientRepo,
        private readonly VendeurRepository $vendeurRepo,
        private readonly NotificationService $notif,
        private readonly LegacyImagePathResolver $imagePathResolver,
    ) {
    }

    private function currentUser(): array
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException();
        }

        return [$user->getUsername(), $user->getLegacyRole()];
    }

    #[Route('/messagerie', name: 'app_messagerie_index')]
    public function index(): Response
    {
        [$username, $role] = $this->currentUser();

        if ($role === 'vendeur') {
            $vendeur = $this->vendeurRepo->findOneBy(['username' => $username]);
            $deals = $vendeur ? $this->dealRepo->findBy(['vendeur_username' => $vendeur]) : [];
            $myPhoto = $vendeur?->getIdPhoto();
        } else {
            $client = $this->clientRepo->findOneBy(['username' => $username]);
            $deals = $client ? $this->dealRepo->findBy(['client_username' => $client]) : [];
            $myPhoto = $client?->getIdPhoto();
        }

        $threads = [];
        foreach ($deals as $deal) {
            $demande = $deal->getIdDemande();
            $last = $this->messageRepo->findLastByDeal($deal);

            $threads[] = [
                'id_deal' => $deal->getId(),
                'client_username' => $deal->getClientUsername()?->getUsername(),
                'vendeur_username' => $deal->getVendeurUsername()?->getUsername(),
                'status' => $deal->getStatus(),
                'prix_propose' => $deal->getPrixPropose(),
                'nom_produit' => $demande?->getNomProduit(),
                'id_photo' => $demande?->getIdPhoto(),
                'photo_url' => $this->imagePathResolver->product($demande?->getIdPhoto() ?? ''),
                'last_msg' => $last?->getContenu(),
                'last_msg_at' => $last?->getCreatedAt()?->format('Y-m-d H:i:s'),
                'unread' => $this->messageRepo->countUnreadForDealAndReceiver($deal, $username),
            ];
        }

        usort($threads, static function (array $a, array $b): int {
            return strcmp((string) $b['last_msg_at'], (string) $a['last_msg_at'])
                ?: ($b['id_deal'] <=> $a['id_deal']);
        });

        return $this->render('messagerie/index.html.twig', [
            'username' => $username,
            'role' => $role,
            'threads' => $threads,
            'photoUrl' => $this->imagePathResolver->profile($myPhoto ?? ''),
            'notifCount' => $this->notif->unreadCount($username, $role),
            'messageCount' => $this->messageRepo->countUnreadForReceiver($username),
        ]);
    }

    #[Route('/messagerie/{idDeal}', name: 'app_messagerie_thread', requirements: ['idDeal' => '\d+'])]
    public function thread(int $idDeal): Response
    {
        [$username, $role] = $this->currentUser();
        $deal = $this->loadDeal($idDeal, $username, $role);

        $clientUsername = $deal->getClientUsername()?->getUsername();
        $vendeurUsername = $deal->getVendeurUsername()?->getUsername();
        $demande = $deal->getIdDemande();

        $otherUsername = $role === 'client' ? $vendeurUsername : $clientUsername;
        $otherRole = $role === 'client' ? 'vendeur' : 'client';
        $otherPhoto = $otherRole === 'vendeur'
            ? $this->vendeurRepo->findOneBy(['username' => $otherUsername])?->getIdPhoto()
            : $this->clientRepo->findOneBy(['username' => $otherUsername])?->getIdPhoto();

        $myPhoto = $role === 'vendeur'
            ? $this->vendeurRepo->findOneBy(['username' => $username])?->getIdPhoto()
            : $this->clientRepo->findOneBy(['username' => $username])?->getIdPhoto();

        return $this->render('messagerie/thread.html.twig', [
            'username' => $username,
            'role' => $role,
            'deal' => [
                'id_deal' => $deal->getId(),
                'id_demande' => $demande?->getId(),
                'client_username' => $clientUsername,
                'vendeur_username' => $vendeurUsername,
                'status' => $deal->getStatus(),
                'prix_propose' => $deal->getPrixPropose(),
                'nom_produit' => $demande?->getNomProduit(),
                'id_photo' => $demande?->getIdPhoto(),
                'categorie' => $demande?->getCategorie(),
            ],
            'otherUsername' => $otherUsername,
            'otherPhoto' => $this->imagePathResolver->profile($otherPhoto ?? ''),
            'demandePhoto' => $this->imagePathResolver->product($demande?->getIdPhoto() ?? ''),
            'photoUrl' => $this->imagePathResolver->profile($myPhoto ?? ''),
            'notifCount' => $this->notif->unreadCount($username, $role),
            'messageCount' => $this->messageRepo->countUnreadForReceiver($username),
        ]);
    }

    #[Route('/messagerie/{idDeal}/messages', name: 'app_messagerie_fetch', requirements: ['idDeal' => '\d+'], methods: ['GET'])]
    public function fetchMessages(int $idDeal, Request $request): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $deal = $this->loadDeal($idDeal, $username, $role);

        $since = (int) $request->query->get('since', 0);
        $messages = $this->messageRepo->findNewerThan($deal, $since);

        $this->messageRepo->markReadForDealAndReceiver($deal, $username);
        $this->notificationRepo->markMessageNotificationsRead($username, $role, $idDeal);

        return new JsonResponse([
            'messages' => array_map(fn (Message $m) => $this->serializeMessage($m, $username), $messages),
        ]);
    }

    #[Route('/messagerie/{idDeal}/send', name: 'app_messagerie_send', requirements: ['idDeal' => '\d+'], methods: ['POST'])]
    public function send(int $idDeal, Request $request): JsonResponse
    {
        [$username, $role] = $this->currentUser();
        $deal = $this->loadDeal($idDeal, $username, $role);

        $payload = json_decode((string) $request->getContent(), true) ?: [];
        $contenu = trim((string) ($payload['contenu'] ?? $request->request->get('contenu', '')));
        if ($contenu === '') {
            return new JsonResponse(['error' => 'empty'], 400);
        }

        $receiver = $role === 'client'
            ? $deal->getVendeurUsername()?->getUsername()
            : $deal->getClientUsername()?->getUsername();
        $receiverRole = $role === 'client' ? 'vendeur' : 'client';

        $message = new Message();
        $message->setIdDeal($deal);
        $message->setSenderUsername($username);
        $message->setReceiverUsername((string) $receiver);
        $message->setContenu($contenu);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setIsRead(false);

        $this->em->persist($message);
        $this->em->flush();

        $this->notif->notifyNewMessage($username, (string) $receiver, $receiverRole, $idDeal, $contenu);

        return new JsonResponse(['message' => $this->serializeMessage($message, $username)]);
    }

    #[Route('/messagerie/start/{idDeal}', name: 'app_messagerie_start', requirements: ['idDeal' => '\d+'])]
    public function startFromDeal(int $idDeal): Response
    {
        return $this->redirectToRoute('app_messagerie_thread', ['idDeal' => $idDeal]);
    }

    private function loadDeal(int $idDeal, string $username, string $role): DealRequest
    {
        $deal = $this->dealRepo->find($idDeal);
        if ($deal === null) {
            throw $this->createNotFoundException('Conversation introuvable.');
        }

        $authorized = ($role === 'client' && $deal->getClientUsername()?->getUsername() === $username)
            || ($role === 'vendeur' && $deal->getVendeurUsername()?->getUsername() === $username);

        if (!$authorized) {
            throw $this->createAccessDeniedException();
        }

        return $deal;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMessage(Message $message, string $username): array
    {
        return [
            'id' => $message->getId(),
            'sender' => $message->getSenderUsername(),
            'receiver' => $message->getReceiverUsername(),
            'contenu' => $message->getContenu(),
            'created_at' => $message->getCreatedAt()?->format('Y-m-d H:i:s'),
            'is_read' => $message->isRead(),
            'mine' => $message->getSenderUsername() === $username,
        ];
    }
}
