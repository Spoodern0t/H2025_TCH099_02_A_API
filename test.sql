INSERT INTO Evenement (id_evenement, id_calendrier, nom, description, couleur)
VALUES
    (1, 4, 'Réunion projet', 'Discussion sur l’avancement du projet', 'FF0000'),
    (2, 4, 'Anniversaire', 'Anniversaire de Sarah', '00FF00'),
    (3, 4, 'Présentation client', 'Présentation des résultats', '0000FF');

INSERT INTO Element (id_evenement, id_calendrier, id_element, nom, description, date_debut, date_fin)
VALUES
    (1, 4, 1, 'Préparation slides', 'Faire les slides de la réunion', '2025-04-10', '2025-04-10'),
    (1, 4, 2, 'Réserver salle', 'Réservation de la salle A', '2025-04-09', '2025-04-09'),
    (2, 4, 3, 'Acheter gâteau', 'Commander le gâteau au chocolat', '2025-04-12', '2025-04-12'),
    (3, 4, 4, 'Répétition', 'Préparer la présentation client', '2025-04-14', '2025-04-14');

