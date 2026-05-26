<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\VendeurRepository;
use App\Security\LegacyUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeVendeurController extends AbstractController
{
    #[Route('/vendeur/commandes', name: 'app_vendeur_commandes')]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        VendeurRepository $vendeurRepository
    ): Response {
        /*
         * On vérifie que l'utilisateur connecté est bien un vendeur.
         *
         * Si l'utilisateur n'a pas ROLE_VENDEUR,
         * Symfony bloque l'accès à cette page.
         */
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        /*
         * On récupère l'utilisateur connecté avec getUser().
         *
         * Dans ton projet Symfony, l'utilisateur connecté est un LegacyUser.
         * On ne travaille pas avec $_SESSION directement.
         */
        $user = $this->getUser();

        /*
         * On récupère le username de l'utilisateur connecté.
         *
         * Exemple :
         * si le vendeur connecté est "mohamedabbes",
         * alors $username = "mohamedabbes".
         */
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';

        /*
         * On cherche le vendeur dans la table vendeur
         * à partir du username récupéré depuis getUser().
         */
        $vendeur = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        /*
         * Si aucun vendeur n'est trouvé,
         * on affiche une erreur 404.
         *
         * Cela évite d'afficher des commandes sans vendeur valide.
         */
        if (!$vendeur) {
            throw $this->createNotFoundException('Vendeur introuvable.');
        }

        /*
         * type = filtre par origine de commande :
         *
         * all     => toutes les commandes
         * panier  => commandes depuis panier
         * demande => commandes après demande / deal
         */
        $type = $request->query->get('type', 'all');

        /*
         * etat = filtre par statut :
         *
         * all       => tous les statuts
         * en_cours  => commandes non terminées et non annulées
         * termine   => commandes terminées
         * annule    => commandes annulées
         */
        $etat = $request->query->get('etat', 'all');

        /*
         * On prépare la requête Doctrine.
         *
         * c représente une commande.
         *
         * Important :
         * on affiche seulement les commandes du vendeur connecté.
         */
        $qb = $commandeRepository->createQueryBuilder('c')
            ->where('c.vendeur = :vendeur')
            ->setParameter('vendeur', $vendeur)
            ->orderBy('c.id', 'DESC');

        /*
         * ==========================
         * FILTRE PAR ORIGINE
         * ==========================
         */

        /*
         * Si le vendeur clique sur "Depuis panier",
         * on affiche seulement les commandes dont source = panier.
         */
        if ($type === 'panier') {
            $qb->andWhere('c.source = :sourcePanier')
                ->setParameter('sourcePanier', 'panier');
        }

        /*
         * Si le vendeur clique sur "Après demande",
         * on affiche les commandes qui viennent d'une demande.
         *
         * Dans ta base, une commande après demande peut avoir :
         * - source = demande
         * - source = deal
         * - ou bien une relation id_demande non vide
         *
         * Donc on accepte ces cas pour que le filtre marche.
         */
        if ($type === 'demande') {
            $qb->andWhere(
                $qb->expr()->orX(
                    'c.source IN (:sourcesDemande)',
                    'c.id_demande IS NOT NULL'
                )
            )
                ->setParameter('sourcesDemande', [
                    'demande',
                    'deal',
                ]);
        }

        /*
         * ==========================
         * FILTRE PAR STATUT
         * ==========================
         */

        /*
         * Si le vendeur clique sur "En cours",
         * on affiche seulement les commandes non terminées et non annulées.
         *
         * Donc on exclut :
         * - termine
         * - terminé
         * - annule
         * - annulé
         */
        if ($etat === 'en_cours') {
            $qb->andWhere('c.statut NOT IN (:statutsFinis)')
                ->setParameter('statutsFinis', [
                    'termine',
                    'terminé',
                    'annule',
                    'annulé',
                ]);
        }

        /*
         * Si le vendeur clique sur "Terminées",
         * on affiche seulement les commandes terminées.
         */
        if ($etat === 'termine') {
            $qb->andWhere('c.statut IN (:statutsTermines)')
                ->setParameter('statutsTermines', [
                    'termine',
                    'terminé',
                ]);
        }

        /*
         * Si le vendeur clique sur "Annulées",
         * on affiche seulement les commandes annulées.
         */
        if ($etat === 'annule') {
            $qb->andWhere('c.statut IN (:statutsAnnules)')
                ->setParameter('statutsAnnules', [
                    'annule',
                    'annulé',
                ]);
        }

        /*
         * On exécute la requête.
         *
         * $commandes contient les commandes du vendeur connecté,
         * avec les filtres appliqués.
         */
        $commandes = $qb->getQuery()->getResult();

        /*
         * On envoie les données vers Twig.
         *
         * Twig va utiliser :
         * - commandes : pour afficher les cartes
         * - type : pour garder le filtre origine actif
         * - etat : pour garder le filtre statut actif
         * - username : pour afficher le nom du vendeur si besoin
         */
        return $this->render('commande_vendeur/index.html.twig', [
            'commandes' => $commandes,
            'type' => $type,
            'etat' => $etat,
            'username' => $username,
        ]);
    }

    #[Route('/vendeur/commande/{id}/statut', name: 'app_vendeur_commande_statut', methods: ['POST'])]
    public function updateStatut(
        Commande $commande,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         * On vérifie que l'utilisateur connecté est bien vendeur.
         */
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        /*
         * On récupère l'utilisateur connecté avec getUser().
         */
        $user = $this->getUser();

        /*
         * On récupère son username.
         */
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';

        /*
         * Sécurité importante :
         *
         * On vérifie que la commande que le vendeur veut modifier
         * appartient vraiment à ce vendeur.
         *
         * Sinon, un vendeur pourrait modifier la commande d'un autre vendeur
         * en changeant simplement l'id dans l'URL.
         */
        if ($commande->getVendeur()?->getUsername() !== $username) {
            throw $this->createAccessDeniedException('Cette commande ne vous appartient pas.');
        }

        /*
         * On récupère le statut envoyé par le formulaire Twig.
         *
         * Le champ select dans Twig est :
         * name="statut"
         */
        $statut = $request->request->get('statut');

        /*
         * Le vendeur peut seulement choisir :
         * - termine
         * - annule
         *
         * Si quelqu'un essaie d'envoyer un autre statut,
         * on bloque l'action.
         */
        if (!in_array($statut, ['termine', 'annule'], true)) {
            throw $this->createAccessDeniedException('Statut non autorisé.');
        }

        /*
         * On modifie le statut de la commande.
         *
         * Exemple :
         * statut = termine
         * ou
         * statut = annule
         */
        $commande->setStatut($statut);

        /*
         * On vérifie si cette commande est liée à une demande.
         *
         * Dans ton entité Commande, la relation avec Demande est :
         * getIdDemande()
         */
        $demande = $commande->getIdDemande();

        /*
         * Si la commande est liée à une demande,
         * on met aussi à jour l'état de la demande.
         */
        if ($demande !== null) {
            /*
             * Si la commande est terminée,
             * alors la demande devient reçue.
             */
            if ($statut === 'termine') {
                $demande->setEtat('recu');
            }

            /*
             * Si la commande est annulée,
             * alors la demande devient annulée.
             */
            if ($statut === 'annule') {
                $demande->setEtat('annule');
            }
        }

        /*
         * On enregistre les modifications dans la base.
         *
         * Symfony va faire automatiquement :
         * UPDATE commande SET statut = ...
         *
         * Et si une demande est liée :
         * UPDATE demande SET etat = ...
         */
        $entityManager->flush();

        /*
         * Après modification, on revient vers la page des commandes.
         *
         * On garde les filtres actuels :
         * - type : all / panier / demande
         * - etat : all / en_cours / termine / annule
         */
        return $this->redirectToRoute('app_vendeur_commandes', [
            'type' => $request->request->get('type', 'all'),
            'etat' => $request->request->get('etat', 'all'),
        ]);
    }
}