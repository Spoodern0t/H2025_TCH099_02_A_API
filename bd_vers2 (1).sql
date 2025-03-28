
-- Table des Utilisateur
CREATE TABLE Utilisateur (
    id_utilisateur INT(10) AUTO_INCREMENT PRIMARY KEY,
    courriel       VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe   VARCHAR(255) NOT NULL,
    nom            VARCHAR(255),
    est_valide     bit DEFAULT FALSE,

    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des calendriers
CREATE TABLE Calendrier (
    id_calendrier INT(10) AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(255) NOT NULL,
    description   VARCHAR(255),
    auteur_id     INT(10) NOT NULL,

    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_calendrier_auteur FOREIGN KEY (auteur_id) REFERENCES Utilisateur(id_utilisateur)
);

CREATE TABLE Utilisateur_Calendrier (
    id_utilisateur       INT(10) NOT NULL,
    id_calendrier   INT(10) NOT NULL,
    est_membre      bit NOT NULL,
    invitation_acceptee bit DEFAULT FALSE,
    url_invitation VARCHAR(255) UNIQUE,

    CONSTRAINT fk_cu_user     FOREIGN KEY (id_utilisateur)REFERENCES Utilisateur(id_utilisateur),
    CONSTRAINT fk_cu_calendar FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

CREATE TABLE Evenement (
    id_evenement INT(10) NOT NULL,
    id_calendrier INT(10) NOT NULL,
    nom VARCHAR(255) NOT NULL, 
    description VARCHAR(255),
    couleur VARCHAR(6),

    PRIMARY KEY (id_calendrier, id_evenement),
    CONSTRAINT fk_ev_calendrier FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

DELIMITER //
CREATE TRIGGER avant_inserer_evenement
BEFORE INSERT ON Evenement
    FOR EACH ROW
        BEGIN 

            DECLARE id_max INT; 

            SELECT IFNULL(MAX(id_evenement), 0) + 1 INTO id_max
            FROM Evenement
            WHERE id_calendrier = NEW.id_calendrier;

            SET NEW.id_evenement = id_max;
    END //
DELIMITER ;


CREATE TABLE Element (
    id_evenement INT(10) REFERENCES Evenement(id_evenement),
    id_calendrier INT(10) NOT NULL, 
    id_element INT(10) NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    date_debut date, 
    date_fin date,

    PRIMARY KEY (id_calendrier, id_element),
    FOREIGN KEY (id_calendrier) REFERENCES Calendrier(id_calendrier)
);

DELIMITER //
CREATE TRIGGER avant_inserer_element
BEFORE INSERT ON Element
    FOR EACH ROW
        BEGIN 

            DECLARE id_max INT;

            SELECT IFNULL(MAX(id_element), 0) + 1 INTO id_max
            FROM Element
            WHERE id_calendrier = NEW.id_calendrier;

            SET NEW.id_element = id_max;
    END //
DELIMITER ;

CREATE TABLE Connexion (
    token VARCHAR(255) PRIMARY KEY, 
    id_utilisateur INT(10) REFERENCES Utilisateur(id_utilisateur)
)