<?php

namespace App\Controller;

use App\Security\LegacyUser;
use App\Service\LegacyImagePathResolver;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PanierController extends AbstractController
{
    #[Route('/panier', name: 'panier_index', methods: ['GET'])]
    public function index(Connection $connection, LegacyImagePathResolver $imagePathResolver, Request $request): Response
    {
        $user = $this->requireClientUser();
        if (!$user instanceof LegacyUser) {
            return $this->redirectToRoute('app_login');
        }

        $items = $this->fetchItems($connection, $imagePathResolver, $user->getUsername());
        $total = $this->calculateTotal($items);
        $ordersCount = $this->ordersCount($connection, $user->getUsername());

        return $this->render('panier/index.html.twig', [
            'items' => $items,
            'total' => $total,
            'ordersCount' => $ordersCount,
            'success' => $request->getSession()->getFlashBag()->get('panier_success'),
            'errors' => $request->getSession()->getFlashBag()->get('panier_error'),
        ]);
    }

    #[Route('/panier/add', name: 'panier_add', methods: ['POST'])]
    #[Route('/panier/add/{id}', name: 'panier_add_legacy', methods: ['GET', 'POST'])]
    public function add(?int $id, Request $request, Connection $connection): Response
    {
        $isAjax = $this->isAjax($request);
        $user = $this->requireClientUser();

        if (!$user instanceof LegacyUser) {
            return $this->panierResponse($request, $isAjax, false, 'Veuillez vous connecter.', $this->generateUrl('app_login'));
        }

        $idProduit = (int) ($request->request->get('id_produit') ?: $id);
        $redirectTo = $this->safeRedirect((string) $request->request->get('redirect_to', $this->generateUrl('panier_index')));

        if ($idProduit <= 0) {
            return $this->panierResponse($request, $isAjax, false, 'Produit invalide.', $redirectTo);
        }

        $product = $connection->fetchAssociative('SELECT quantite FROM produit WHERE id = :id_produit', [
            'id_produit' => $idProduit,
        ]);

        if (!$product || (int) $product['quantite'] < 1) {
            return $this->panierResponse($request, $isAjax, false, 'Produit en rupture de stock.', $redirectTo);
        }

        $existingItem = $connection->fetchAssociative(
            'SELECT id AS id_panier, quantite FROM panier WHERE username = :username AND id_produit = :id_produit',
            ['username' => $user->getUsername(), 'id_produit' => $idProduit]
        );

        if ($existingItem) {
            $newQuantite = (int) $existingItem['quantite'] + 1;
            if ($newQuantite > (int) $product['quantite']) {
                return $this->panierResponse($request, $isAjax, false, 'Quantite demandee superieure au stock disponible.', $redirectTo);
            }

            $connection->update('panier', ['quantite' => $newQuantite], ['id' => $existingItem['id_panier']]);
        } else {
            $connection->insert('panier', [
                'username' => $user->getUsername(),
                'id_produit' => $idProduit,
                'quantite' => 1,
                'date_ajout' => (new \DateTimeImmutable())->format('H:i:s'),
            ]);
        }

        return $this->panierResponse($request, $isAjax, true, 'Produit ajoute dans le panier.', $redirectTo);
    }

    #[Route('/panier/update', name: 'panier_update', methods: ['POST'])]
    #[Route('/panier/update/{id}', name: 'panier_update_legacy', methods: ['POST'])]
    public function update(?int $id, Request $request, Connection $connection): Response
    {
        $user = $this->requireClientUser();
        if (!$user instanceof LegacyUser) {
            return $this->redirectToRoute('app_login');
        }

        $action = (string) $request->request->get('action', 'update');
        $idPanier = (int) ($request->request->get('id_panier') ?: $id);
        $quantite = (int) $request->request->get('quantite', 1);

        if ($idPanier <= 0) {
            $this->addFlash('panier_error', 'Element de panier invalide.');
            return $this->redirectToRoute('panier_index');
        }

        if ($action === 'delete') {
            $connection->delete('panier', [
                'id' => $idPanier,
                'username' => $user->getUsername(),
            ]);

            $this->addFlash('panier_success', 'Produit supprime du panier.');
            return $this->redirectToRoute('panier_index');
        }

        if ($action !== 'update') {
            $this->addFlash('panier_error', 'Action panier invalide.');
            return $this->redirectToRoute('panier_index');
        }

        if ($quantite < 1) {
            $this->addFlash('panier_error', 'La quantite doit etre au moins 1.');
            return $this->redirectToRoute('panier_index');
        }

        $stockInfo = $connection->fetchAssociative(
            'SELECT pr.quantite AS stock FROM panier p INNER JOIN produit pr ON pr.id = p.id_produit WHERE p.id = :id_panier AND p.username = :username',
            ['id_panier' => $idPanier, 'username' => $user->getUsername()]
        );

        if (!$stockInfo) {
            $this->addFlash('panier_error', 'Element de panier introuvable.');
            return $this->redirectToRoute('panier_index');
        }

        if ($quantite > (int) $stockInfo['stock']) {
            $this->addFlash('panier_error', 'Quantite demandee superieure au stock disponible.');
            return $this->redirectToRoute('panier_index');
        }

        $connection->update('panier', ['quantite' => $quantite], [
            'id' => $idPanier,
            'username' => $user->getUsername(),
        ]);

        $this->addFlash('panier_success', 'Quantite mise a jour.');
        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/delete/{id}', name: 'panier_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, Connection $connection): RedirectResponse
    {
        $request->request->set('action', 'delete');
        $request->request->set('id_panier', (string) $id);

        return $this->update($id, $request, $connection);
    }

    #[Route('/panier/valider', name: 'panier_validate', methods: ['POST'])]
    public function validate(Connection $connection): RedirectResponse
    {
        $user = $this->requireClientUser();
        if (!$user instanceof LegacyUser) {
            return $this->redirectToRoute('app_login');
        }

        $username = $user->getUsername();
        $items = $connection->fetchAllAssociative(
            'SELECT p.id AS id_panier, p.quantite, pr.id AS id_produit, pr.nom_produit, pr.prix, pr.decription AS description, pr.categorie, pr.image_path, v.username AS vendeur_username, pr.quantite AS stock
             FROM panier p
             INNER JOIN produit pr ON pr.id = p.id_produit
             LEFT JOIN vendeur v ON v.id = pr.vendeur_username_id
             WHERE p.username = :username',
            ['username' => $username]
        );

        if (!$items) {
            $this->addFlash('panier_error', 'Votre panier est vide.');
            return $this->redirectToRoute('panier_index');
        }

        try {
            $connection->beginTransaction();
            $commandesParVendeur = [];

            foreach ($items as $item) {
                if ((int) $item['stock'] < (int) $item['quantite']) {
                    throw new \RuntimeException('Le produit "'.($item['nom_produit'] ?? 'inconnu').'" n\'est plus disponible en quantite suffisante.');
                }

                $updated = $connection->executeStatement(
                    'UPDATE produit SET quantite = quantite - :quantite WHERE id = :id_produit AND quantite >= :quantite',
                    ['quantite' => (int) $item['quantite'], 'id_produit' => (int) $item['id_produit']]
                );

                if ($updated === 0) {
                    throw new \RuntimeException('Stock insuffisant pour "'.($item['nom_produit'] ?? 'inconnu').'".');
                }

                $vendeur = (string) ($item['vendeur_username'] ?? '');
                if ($vendeur === '') {
                    throw new \RuntimeException('Vendeur introuvable pour le produit "'.($item['nom_produit'] ?? 'inconnu').'".');
                }

                $commandesParVendeur[$vendeur] ??= ['vendeur' => $vendeur, 'total' => 0.0, 'items' => []];
                $sousTotal = ((float) $item['prix']) * ((int) $item['quantite']);
                $commandesParVendeur[$vendeur]['total'] += $sousTotal;
                $commandesParVendeur[$vendeur]['items'][] = [
                    'id_produit' => (int) $item['id_produit'],
                    'nom_produit' => (string) $item['nom_produit'],
                    'prix_unitaire' => (float) $item['prix'],
                    'quantite' => (int) $item['quantite'],
                    'sous_total' => $sousTotal,
                    'image_path' => (string) ($item['image_path'] ?? ''),
                    'categorie' => (string) ($item['categorie'] ?? 'tous'),
                ];
            }

            foreach ($commandesParVendeur as $commandeData) {
                $firstItem = $commandeData['items'][0];
                $lines = array_map(fn (array $orderItem): string => $orderItem['nom_produit'].' x'.$orderItem['quantite'], $commandeData['items']);
                $clientId = $connection->fetchOne('SELECT id FROM client WHERE username = :username', ['username' => $username]);
                $vendeurId = $connection->fetchOne('SELECT id FROM vendeur WHERE username = :username', ['username' => $commandeData['vendeur']]);

                if (!$clientId || !$vendeurId) {
                    throw new \RuntimeException('Client ou vendeur introuvable pour la commande panier.');
                }

                $connection->insert('demande', [
                    'nom_produit' => 'Commande panier - '.$commandeData['vendeur'],
                    'prix' => $commandeData['total'],
                    'lien_produit' => '',
                    'descrption' => 'Commande creee depuis le panier : '.implode(', ', $lines),
                    'categorie' => $firstItem['categorie'] !== '' ? $firstItem['categorie'] : 'tous',
                    'id_photo' => $firstItem['image_path'],
                    'username_id' => (int) $clientId,
                    'etat' => 'en attente',
                ]);

                $idDemande = (int) $connection->lastInsertId();
                $connection->insert('commande', [
                    'id_demande_id' => $idDemande,
                    'vendeur_id' => (int) $vendeurId,
                    'client_id' => (int) $clientId,
                    'statut' => 'en cours',
                    'source' => 'panier',
                    'total' => $commandeData['total'],
                ]);

                $idCommande = (int) $connection->lastInsertId();
                foreach ($commandeData['items'] as $orderItem) {
                    $connection->insert('commande_item', [
                        'id_commande_id' => $idCommande,
                        'id_produit_id' => $orderItem['id_produit'],
                        'prix_unitaire' => $orderItem['prix_unitaire'],
                        'quantite' => $orderItem['quantite'],
                        'sous_total' => $orderItem['sous_total'],
                        'image_path' => $orderItem['image_path'],
                    ]);
                }
            }

            $connection->delete('panier', ['username' => $username]);
            $connection->commit();
            $this->addFlash('panier_success', 'Panier valide et envoye au vendeur.');
        } catch (\Throwable $exception) {
            $connection->rollBack();
            $this->addFlash('panier_error', 'Erreur lors de la validation du panier : '.$exception->getMessage());
        }

        return $this->redirectToRoute('panier_index');
    }

    #[Route('/panier/fragment', name: 'panier_fragment', methods: ['GET'])]
    public function fragment(Connection $connection, LegacyImagePathResolver $imagePathResolver): Response
    {
        $user = $this->requireClientUser();
        if (!$user instanceof LegacyUser) {
            return new JsonResponse(['success' => false, 'redirect' => $this->generateUrl('app_login')], 401);
        }

        return $this->render('panier/_items.html.twig', [
            'items' => $this->fetchItems($connection, $imagePathResolver, $user->getUsername()),
        ]);
    }

    private function requireClientUser(): ?LegacyUser
    {
        $user = $this->getUser();
        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'client') {
            return null;
        }

        return $user;
    }

    private function fetchItems(Connection $connection, LegacyImagePathResolver $imagePathResolver, string $username): array
    {
        $items = $connection->fetchAllAssociative(
            'SELECT p.id AS id_panier, p.quantite, p.date_ajout, pr.id AS id_produit, pr.nom_produit, pr.prix, pr.decription AS description, pr.categorie, pr.image_path, pr.quantite AS stock
             FROM panier p
             INNER JOIN produit pr ON pr.id = p.id_produit
             WHERE p.username = :username
             ORDER BY p.date_ajout DESC',
            ['username' => $username]
        );

        foreach ($items as &$item) {
            $item['resolved_image'] = $imagePathResolver->product($item['image_path'] ?? '');
            $item['subtotal'] = ((float) $item['prix']) * ((int) $item['quantite']);
        }
        unset($item);

        return $items;
    }

    private function calculateTotal(array $items): float
    {
        return array_reduce($items, fn (float $total, array $item): float => $total + (float) $item['subtotal'], 0.0);
    }

    private function ordersCount(Connection $connection, string $username): int
    {
        try {
            return (int) $connection->fetchOne("SELECT COUNT(*) FROM commande co INNER JOIN client c ON c.id = co.client_id WHERE c.username = :client AND co.source = 'panier'", [
                'client' => $username,
            ]);
        } catch (\Throwable) {
            return 0;
        }
    }

    private function panierResponse(Request $request, bool $isAjax, bool $success, string $message, string $redirectTo): Response
    {
        if ($isAjax) {
            return new JsonResponse([
                'success' => $success,
                'message' => $message,
                'redirect' => $redirectTo,
            ]);
        }

        $this->addFlash($success ? 'panier_success' : 'panier_error', $message);
        return new RedirectResponse($redirectTo);
    }

    private function isAjax(Request $request): bool
    {
        return strtolower((string) $request->headers->get('X-Requested-With')) === 'xmlhttprequest'
            || str_contains(strtolower((string) $request->headers->get('Accept')), 'application/json');
    }

    private function safeRedirect(string $redirectTo): string
    {
        if ($redirectTo === '' || preg_match('/^https?:/i', $redirectTo)) {
            return $this->generateUrl('panier_index');
        }

        return $redirectTo;
    }
}
