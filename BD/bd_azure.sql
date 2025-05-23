-- Nouveau fichier des création des tables sur azure. SQL azure à
-- quelque particularité qui faut changer.

-- Création de la table Utilisateur
CREATE TABLE Utilisateur (
    id_utilisateur INT IDENTITY(1,1) PRIMARY KEY,
    courriel       NVARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe   NVARCHAR(255) NOT NULL,
    nom            NVARCHAR(255),
    est_valide     BIT DEFAULT 0,

    created_at     DATETIME2 DEFAULT GETDATE(),
    updated_at     DATETIME2 DEFAULT GETDATE()
);

GO

-- Créer un trigger pour mettre à jour automatiquement updated_at
CREATE TRIGGER trg_UpdateUtilisateur
ON Utilisateur
AFTER UPDATE
AS
BEGIN
    UPDATE Utilisateur
    SET updated_at = GETDATE()
    FROM Utilisateur
    INNER JOIN inserted ON Utilisateur.id_utilisateur = inserted.id_utilisateur;
END;

-- Création de la table Calendrier
CREATE TABLE Calendrier (
    id_calendrier INT IDENTITY(1,1) PRIMARY KEY,
    nom           VARCHAR(255) NOT NULL,
    description   VARCHAR(255),
    auteur_id     INT NOT NULL,

    created_at    DATETIME2 DEFAULT GETDATE(),
    updated_at    DATETIME2 DEFAULT GETDATE(),

    CONSTRAINT fk_calendrier_auteur FOREIGN KEY (auteur_id) REFERENCES Utilisateur(id_utilisateur)
);

GO 

-- Création d'un trigger pour mettre à jour automatiquement update_at.
CREATE TRIGGER trg_Update_Calendrier
ON Calendrier
AFTER UPDATE
AS
BEGIN
    UPDATE Calendrier
    SET updated_at = GETDATE()
    FROM Calendrier
    INNER JOIN inserted ON Calendrier.id_calendrier = inserted.id_calendrier;
END;

-- Création de la table Utilisateur_Calendrier
CREATE TABLE Utilisateur_Calendrier (
    id_utilisateur       INT NOT NULL,
    id_calendrier   INT NOT NULL,
    est_membre      bit NOT NULL,
    invitation_acceptee bit DEFAULT 0,

    CONSTRAINT fk_cu_user     FOREIGN KEY (id_utilisateur)REFERENCES Utilisateur(id_utilisateur),
    CONSTRAINT fk_cu_calendar FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

-- Création de la table Evenement
CREATE TABLE Evenement (
    id_evenement INT IDENTITY(1,1) NOT NULL,
    id_calendrier INT NOT NULL,
    nom VARCHAR(255) NOT NULL, 
    description VARCHAR(255),
    couleur VARCHAR(6),

    PRIMARY KEY (id_calendrier, id_evenement),
    CONSTRAINT fk_ev_calendrier FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

-- Création de la table Element
CREATE TABLE Element (
    id_evenement INT,
    id_calendrier INT NOT NULL, 
    id_element INT IDENTITY(1,1) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    date_debut date, 
    date_fin date,

    PRIMARY KEY (id_calendrier, id_element),
	FOREIGN KEY (id_calendrier, id_evenement) REFERENCES Evenement(id_calendrier, id_evenement),
    FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

CREATE TABLE Connexion (
    token VARCHAR(255) PRIMARY KEY, 
    id_utilisateur INT REFERENCES Utilisateur(id_utilisateur)
)