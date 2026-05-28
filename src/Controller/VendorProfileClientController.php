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
use App\Security\LegacyUser;
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

        $this->denyAccessUnlessGranted('ROLE_CLIENT');

        /*
         * 2. Récupérer l'utilisateur connecté avec getUser()
         *
         * Ici, on ne travaille pas avec session.
         * Symfony récupère l'utilisateur connecté automatiquement.
         */
        $user = $this->getUser();

        if (!$user instanceof LegacyUser) {
            return $this->redirectToRoute('app_home');
        }

        /*
         * 3. Récupérer le username du client connecté
         *
         * Exemple :
         * si le client connecté est amira,
         * $clientUsername = "amira"
         */
        $clientUsername = $user->getUsername();

        /*
         * 4. Récupérer le client depuis la base
         *
         * Interface :
         * Ce client sert à savoir :
         * - s'il peut laisser un avis
         * - quel nom afficher dans l'avis
         */
        $client = $clientRepository->findOneBy([
            'username' => $clientUsername,
        ]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        /*
         * 5. Récupérer le vendeur depuis l'URL
         *
         * Exemple URL :
         * /profil/vendeur/mohamedabbes
         *
         * Ici, $username = "mohamedabbes"
         *
         * Interface :
         * Ce vendeur est affiché dans la grande carte :
         * - photo
         * - username
         * - email
         * - adresse
         * - téléphone
         * - date de création
         */
        $vendor = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$vendor) {
            throw $this->createNotFoundException('Vendeur introuvable.');
        }

        /*
         * 6. Récupérer les avis du vendeur
         *
         * Interface :
         * Section "Avis clients".
         */
        $reviews = $reviewRepository->findBy(
            ['vendeur_username' => $vendor],
            ['created_at' => 'DESC']
        );

        /*
         * 7. Calculer la note moyenne
         *
         * Interface :
         * Bloc "Note moyenne" à droite dans la carte profil.
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
         * 8. Statistiques du vendeur
         *
         * Interface :
         * Les 4 cartes sous le profil vendeur.
         */

        // Carte 1 : Commandes reçues
        $commandesRecues = $commandeRepository->count([
            'vendeur' => $vendor,
        ]);

        // Carte 2 : Commandes terminées
        $commandesTerminees = $commandeRepository->count([
            'vendeur' => $vendor,
            'statut' => 'termine',
        ]);

        // Carte 3 : Deals acceptés
        $dealsAcceptes = $dealRequestRepository->count([
            'vendeur_username' => $vendor,
            'status' => 'accepted',
        ]);

        // Carte 4 : Produits publiés
        $produitsPublies = $produitRepository->count([
            'vendeurUsername' => $vendor,
        ]);

        /*
         * 9. Chercher les deals que ce client peut noter
         *
         * Interface :
         * Formulaire "Laisser un nouvel avis".
         *
         * Le client peut noter seulement si :
         * - le deal appartient à ce client
         * - le deal appartient à ce vendeur
         * - le deal est accepté
         * - le deal n'a pas encore de review
         */
        $deals = $dealRequestRepository->findBy([
            'client_username' => $client,
            'vendeur_username' => $vendor,
            'status' => 'accepted',
        ]);

        $reviewableDeals = [];

        foreach ($deals as $deal) {
            if ($deal->getReview() === null) {
                $reviewableDeals[] = $deal;
            }
        }

        /*
         * 10. Formulaire d'avis
         *
         * Interface :
         * Section "Laisser un nouvel avis".
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
                 * On vérifie que le deal choisi est bien dans les deals autorisés.
                 */
                if (!in_array($selectedDeal, $reviewableDeals, true)) {
                    $this->addFlash('error', 'Vous ne pouvez pas noter ce deal.');

                    return $this->redirectToRoute('app_vendor_profile_client', [
                        'username' => $vendor->getUsername(),
                    ]);
                }

                /*
                 * On complète les champs que le client ne choisit pas lui-même.
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
         * 11. Envoyer les données au Twig
         *
         * Interface :
         * Toutes ces variables sont utilisées dans index.html.twig.
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