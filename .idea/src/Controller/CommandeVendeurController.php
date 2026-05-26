<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Repository\VendeurRepository;
// LegacyUser représente l'utilisateur connecté dans ton système de sécurité Symfony
use App\Security\LegacyUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeVendeurController extends AbstractController
{
    /*
     * Cette route affiche la page des commandes du vendeur.
     *
     * URL :
     * /vendeur/commandes
     *
     * Nom de la route :
     * app_vendeur_commandes
     *
     * Cette méthode sert à afficher la liste des commandes du vendeur connecté.
     */
    #[Route('/vendeur/commandes', name: 'app_vendeur_commandes')]
    public function index(
        Request $request,
        CommandeRepository $commandeRepository,
        VendeurRepository $vendeurRepository
    ): Response {
        /*
         * On vérifie que l'utilisateur connecté a bien le rôle ROLE_VENDEUR.
         *
         * Si l'utilisateur n'est pas vendeur, Symfony bloque l'accès automatiquement.
         */
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        /*
         * On récupère l'utilisateur connecté avec getUser().
         *
         * Dans ton projet Symfony, on n'utilise pas $_SESSION comme dans PHP classique.
         * On utilise getUser() parce que l'utilisateur est géré par le système de sécurité Symfony.
         */
        $user = $this->getUser();

        /*
         * On vérifie que l'utilisateur est bien un LegacyUser.
         *
         * Si oui, on récupère son username.
         * Sinon, on met une chaîne vide.
         */
        $username = $user instanceof LegacyUser ? $user->getUsername() : '';

        /*
         * Maintenant qu'on a le username du vendeur connecté,
         * on cherche le vendeur correspondant dans la table vendeur.
         *
         * Exemple :
         * username = "eyaabbes"
         *
         * Symfony cherche :
         * SELECT * FROM vendeur WHERE username = "eyaabbes"
         */
        $vendeur = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        /*
         * Si aucun vendeur n'est trouvé dans la base,
         * on affiche une erreur 404.
         *
         * Cela évite d'afficher une page vide ou de provoquer une erreur plus loin.
         */
        if (!$vendeur) {
            throw $this->createNotFoundException('Vendeur introuvable');
        }

        /*
         * On récupère le filtre depuis l'URL.
         *
         * Exemple :
         * /vendeur/commandes?type=panier
         *
         * Si aucun type n'est envoyé, on utilise "all" par défaut.
         */
        $type = $request->query->get('type', 'all');

        /*
         * On prépare une requête Doctrine avec QueryBuilder.
         *
         * Le QueryBuilder permet d'écrire une requête plus proprement
         * sans écrire directement du SQL.
         *
         * Ici, on récupère les commandes du vendeur connecté.
         */
        $qb = $commandeRepository->createQueryBuilder('c')
            /*
             * c représente la commande.
             *
             * On veut seulement les commandes où le vendeur est le vendeur connecté.
             */
            ->where('c.vendeur = :vendeur')

            /*
             * On donne la valeur du paramètre :vendeur.
             */
            ->setParameter('vendeur', $vendeur)

            /*
             * On trie les commandes par id décroissant.
             *
             * Donc les commandes les plus récentes apparaissent en premier.
             */
            ->orderBy('c.id', 'DESC');

        /*
         * Si le vendeur clique sur le filtre "Depuis panier",
         * on ajoute une condition :
         *
         * source = panier
         */
        if ($type === 'panier') {
            $qb->andWhere('c.source = :source')
                ->setParameter('source', 'panier');
        }

        /*
         * Si le vendeur clique sur le filtre "Après demande",
         * on ajoute une condition :
         *
         * source = demande
         */
        if ($type === 'demande') {
            $qb->andWhere('c.source = :source')
                ->setParameter('source', 'demande');
        }

        /*
         * Ici, on exécute la requête.
         *
         * Résultat :
         * $commandes contient la liste des commandes trouvées.
         */
        $commandes = $qb->getQuery()->getResult();

        /*
         * On envoie les données vers le fichier Twig.
         *
         * Le fichier Twig va utiliser :
         * - commandes : pour afficher la liste
         * - type : pour savoir quel filtre est actif
         * - username : pour afficher le nom du vendeur si besoin
         */
        return $this->render('commande_vendeur/index.html.twig', [
            'commandes' => $commandes,
            'type' => $type,
            'username' => $username,
        ]);
    }

    /*
     * Cette route sert à modifier le statut d'une commande.
     *
     * URL :
     * /vendeur/commande/{id}/statut
     *
     * Exemple :
     * /vendeur/commande/5/statut
     *
     * Cette route accepte seulement la méthode POST,
     * parce qu'on modifie des données dans la base.
     */
    #[Route('/vendeur/commande/{id}/statut', name: 'app_vendeur_commande_statut', methods: ['POST'])]
    public function updateStatut(
        Commande $commande,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         * On vérifie encore que l'utilisateur est un vendeur.
         *
         * Même si la page est protégée, il faut aussi protéger l'action de modification.
         */
        $this->denyAccessUnlessGranted('ROLE_VENDEUR');

        /*
         * On récupère l'utilisateur connecté.
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
         * Sinon, un vendeur pourrait modifier une commande d'un autre vendeur
         * en changeant simplement l'id dans l'URL.
         */
        if ($commande->getVendeur()?->getUsername() !== $username) {
            throw $this->createAccessDeniedException('Cette commande ne vous appartient pas.');
        }

        /*
         * On récupère le statut envoyé par le formulaire.
         *
         * Dans le Twig, le select s'appelle :
         * name="statut"
         *
         * Donc ici on récupère :
         * $_POST['statut'] en version Symfony.
         */
        $statut = $request->request->get('statut');



        /*
         * On modifie le statut de la commande.
         *
         * Exemple :
         * commande.statut = termine
         */
        $commande->setStatut($statut);

        /*
         * Certaines commandes sont liées à une demande.
         *
         * Dans ton ancienne base PHP, tu avais :
         * commandes.id_demande
         *
         * En Symfony, c'est une relation avec l'entité Demande.
         */
        $demande = $commande->getIdDemande();

        /*
         * Si la commande est liée à une demande,
         * on met aussi à jour l'état de cette demande.
         */
        if ($demande !== null) {
            /*
             * Si le vendeur met la commande comme terminée,
             * alors la demande devient reçue.
             */
            if ($statut === 'termine') {
                $demande->setEtat('recu');
            }

            /*
             * Si le vendeur annule la commande,
             * alors la demande devient annulée.
             */
            if ($statut === 'annule') {
                $demande->setEtat('annule');
            }
        }

        /*
         * flush() enregistre toutes les modifications dans la base.
         *
         * Ici, Symfony va faire automatiquement :
         * UPDATE commande SET statut = ...
         *
         * Et si une demande est liée :
         * UPDATE demande SET etat = ...
         */
        $entityManager->flush();

        /*
         * Après la modification, on retourne vers la page Mes commandes.
         *
         * On garde aussi le filtre actuel.
         *
         * Exemple :
         * Si on était dans ?type=panier,
         * on revient à ?type=panier.
         */
        return $this->redirectToRoute('app_vendeur_commandes', [
            'type' => $request->request->get('type', 'all'),
        ]);
    }
}