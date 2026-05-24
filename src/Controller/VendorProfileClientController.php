<?php

namespace App\Controller;

use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\ClientRepository;
use App\Repository\CommandeRepository;
use App\Repository\DealRequestRepository;
use App\Repository\ProduitRepository;
use App\Repository\ReviewRepository;
use App\Repository\VendeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VendorProfileClientController extends AbstractController
{
    #[Route('/profil/vendeur/{username}', name: 'app_vendor_profile_client')]
    public function index(
        string $username,
        Request $request,
        ClientRepository $clientRepository,
        VendeurRepository $vendeurRepository,
        ReviewRepository $reviewRepository,
        DealRequestRepository $dealRequestRepository,
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         * 1. Vérifier la session
         */
        $userSession = [
            'username' => 'amira',
            'role' => 'client',
        ];

        /*
         * 2. Vérifier que l'utilisateur connecté est un client
         */
        $role = $userSession['role'] ?? null;

        if ($role !== 'client') {
            throw $this->createAccessDeniedException('Cette page est réservée aux clients.');
        }

        /*
         * 3. Récupérer le client connecté
         */
        $client = $clientRepository->findOneBy([
            'username' => $userSession['username'],
        ]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        /*
         * 4. Récupérer le vendeur depuis l'URL
         */
        $vendor = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$vendor) {
            throw $this->createNotFoundException('Vendeur introuvable.');
        }

        /*
         * 5. Récupérer les avis du vendeur
         */
        $reviews = $reviewRepository->findBy(
            ['vendeur_username' => $vendor],
            ['created_at' => 'DESC']
        );

        /*
         * 6. Calculer la note moyenne
         */
        $totalReviews = count($reviews);
        $averageRating = 0;

        if ($totalReviews > 0) {
            $sum = 0;

            foreach ($reviews as $reviewItem) {
                $sum += $reviewItem->getRating();
            }

            $averageRating = round($sum / $totalReviews, 1);
        }

        /*
         * 7. Statistiques du vendeur
         */
        $commandesRecues = $commandeRepository->count([
            'vendeur' => $vendor,
        ]);

        /*
         * Attention :
         * Change 'terminée' si dans ta base le statut est écrit autrement.
         * Exemple : 'Terminé', 'livrée', 'completed', etc.
         */
        $commandesTerminees = $commandeRepository->count([
            'vendeur' => $vendor,
            'statut' => 'terminée',
        ]);

        $produitsPublies = $produitRepository->count([
            'vendeur_username' => $vendor,
        ]);

        /*
         * Ton entité DealRequest n'a pas encore un champ statut.
         * Donc ici, on compte tous les deals du vendeur.
         * Si plus tard tu ajoutes un champ statut, on filtrera seulement les deals acceptés.
         */
        $dealsAcceptes = $dealRequestRepository->count([
            'vendeur_username' => $vendor,
        ]);

        /*
         * 8. Deals que le client peut encore noter
         */
        $deals = $dealRequestRepository->findBy([
            'client_username' => $client,
            'vendeur_username' => $vendor,
        ]);

        $reviewableDeals = [];

        foreach ($deals as $deal) {
            if ($deal->getReview() === null) {
                $reviewableDeals[] = $deal;
            }
        }

        /*
         * 9. Formulaire d'avis
         */
        $review = new Review();
        $reviewForm = null;

        if (count($reviewableDeals) > 0) {
            $form = $this->createForm(ReviewType::class, $review, [
                'reviewable_deals' => $reviewableDeals,
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $selectedDeal = $review->getIdDeal();

                /*
                 * Sécurité :
                 * On vérifie que le deal choisi fait bien partie
                 * des deals que ce client peut noter.
                 */
                if (!in_array($selectedDeal, $reviewableDeals, true)) {
                    $this->addFlash('error', 'Vous ne pouvez pas noter ce deal.');

                    return $this->redirectToRoute('app_vendor_profile_client', [
                        'username' => $vendor->getUsername(),
                    ]);
                }

                /*
                 * Compléter automatiquement les champs que le client
                 * ne doit pas remplir lui-même.
                 */
                $review->setClientUsername($client);
                $review->setVendeurUsername($vendor);
                $review->setCreatedAt(new \DateTimeImmutable());

                $entityManager->persist($review);
                $entityManager->flush();

                $this->addFlash('success', 'Votre avis a été ajouté avec succès.');

                return $this->redirectToRoute('app_vendor_profile_client', [
                    'username' => $vendor->getUsername(),
                ]);
            }

            $reviewForm = $form->createView();
        }

        /*
         * 10. Envoyer les données vers Twig
         */
        return $this->render('vendor_profile_client/index.html.twig', [
            'vendor' => $vendor,
            'client' => $client,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,
            'commandesRecues' => $commandesRecues,
            'commandesTerminees' => $commandesTerminees,
            'dealsAcceptes' => $dealsAcceptes,
            'produitsPublies' => $produitsPublies,
            'reviewableDeals' => $reviewableDeals,
            'reviewForm' => $reviewForm,
        ]);
    }
}