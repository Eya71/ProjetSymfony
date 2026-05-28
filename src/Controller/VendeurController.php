<?php
namespace App\Controller;

use App\Entity\Vendeur;
use App\Entity\Produit;
use App\Entity\Demande;
use App\Repository\VendeurRepository;
use App\Repository\ProduitRepository;
use App\Repository\DemandeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use App\Security\LegacyUser;

final class VendeurController extends AbstractController
{
    #[Route('/vendeur/dashboard', name: 'vendeur_dashboard', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VENDEUR')]
    public function dashboard(
        Request $request,
        VendeurRepository $vendeurRepo,
        ProduitRepository $produitRepo,
        DemandeRepository $demandeRepo,
        EntityManagerInterface $em //enregistrer dans bd
    ): Response {
        /*
 * On récupère l'utilisateur connecté.
 * Dans ton système, c'est un LegacyUser.
 */
        $user = $this->getUser();

        if (!$user instanceof LegacyUser || $user->getLegacyRole() !== 'vendeur') {
            return $this->redirectToRoute('app_login');
        }

        $vendeur = $vendeurRepo->findOneBy([
            'username' => $user->getUsername(),
        ]);



        /*
         * Si aucun vendeur n'est trouvé, on bloque l'accès.
         */
        if (!$vendeur) {
            throw $this->createAccessDeniedException('Vendeur introuvable');
        }

        // Récupérer les produits du vendeur
        $produits = $produitRepo->findBy(
            ['vendeurUsername' => $vendeur],
            ['createdAt' => 'DESC']
        );

        // Récupérer les demandes non traitées
        $demandes = $demandeRepo->findAll();

        // Gestion POST - Créer un produit
        if ($request->isMethod('POST')) {
            $nomProduit = $request->request->get('nom_produit');
            $prix = $request->request->get('prix');
            $quantite = $request->request->get('quantite', 1);
            $categorie = $request->request->get('categorie');
            $description = $request->request->get('description');
            $image = $request->files->get('image');

            if ($nomProduit && $prix && $categorie) {
                $produit = new Produit();
                $produit->setNomProduit($nomProduit);
                $produit->setPrix((float)$prix);
                $produit->setQuantite((int)$quantite);
                $produit->setCategorie($categorie);
                $produit->setDescription($description ?? '');
                $produit->setVendeurUsername($vendeur);

                // Traiter l'image si envoyée
                if ($image && $image->isValid()) {
                    //creation d'un nom unique de l'image
                    $filename = bin2hex(random_bytes(16)) . '.' . $image->guessExtension();
                    $image->move($this->getParameter('kernel.project_dir') . '/public/files_produit', $filename);
                    $produit->setImagePath('/files_produit/' . $filename);
                }

                $em->persist($produit);
                $em->flush();

                $this->addFlash('success', 'Produit ajouté avec succès !');//!!!!!
                return $this->redirectToRoute('vendeur_dashboard');
            }
        }

        return $this->render('Vendeur/page_vendeur.html.twig', [
            'vendeur' => $vendeur,
            'produits' => $produits,
            'demandes' => $demandes,
        ]);
    }
    #[Route('/vendeur/produit/{id}/modifier', name: 'vendeur_edit_product', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VENDEUR')]
    public function editProduct(
        Produit $produit,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($produit->getVendeurUsername()?->getUsername() !== $user->getUsername()) {
            throw $this->createAccessDeniedException('Vous ne pouvez modifier que vos propres produits');
        }

        if ($request->isMethod('POST')) {
            $produit->setNomProduit($request->request->get('nom_produit'));
            $produit->setPrix((float) $request->request->get('prix'));
            $produit->setQuantite((int) $request->request->get('quantite'));
            $produit->setCategorie($request->request->get('categorie'));
            $produit->setDescription($request->request->get('description'));

            $image = $request->files->get('image');

            if ($image && $image->isValid()) {
                $filename = bin2hex(random_bytes(16)) . '.' . $image->guessExtension();

                $image->move(
                    $this->getParameter('kernel.project_dir') . '/public/files_produit',
                    $filename
                );

                $produit->setImagePath('/files_produit/' . $filename);
            }

            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès !');

            return $this->redirectToRoute('vendeur_dashboard');
        }

        return $this->render('Vendeur/edit_product.html.twig', [
            'produit' => $produit,
            'vendeur' => $user,
        ]);
    }

    #[Route('/vendeur/produit/{id}/supprimer', name: 'vendeur_delete_product', methods: ['POST'])]
    #[IsGranted('ROLE_VENDEUR')]
    public function deleteProduct(
        Produit $produit,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof LegacyUser) {
            throw $this->createAccessDeniedException('Accès refusé');
        }

        if ($produit->getVendeurUsername()?->getUsername() !== $user->getUsername()) {
            throw $this->createAccessDeniedException('Vous ne pouvez supprimer que vos propres produits');
        }

        // Supprimer aussi l'image du dossier public/files_produit
        $imagePath = $produit->getImagePath();

        if ($imagePath) {
            $fullPath = $this->getParameter('kernel.project_dir') . '/public' . $imagePath;

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        $em->remove($produit);
        $em->flush();

        $this->addFlash('success', 'Produit supprimé avec succès !');

        return $this->redirectToRoute('vendeur_dashboard');
    }

    #[Route('/vendeur/offre', name: 'vendeur_send_offer', methods: ['POST'])]
    #[IsGranted('ROLE_VENDEUR')]
    public function sendOffer(
        Request $request,
        DemandeRepository $demandeRepo,
        VendeurRepository $vendeurRepository,
        EntityManagerInterface $em
    ): Response {

        $user = $this->getUser();
        if (!$user instanceof LegacyUser) {
            return $this->redirectToRoute('app_login');
        }
        $username = $user->getUsername();
        $vendeur = $vendeurRepository->findOneBy([
            'username' => $username,
        ]);

        if (!$vendeur) {
            throw $this->createNotFoundException('Vendeur introuvable');
        }
        $idDemande = $request->request->get('id_demande');
        $prixPropose = $request->request->get('prix_propose');
        $message = $request->request->get('message');
        $demande = $demandeRepo->find($idDemande);
        if (!$demande) {
            throw $this->createNotFoundException('Demande non trouvée');
        }
        $offer = new DealRequest();
        $offer->setIdDemande($demande);
        $offer->setVendeurUsername($vendeur);
        $offer->setPrixPropose((string) $prixPropose);
        $offer->setMessage($message);
        $now = new \DateTimeImmutable();
        $offer->setCreatedAt($now);
        $offer->setClientSeenAt($now);
        $offer->setVendeurSeenAt($now);
        $offer->setStatus('envoyee');
        $offer->setClientUsername($client);
        $em->persist($offer);
        $em->flush();
        $this->addFlash('success', 'Offre envoyée avec succès !');
        return $this->redirectToRoute('vendeur_dashboard');
    }
}