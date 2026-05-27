<?php

namespace App\Controller;

use App\Repository\DealRequestRepository;
use App\Repository\VendeurRepository;
use App\Security\LegacyUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de la page "Mes offres envoyées".
 *
 * Cette page permet au vendeur connecté de voir toutes les offres
 * qu'il a envoyées aux clients.
 */
final class VendeurOffresController extends AbstractController
{
    /**
     * Route de la page "Mes offres envoyées".
     *
     * URL :
     * http://127.0.0.1:8000/vendeur/offres
     *
     * Nom de route :
     * app_vendeur_offres
     */
    #[Route('/vendeur/offres', name: 'app_vendeur_offres')]
    public function index(
        VendeurRepository $vendeurRepository,
        DealRequestRepository $dealRequestRepository
    ): Response {
        /*
         * Sécurité :
         *
         * Ici, on autorise seulement les utilisateurs avec le rôle ROLE_VENDEUR.
         * Si l'utilisateur n'est pas connecté ou n'est pas vendeur,
         * Symfony bloque l'accès.
         */
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        /*
        * On récupère l'utilisateur actuellement connecté.
        *
        * Dans ton projet, l'utilisateur connecté est représenté
        * par la classe LegacyUser.
        */
        $user = $this->getUser();

        /*
        * Vérification de sécurité supplémentaire.
        *
        * Si l'utilisateur connecté n'est pas un LegacyUser,
        * on bloque l'accès.
        */
        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException('Utilisateur non valide.');
        }

        /*
        * On récupère le username du vendeur connecté.
        *
        * Exemple :
        * si le vendeur connecté est "eyaabbes",
        * alors $username contient "eyaabbes".
        */
        $username = $user->getUsername();

        /*
        * On cherche le vendeur dans la table vendeur.
        *
        * Important :
        * dans ton entité DealRequest, vendeur_username est une relation
        * vers l'entité Vendeur.
        *
        * Donc il faut récupérer l'objet Vendeur complet,
        * pas seulement le texte du username.
        */
        $vendeur = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        /*
        * Si aucun vendeur n'est trouvé avec ce username,
        * on affiche une erreur 404.
        *
        * Cela veut dire que l'utilisateur est connecté,
        * mais qu'il n'existe pas dans la table vendeur.
        */
        if (!$vendeur) {
            throw $this->createNotFoundException('Vendeur introuvable.');
        }

        /*
        * On récupère toutes les offres envoyées par ce vendeur.
        *
        * Cette méthode doit être créée dans DealRequestRepository.
        */
        $offres = $dealRequestRepository->findOffersByVendeur($vendeur);

        /*
        * On envoie les données vers le fichier Twig.
        *
        * Dans Twig, on pourra utiliser :
        * - vendeur
        * - offres
        */
        return $this->render('vendeur_offres/index.html.twig', [
            'vendeur' => $vendeur,
            'offres' => $offres,
        ]);
    }
}