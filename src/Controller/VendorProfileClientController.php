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
         * 1. SESSION TEMPORAIRE POUR TESTER SANS LOGIN
         *
         * Interface concernée :
         * Cela ne s'affiche pas directement dans la page.
         * Ça sert seulement à dire que le client connecté est "amira".
         *
         * Quand ton camarade termine le login,
         * tu enlèves ce bloc et tu remets la vraie session.
         */
        $userSession = [
            'username' => 'amira',
            'role' => 'client',
        ];

        /*
         * 2. VÉRIFIER QUE L'UTILISATEUR EST UN CLIENT
         *
         * Interface concernée :
         * Si le rôle n'est pas "client", la page ne s'ouvre pas.
         */
        $role = $userSession['role'] ?? null;

        if ($role !== 'client') {
            throw $this->createAccessDeniedException('Cette page est réservée aux clients.');
        }

        /*
         * 3. RÉCUPÉRER LE CLIENT CONNECTÉ
         *
         * Interface concernée :
         * Ce client est utilisé pour :
         * - savoir s'il peut laisser un avis
         * - enregistrer son nom dans l'avis
         * - afficher son username dans la liste des avis après publication
         */
        $client = $clientRepository->findOneBy([
            'username' => $userSession['username'],
        ]);

        if (!$client) {
            throw $this->createNotFoundException('Client introuvable.');
        }

        /*
         * 4. RÉCUPÉRER LE VENDEUR DEPUIS L'URL
         *
         * Exemple d'URL :
         * /profil/vendeur/mohamedabbes
         *
         * Interface concernée :
         * C'est ce vendeur qui est affiché dans la grande carte en haut :
         * - photo
         * - username
         * - email
         * - adresse
         * - téléphone
         * - date d'inscription
         */
        $vendor = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$vendor) {
            throw $this->createNotFoundException('Vendeur introuvable.');
        }

        /*
         * 5. RÉCUPÉRER LES AVIS DU VENDEUR
         *
         * Interface concernée :
         * Section "Avis clients".
         *
         * On récupère tous les avis liés à ce vendeur,
         * du plus récent au plus ancien.
         */
        $reviews = $reviewRepository->findBy(
            ['vendeur_username' => $vendor],
            ['created_at' => 'DESC']
        );

        /*
         * 6. CALCULER LA NOTE MOYENNE
         *
         * Interface concernée :
         * Carte "Note moyenne" en haut à droite.
         *
         * Exemple :
         * si le vendeur a 2 avis : 5 et 3
         * moyenne = 4/5
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
         * 7. STATISTIQUES DU VENDEUR
         *
         * Interface concernée :
         * Les 4 petites cartes sous le profil :
         * - Commandes reçues
         * - Commandes terminées
         * - Deals acceptés
         * - Produits publiés
         */

        /*
         * Carte 1 : Commandes reçues
         * On compte toutes les commandes liées à ce vendeur.
         */
        $commandesRecues = $commandeRepository->count([
            'vendeur' => $vendor,
        ]);

        /*
         * Carte 2 : Commandes terminées
         * On compte seulement les commandes de ce vendeur
         * dont le statut est "terminée".
         *
         * Important :
         * Si dans ta base tu écris "terminee", "Terminée" ou "livrée",
         * il faut changer la valeur ici.
         */
        $commandesTerminees = $commandeRepository->count([
            'vendeur' => $vendor,
            'statut' => 'terminée',
        ]);

        /*
         * Carte 3 : Deals acceptés
         * Maintenant que tu as ajouté un champ status dans DealRequest,
         * on compte seulement les deals acceptés.
         *
         * Important :
         * La valeur "accepté" doit être exactement la même que dans ta base.
         * Si tu utilises "accepted", remplace "accepté" par "accepted".
         */
        $dealsAcceptes = $dealRequestRepository->count([
            'vendeur_username' => $vendor,
            'status' => 'accepté',
        ]);

        /*
         * Carte 4 : Produits publiés
         * On compte tous les produits liés à ce vendeur.
         */
        $produitsPublies = $produitRepository->count([
            'vendeur_username' => $vendor,
        ]);

        /*
         * 8. CHERCHER LES DEALS QUE LE CLIENT PEUT NOTER
         *
         * Interface concernée :
         * Formulaire "Laisser un nouvel avis".
         *
         * Le client peut laisser un avis seulement si :
         * - il a un deal avec ce vendeur
         * - le deal est accepté
         * - le deal n'a pas encore d'avis
         */
        $deals = $dealRequestRepository->findBy([
            'client_username' => $client,
            'vendeur_username' => $vendor,
            'status' => 'accepté',
        ]);

        $reviewableDeals = [];

        foreach ($deals as $deal) {
            /*
             * Si getReview() retourne null,
             * ça veut dire que ce deal n'a pas encore été noté.
             */
            if ($deal->getReview() === null) {
                $reviewableDeals[] = $deal;
            }
        }

        /*
         * 9. FORMULAIRE D'AVIS
         *
         * Interface concernée :
         * Section "Laisser un nouvel avis".
         *
         * Si $reviewableDeals contient au moins un deal,
         * on affiche le formulaire.
         *
         * Sinon, on affiche :
         * "Vous ne pouvez pas encore laisser un avis pour ce vendeur."
         */
        $review = new Review();
        $reviewForm = null;

        if (count($reviewableDeals) > 0) {
            /*
             * On crée le formulaire ReviewType.
             * On lui envoie seulement les deals que le client peut noter.
             */
            $form = $this->createForm(ReviewType::class, $review, [
                'reviewable_deals' => $reviewableDeals,
            ]);

            /*
             * handleRequest lit la requête.
             * Si le formulaire a été envoyé,
             * Symfony remplit automatiquement :
             * - id_deal
             * - rating
             * - commentaire
             */
            $form->handleRequest($request);

            /*
             * Si le formulaire est envoyé et valide,
             * on enregistre l'avis.
             */
            if ($form->isSubmitted() && $form->isValid()) {
                $selectedDeal = $review->getIdDeal();

                /*
                 * Sécurité :
                 * Même si quelqu'un modifie le HTML,
                 * on vérifie que le deal choisi est bien autorisé.
                 */
                if (!in_array($selectedDeal, $reviewableDeals, true)) {
                    $this->addFlash('error', 'Vous ne pouvez pas noter ce deal.');

                    return $this->redirectToRoute('app_vendor_profile_client', [
                        'username' => $vendor->getUsername(),
                    ]);
                }

                /*
                 * Ici on complète les champs que le client
                 * ne doit pas choisir lui-même.
                 *
                 * Interface concernée :
                 * Après l'enregistrement, l'avis apparaît dans "Avis clients".
                 */
                $review->setClientUsername($client);
                $review->setVendeurUsername($vendor);
                $review->setCreatedAt(new \DateTimeImmutable());

                /*
                 * persist prépare l'enregistrement.
                 * flush exécute l'INSERT dans la base.
                 */
                $entityManager->persist($review);
                $entityManager->flush();

                /*
                 * Message affiché en haut de la page.
                 */
                $this->addFlash('success', 'Votre avis a été ajouté avec succès.');

                return $this->redirectToRoute('app_vendor_profile_client', [
                    'username' => $vendor->getUsername(),
                ]);
            }

            /*
             * createView transforme le formulaire PHP
             * en formulaire utilisable dans Twig.
             */
            $reviewForm = $form->createView();
        }

        /*
         * 10. ENVOYER LES DONNÉES AU TWIG
         *
         * Interface concernée :
         * Chaque variable ici est utilisée dans index.html.twig.
         */
        return $this->render('vendor_profile_client/index.html.twig', [
            /*
             * Grande carte profil vendeur :
             * photo, username, email, adresse, téléphone, createdAt.
             */
            'vendor' => $vendor,

            /*
             * Client connecté.
             * Pas beaucoup affiché directement ici,
             * mais utile pour la logique de l'avis.
             */
            'client' => $client,

            /*
             * Section Avis clients.
             */
            'reviews' => $reviews,

            /*
             * Carte Note moyenne.
             */
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,

            /*
             * Les 4 cartes statistiques.
             */
            'commandesRecues' => $commandesRecues,
            'commandesTerminees' => $commandesTerminees,
            'dealsAcceptes' => $dealsAcceptes,
            'produitsPublies' => $produitsPublies,

            /*
             * Deals disponibles pour laisser un avis.
             */
            'reviewableDeals' => $reviewableDeals,

            /*
             * Formulaire d'avis.
             */
            'reviewForm' => $reviewForm,
        ]);
    }
}