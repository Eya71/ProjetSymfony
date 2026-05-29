-- ============================================================
-- Donnees de test pour la base importy
-- Comptes : client_test / vendeur_test  (mot de passe : Test1234)
-- ============================================================
USE importy;

-- Client de test
INSERT INTO client (username, email, adresse, num_tel, id_photo, pwd)
VALUES ('client_test', 'client@test.tn', 'Tunis', '20000000', 'files_profil/logo.png',
        '$2y$13$cI3MLNnGkCXeVhiNbRZA1uUzJijl/AT4I4s/pU9XI3jdh0IJEWy8S');
SET @client_id = LAST_INSERT_ID();

-- Vendeur de test
INSERT INTO vendeur (username, email, adresse, num_tel, id_photo, pwd, created_at)
VALUES ('vendeur_test', 'vendeur@test.tn', 'Sfax', '21000000', 'files_profil/logo.png',
        '$2y$13$cI3MLNnGkCXeVhiNbRZA1uUzJijl/AT4I4s/pU9XI3jdh0IJEWy8S', NOW());
SET @vendeur_id = LAST_INSERT_ID();

-- Demande publiee par le client
INSERT INTO demande (nom_produit, prix, lien_produit, description, categorie, id_photo, etat, username_id)
VALUES ('iPhone 13', '1500.00', '', 'Recherche un iPhone 13 neuf', 'homme', 'files_profil/logo.png',
        'en attente', @client_id);
SET @demande_id = LAST_INSERT_ID();

-- Offre (deal) du vendeur sur la demande
INSERT INTO deal_request (prix_propose, message, created_at, client_seen_at, vendeur_seen_at, status,
                          id_demande_id, client_username_id, vendeur_username_id)
VALUES ('1400.00', 'Bonjour, je vous propose 1400 DT.', NOW(), NOW(), NOW(), 'envoyee',
        @demande_id, @client_id, @vendeur_id);
SET @deal_id = LAST_INSERT_ID();

-- Quelques messages dans la conversation
INSERT INTO message (id_deal_id, sender_username, receiver_username, contenu, created_at, is_read) VALUES
 (@deal_id, 'vendeur_test', 'client_test', 'Bonjour, mon offre est de 1400 DT.', NOW(), 1),
 (@deal_id, 'client_test', 'vendeur_test', 'Merci ! Possible de baisser un peu le prix ?', NOW(), 0);

-- Notification non lue cote vendeur (nouveau message du client)
INSERT INTO notification (recipient_username, recipient_role, type, title, body, link, actor_username,
                          related_id, is_read, created_at)
VALUES ('vendeur_test', 'vendeur', 'new_message', 'Nouveau message de client_test',
        'Merci ! Possible de baisser un peu le prix ?', CONCAT('/messagerie/', @deal_id),
        'client_test', @deal_id, 0, NOW());
