
-- Table des utilisateurs
CREATE TABLE utilisateurs (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    courriel      VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe  VARCHAR(255) NOT NULL,
    nom           VARCHAR(255),
    est_valide    BOOLEAN DEFAULT FALSE,

    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des calendriers
CREATE TABLE calendriers (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nom           VARCHAR(255) NOT NULL,
    description   TEXT,
    auteur_id     INT NOT NULL,

    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_calendrier_auteur FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id)
);

CREATE TABLE calendrier_utilisateur (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    id_calendrier   INT NOT NULL,
    role          ENUM('AUTHOR', 'MEMBER', 'GUEST') NOT NULL,

    UNIQUE KEY unique_user_calendar (user_id, id_calendrier),
    CONSTRAINT fk_cu_user     FOREIGN KEY (user_id)     REFERENCES utilisateurs(id),
    CONSTRAINT fk_cu_calendar FOREIGN KEY (id_calendrier) REFERENCES calendriers(id)
);

CREATE TABLE evenements (
    id_evenement INT NOT NULL,
    id_calendrier INT NOT NULL,
    nom VARCHAR(255) NOT NULL, 
    description VARCHAR(255),

    PRIMARY KEY (id_calendrier, id_evenement),
    FOREIGN KEY (id_calendrier) REFERENCES calendriers(id)
);

DELIMITER //
CREATE TRIGGER avant_inserer_evenement
BEFORE INSERT ON evenements
    FOR EACH ROW
        BEGIN 

            DECLARE id_max INT; 

            SELECT IFNULL(MAX(id_evenement), 0) + 1 INTO id_max
            FROM evenements
            WHERE id_calendrier = NEW.id_calendrier;

            SET NEW.id_evenement = id_max;
    END //
DELIMITER ;


CREATE TABLE element (
    id_evenement INT REFERENCES evenements(id_evenement),
    id_calendrier INT NOT NULL, 
    id_element INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    date_debut DATETIME, 
    date_fin DATETIME,

    PRIMARY KEY (id_calendrier, id_element),
    FOREIGN KEY (id_calendrier) REFERENCES calendriers(id)
);

DELIMITER //
CREATE TRIGGER avant_inserer_element
BEFORE INSERT ON element
    FOR EACH ROW
        BEGIN 

            DECLARE id_max INT;

            SELECT IFNULL(MAX(id_element), 0) + 1 INTO id_max
            FROM element
            WHERE id_calendrier = NEW.id_calendrier;

            SET NEW.id_element = id_max;
    END //
DELIMITER ;