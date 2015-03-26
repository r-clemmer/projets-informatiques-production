SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `pmqvision4` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;

-- -----------------------------------------------------
-- Table `pmqvision4`.`entite`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`entite` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`entite` (
  `entite_id` INT NOT NULL AUTO_INCREMENT ,
  `entite_nom` VARCHAR(255) NOT NULL COMMENT 'nom de l\'entité' ,
  `entite_code` VARCHAR(255) NULL COMMENT 'code' ,
  `entite_adresse` VARCHAR(255) NOT NULL COMMENT 'adresse' ,
  `entite_ville` VARCHAR(255) NOT NULL COMMENT 'ville' ,
  `entite_cp` VARCHAR(255) NOT NULL COMMENT 'code postal' ,
  `entite_activite` VARCHAR(255) NULL COMMENT 'activite d\'une entreprise' ,
  `entite_tel` VARCHAR(255) NULL COMMENT 'téléphone' ,
  `entite_date_creation` DATE NOT NULL ,
  `parent_id` INT NULL ,
  `entite_login` VARCHAR(255) NULL ,
  PRIMARY KEY (`entite_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`civilite`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`civilite` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`civilite` (
  `civilite_id` INT NOT NULL AUTO_INCREMENT ,
  `civilite_libelle` VARCHAR(255) NOT NULL ,
  `civilite_abrege` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`civilite_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`personne`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`personne` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`personne` (
  `personne_id` INT NOT NULL AUTO_INCREMENT ,
  `civilite_id` INT NOT NULL ,
  `personne_nom` VARCHAR(255) NOT NULL ,
  `personne_prenom` VARCHAR(255) NOT NULL ,
  `personne_date_naissance` DATE NULL ,
  `personne_tel` VARCHAR(255) NULL ,
  `personne_port` VARCHAR(255) NULL ,
  `personne_mail` VARCHAR(255) NULL ,
  `personne_poste` VARCHAR(255) NULL ,
  `personne_date_creation` DATE NOT NULL ,
  `entite_id` INT NOT NULL ,
  PRIMARY KEY (`personne_id`) ,
  CONSTRAINT `fk_personne_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_personne_civilite1`
    FOREIGN KEY (`civilite_id` )
    REFERENCES `pmqvision4`.`civilite` (`civilite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_personne_entite1` ON `pmqvision4`.`personne` (`entite_id` ASC) ;

CREATE INDEX `fk_personne_civilite1` ON `pmqvision4`.`personne` (`civilite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`contact`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`contact` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`contact` (
  `contact_id` INT NOT NULL AUTO_INCREMENT ,
  `contact_forme` TINYINT(1)  NOT NULL DEFAULT false ,
  `contact_date_formation` DATE NULL ,
  `personne_id` INT NOT NULL ,
  PRIMARY KEY (`contact_id`) ,
  CONSTRAINT `fk_contact_personne1`
    FOREIGN KEY (`personne_id` )
    REFERENCES `pmqvision4`.`personne` (`personne_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_contact_personne1` ON `pmqvision4`.`contact` (`personne_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`type_entite`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`type_entite` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`type_entite` (
  `type_entite_id` INT NOT NULL AUTO_INCREMENT ,
  `type_entite_libelle` VARCHAR(45) NOT NULL COMMENT 'libelle type entite :\rentreprise\rorganisme referent\rdelegation...' ,
  PRIMARY KEY (`type_entite_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`candidat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`candidat` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`candidat` (
  `candidat_id` INT NOT NULL AUTO_INCREMENT ,
  `candidat_code` VARCHAR(255) NULL COMMENT 'pas utilisé dans la v3' ,
  `candidat_anciennete` DATE NOT NULL COMMENT 'année d\'entrée dans l\'entreprise' ,
  `candidat_contrat` VARCHAR(255) NOT NULL COMMENT 'contrat' ,
  `candidat_cursus` VARCHAR(255) NULL ,
  `personne_id` INT NOT NULL ,
  PRIMARY KEY (`candidat_id`) ,
  CONSTRAINT `fk_candidat_personne1`
    FOREIGN KEY (`personne_id` )
    REFERENCES `pmqvision4`.`personne` (`personne_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_candidat_personne1` ON `pmqvision4`.`candidat` (`personne_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`objectif`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`objectif` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`objectif` (
  `objectif_id` INT NOT NULL AUTO_INCREMENT ,
  `objectif_libelle` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`objectif_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`fiche`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`fiche` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`fiche` (
  `fiche_id` INT NOT NULL AUTO_INCREMENT ,
  `fiche_remarque` TEXT NULL COMMENT 'remarques concernant une fiche' ,
  `fiche_acces_candidats` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'validation accès aux candidats\r0 -> non\r1 -> ok' ,
  `fiche_projet` TINYINT(1)  NOT NULL DEFAULT 0 COMMENT 'fiche en mode projet\r0 -> fiche\r1 -> fiche projet' ,
  `fiche_date_creation` DATE NOT NULL ,
  `fiche_date_modif` DATE NOT NULL ,
  `fiche_date_meo` DATE NULL ,
  `objectif_id` INT NULL ,
  PRIMARY KEY (`fiche_id`) ,
  CONSTRAINT `fk_fiche_objectif1`
    FOREIGN KEY (`objectif_id` )
    REFERENCES `pmqvision4`.`objectif` (`objectif_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_fiche_objectif1` ON `pmqvision4`.`fiche` (`objectif_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`jury`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`jury` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`jury` (
  `jury_id` INT NOT NULL AUTO_INCREMENT ,
  `jury_ville` VARCHAR(255) NOT NULL ,
  `jury_cp` VARCHAR(255) NULL ,
  `jury_adresse` VARCHAR(255) NULL ,
  `jury_date` DATE NOT NULL ,
  `branche_id` INT NULL ,
  `fed_patron_id` INT NULL ,
  `fed_salar_id` INT NULL ,
  PRIMARY KEY (`jury_id`) ,
  CONSTRAINT `fk_jury_entite1`
    FOREIGN KEY (`branche_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_jury_entite2`
    FOREIGN KEY (`fed_patron_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_jury_entite3`
    FOREIGN KEY (`fed_salar_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_jury_entite1` ON `pmqvision4`.`jury` (`branche_id` ASC) ;

CREATE INDEX `fk_jury_entite2` ON `pmqvision4`.`jury` (`fed_patron_id` ASC) ;

CREATE INDEX `fk_jury_entite3` ON `pmqvision4`.`jury` (`fed_salar_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`demarche`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`demarche` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`demarche` (
  `demarche_id` INT NOT NULL AUTO_INCREMENT ,
  `demarche_libelle` VARCHAR(255) NOT NULL COMMENT 'libelle de la demarche' ,
  `demarche_abrege` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`demarche_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`metier`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`metier` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`metier` (
  `metier_id` INT NOT NULL AUTO_INCREMENT ,
  `metier_effectif` INT NOT NULL COMMENT 'effectif prevu' ,
  `metier_nb_dossiers_candidats` INT NOT NULL COMMENT 'nombre de dossiers candidats' ,
  `metier_nb_dossiers_tuteurs` INT NOT NULL COMMENT 'nombre de dossiers tuteurs' ,
  `metier_date_envoi_dossiers` DATE NULL ,
  `fiche_id` INT NOT NULL ,
  `demarche_id` INT NOT NULL ,
  `bloc1_id` INT NOT NULL COMMENT 'identifiant xml' ,
  `bloc2_id` INT NULL ,
  `metier_xml` VARCHAR(2) NOT NULL ,
  PRIMARY KEY (`metier_id`) ,
  CONSTRAINT `fk_fiche_has_history_fiche1`
    FOREIGN KEY (`fiche_id` )
    REFERENCES `pmqvision4`.`fiche` (`fiche_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_metier_demarche1`
    FOREIGN KEY (`demarche_id` )
    REFERENCES `pmqvision4`.`demarche` (`demarche_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_fiche_has_history_fiche1` ON `pmqvision4`.`metier` (`fiche_id` ASC) ;

CREATE INDEX `fk_metier_demarche1` ON `pmqvision4`.`metier` (`demarche_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`binome`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`binome` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`binome` (
  `binome_id` INT NOT NULL AUTO_INCREMENT ,
  `metier_id` INT NOT NULL ,
  `contact_id` INT NOT NULL ,
  `binome_defaut` TINYINT(1)  NOT NULL DEFAULT false ,
  PRIMARY KEY (`binome_id`) ,
  CONSTRAINT `fk_binome_metier1`
    FOREIGN KEY (`metier_id` )
    REFERENCES `pmqvision4`.`metier` (`metier_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_binome_contact1`
    FOREIGN KEY (`contact_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_binome_metier1` ON `pmqvision4`.`binome` (`metier_id` ASC) ;

CREATE INDEX `fk_binome_contact1` ON `pmqvision4`.`binome` (`contact_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`etat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`etat` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`etat` (
  `etat_id` INT NOT NULL AUTO_INCREMENT ,
  `etat_libelle` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`etat_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`raison`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`raison` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`raison` (
  `raison_id` INT NOT NULL AUTO_INCREMENT ,
  `raison_libelle` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`raison_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`formation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`formation` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`formation` (
  `formation_id` INT NOT NULL AUTO_INCREMENT ,
  `formation_libelle` VARCHAR(255) NOT NULL ,
  `formation_formacode` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`formation_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`candidat_metier`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`candidat_metier` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`candidat_metier` (
  `candidat_metier_id` INT NOT NULL AUTO_INCREMENT ,
  `candidat_id` INT NOT NULL ,
  `metier_id` INT NOT NULL ,
  `tuteur_id` INT NULL DEFAULT NULL ,
  `expert_id` INT NULL DEFAULT NULL ,
  `candidat_metier_fiche_enquete` TINYINT(1)  NOT NULL DEFAULT 0 ,
  `etat_id` INT NOT NULL ,
  `raison_id` INT NULL ,
  `formation_id` INT NULL ,
  `candidat_metier_formation_duree_estimee` INT NOT NULL DEFAULT 0 ,
  `candidat_metier_formation_duree_realisee` INT NOT NULL DEFAULT 0 ,
  `candidat_metier_formation_remarque` VARCHAR(255) NULL ,
  `org_formation_id` INT NULL ,
  `formateur_id` INT NULL ,
  PRIMARY KEY (`candidat_metier_id`) ,
  CONSTRAINT `fk_liaison_fiche_fiche_has_history1`
    FOREIGN KEY (`metier_id` )
    REFERENCES `pmqvision4`.`metier` (`metier_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_candidat1`
    FOREIGN KEY (`candidat_id` )
    REFERENCES `pmqvision4`.`candidat` (`candidat_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_binome1`
    FOREIGN KEY (`expert_id` )
    REFERENCES `pmqvision4`.`binome` (`binome_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_binome2`
    FOREIGN KEY (`tuteur_id` )
    REFERENCES `pmqvision4`.`binome` (`binome_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_etat_candidat1`
    FOREIGN KEY (`etat_id` )
    REFERENCES `pmqvision4`.`etat` (`etat_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_raison1`
    FOREIGN KEY (`raison_id` )
    REFERENCES `pmqvision4`.`raison` (`raison_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_formation1`
    FOREIGN KEY (`formation_id` )
    REFERENCES `pmqvision4`.`formation` (`formation_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_entite1`
    FOREIGN KEY (`org_formation_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_candidat_metier_contact1`
    FOREIGN KEY (`formateur_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_liaison_fiche_fiche_has_history1` ON `pmqvision4`.`candidat_metier` (`metier_id` ASC) ;

CREATE INDEX `fk_candidat_metier_candidat1` ON `pmqvision4`.`candidat_metier` (`candidat_id` ASC) ;

CREATE INDEX `fk_candidat_metier_binome1` ON `pmqvision4`.`candidat_metier` (`expert_id` ASC) ;

CREATE INDEX `fk_candidat_metier_binome2` ON `pmqvision4`.`candidat_metier` (`tuteur_id` ASC) ;

CREATE INDEX `fk_candidat_metier_etat_candidat1` ON `pmqvision4`.`candidat_metier` (`etat_id` ASC) ;

CREATE INDEX `fk_candidat_metier_raison1` ON `pmqvision4`.`candidat_metier` (`raison_id` ASC) ;

CREATE INDEX `fk_candidat_metier_formation1` ON `pmqvision4`.`candidat_metier` (`formation_id` ASC) ;

CREATE INDEX `fk_candidat_metier_entite1` ON `pmqvision4`.`candidat_metier` (`org_formation_id` ASC) ;

CREATE INDEX `fk_candidat_metier_contact1` ON `pmqvision4`.`candidat_metier` (`formateur_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`resultat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`resultat` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`resultat` (
  `resultat_id` INT NOT NULL AUTO_INCREMENT ,
  `resultat_num_passage` INT NOT NULL DEFAULT 1 COMMENT 'notes' ,
  `jury_id` INT NULL ,
  `resultat_commentaire_jury` VARCHAR(255) NULL ,
  `candidat_metier_id` INT NOT NULL ,
  PRIMARY KEY (`resultat_id`) ,
  CONSTRAINT `fk_resultat_jury1`
    FOREIGN KEY (`jury_id` )
    REFERENCES `pmqvision4`.`jury` (`jury_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_resultat_candidat_metier1`
    FOREIGN KEY (`candidat_metier_id` )
    REFERENCES `pmqvision4`.`candidat_metier` (`candidat_metier_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_resultat_jury1` ON `pmqvision4`.`resultat` (`jury_id` ASC) ;

CREATE INDEX `fk_resultat_candidat_metier1` ON `pmqvision4`.`resultat` (`candidat_metier_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`outil`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`outil` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`outil` (
  `outil_id` INT NOT NULL AUTO_INCREMENT ,
  `outil_libelle` VARCHAR(45) NOT NULL COMMENT 'libelle du champ' ,
  `demarche_id` INT NOT NULL ,
  PRIMARY KEY (`outil_id`) ,
  CONSTRAINT `fk_champ_demarche1`
    FOREIGN KEY (`demarche_id` )
    REFERENCES `pmqvision4`.`demarche` (`demarche_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_champ_demarche1` ON `pmqvision4`.`outil` (`demarche_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`fonction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`fonction` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`fonction` (
  `fonction_id` INT NOT NULL AUTO_INCREMENT ,
  `fonction_libelle` VARCHAR(255) NOT NULL COMMENT 'libellé fonction contact' ,
  PRIMARY KEY (`fonction_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`entite_type_entite`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`entite_type_entite` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`entite_type_entite` (
  `entite_id` INT NOT NULL ,
  `type_entite_id` INT NOT NULL ,
  CONSTRAINT `fk_entite_has_type_entite_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_entite_has_type_entite_type_entite1`
    FOREIGN KEY (`type_entite_id` )
    REFERENCES `pmqvision4`.`type_entite` (`type_entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_entite_has_type_entite_entite1` ON `pmqvision4`.`entite_type_entite` (`entite_id` ASC) ;

CREATE INDEX `fk_entite_has_type_entite_type_entite1` ON `pmqvision4`.`entite_type_entite` (`type_entite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`fonction_contact`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`fonction_contact` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`fonction_contact` (
  `fonction_id` INT NOT NULL ,
  `contact_id` INT NOT NULL ,
  CONSTRAINT `fk_fonction_has_contact_fonction1`
    FOREIGN KEY (`fonction_id` )
    REFERENCES `pmqvision4`.`fonction` (`fonction_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_fonction_has_contact_contact1`
    FOREIGN KEY (`contact_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_fonction_has_contact_fonction1` ON `pmqvision4`.`fonction_contact` (`fonction_id` ASC) ;

CREATE INDEX `fk_fonction_has_contact_contact1` ON `pmqvision4`.`fonction_contact` (`contact_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`type_entite_fonction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`type_entite_fonction` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`type_entite_fonction` (
  `type_entite_id` INT NOT NULL ,
  `fonction_id` INT NOT NULL ,
  CONSTRAINT `fk_fonction_has_type_entite_fonction1`
    FOREIGN KEY (`fonction_id` )
    REFERENCES `pmqvision4`.`fonction` (`fonction_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_fonction_has_type_entite_type_entite1`
    FOREIGN KEY (`type_entite_id` )
    REFERENCES `pmqvision4`.`type_entite` (`type_entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_fonction_has_type_entite_fonction1` ON `pmqvision4`.`type_entite_fonction` (`fonction_id` ASC) ;

CREATE INDEX `fk_fonction_has_type_entite_type_entite1` ON `pmqvision4`.`type_entite_fonction` (`type_entite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`type_membre_jury`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`type_membre_jury` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`type_membre_jury` (
  `type_membre_jury_id` INT NOT NULL AUTO_INCREMENT ,
  `type_membre_jury_libelle` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`type_membre_jury_id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `pmqvision4`.`membre_jury`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`membre_jury` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`membre_jury` (
  `contact_id` INT NOT NULL ,
  `jury_id` INT NOT NULL ,
  `type_membre_jury_id` INT NOT NULL ,
  CONSTRAINT `fk_contact_has_jury_contact1`
    FOREIGN KEY (`contact_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contact_has_jury_jury1`
    FOREIGN KEY (`jury_id` )
    REFERENCES `pmqvision4`.`jury` (`jury_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contact_has_jury_type_membre_jury1`
    FOREIGN KEY (`type_membre_jury_id` )
    REFERENCES `pmqvision4`.`type_membre_jury` (`type_membre_jury_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_contact_has_jury_contact1` ON `pmqvision4`.`membre_jury` (`contact_id` ASC) ;

CREATE INDEX `fk_contact_has_jury_jury1` ON `pmqvision4`.`membre_jury` (`jury_id` ASC) ;

CREATE INDEX `fk_contact_has_jury_type_membre_jury1` ON `pmqvision4`.`membre_jury` (`type_membre_jury_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`expertise`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`expertise` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`expertise` (
  `expertise_id` INT NOT NULL AUTO_INCREMENT ,
  `contact_id` INT NOT NULL DEFAULT 0 ,
  `demarche_id` INT NOT NULL DEFAULT 0 ,
  `bloc1_id` INT NOT NULL DEFAULT 0 ,
  `bloc2_id` INT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`expertise_id`) ,
  CONSTRAINT `fk_expertise_contact1`
    FOREIGN KEY (`contact_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_expertise_demarche1`
    FOREIGN KEY (`demarche_id` )
    REFERENCES `pmqvision4`.`demarche` (`demarche_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_expertise_contact1` ON `pmqvision4`.`expertise` (`contact_id` ASC) ;

CREATE INDEX `fk_expertise_demarche1` ON `pmqvision4`.`expertise` (`demarche_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`question`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`question` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`question` (
  `question_id` INT NOT NULL AUTO_INCREMENT ,
  `question_auteur` VARCHAR(255) NOT NULL ,
  `question_objet` VARCHAR(255) NOT NULL ,
  `question_message` TEXT NOT NULL ,
  `question_severite` INT NOT NULL DEFAULT 0 ,
  `question_date` DATETIME NOT NULL ,
  `question_valide` TINYINT(1)  NOT NULL DEFAULT 0 ,
  `entite_id` INT NOT NULL ,
  PRIMARY KEY (`question_id`) ,
  CONSTRAINT `fk_question_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_question_entite1` ON `pmqvision4`.`question` (`entite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`reponse`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`reponse` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`reponse` (
  `reponse_id` INT NOT NULL AUTO_INCREMENT ,
  `reponse_auteur` VARCHAR(255) NOT NULL ,
  `reponse_message` VARCHAR(1000) NOT NULL ,
  `reponse_date` DATETIME NOT NULL ,
  `question_id` INT NOT NULL ,
  `entite_id` INT NOT NULL ,
  PRIMARY KEY (`reponse_id`) ,
  CONSTRAINT `fk_reponse_question1`
    FOREIGN KEY (`question_id` )
    REFERENCES `pmqvision4`.`question` (`question_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reponse_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_reponse_question1` ON `pmqvision4`.`reponse` (`question_id` ASC) ;

CREATE INDEX `fk_reponse_entite1` ON `pmqvision4`.`reponse` (`entite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`contacts_fiche`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`contacts_fiche` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`contacts_fiche` (
  `fiche_id` INT NOT NULL ,
  `entite_id` INT NULL ,
  `contact_id` INT NULL ,
  CONSTRAINT `fk_contacts_fiche_fiche1`
    FOREIGN KEY (`fiche_id` )
    REFERENCES `pmqvision4`.`fiche` (`fiche_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contacts_fiche_contact1`
    FOREIGN KEY (`contact_id` )
    REFERENCES `pmqvision4`.`contact` (`contact_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_contacts_fiche_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_contacts_fiche_fiche1` ON `pmqvision4`.`contacts_fiche` (`fiche_id` ASC) ;

CREATE INDEX `fk_contacts_fiche_contact1` ON `pmqvision4`.`contacts_fiche` (`contact_id` ASC) ;

CREATE INDEX `fk_contacts_fiche_entite1` ON `pmqvision4`.`contacts_fiche` (`entite_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`resultat_outil`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`resultat_outil` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`resultat_outil` (
  `resultat_outil_id` INT NOT NULL AUTO_INCREMENT ,
  `outil_id` INT NOT NULL ,
  `resultat_id` INT NOT NULL ,
  `resultat_valeur` TEXT NULL ,
  `resultat_date` VARCHAR(255) NOT NULL DEFAULT '0000-00-00' ,
  PRIMARY KEY (`resultat_outil_id`) ,
  CONSTRAINT `fk_resultat_outil_outil1`
    FOREIGN KEY (`outil_id` )
    REFERENCES `pmqvision4`.`outil` (`outil_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_resultat_outil_resultat1`
    FOREIGN KEY (`resultat_id` )
    REFERENCES `pmqvision4`.`resultat` (`resultat_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_resultat_outil_outil1` ON `pmqvision4`.`resultat_outil` (`outil_id` ASC) ;

CREATE INDEX `fk_resultat_outil_resultat1` ON `pmqvision4`.`resultat_outil` (`resultat_id` ASC) ;


-- -----------------------------------------------------
-- Table `pmqvision4`.`entite_formation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pmqvision4`.`entite_formation` ;

CREATE  TABLE IF NOT EXISTS `pmqvision4`.`entite_formation` (
  `entite_id` INT NOT NULL ,
  `formation_id` INT NOT NULL ,
  CONSTRAINT `fk_entite_has_formation_entite1`
    FOREIGN KEY (`entite_id` )
    REFERENCES `pmqvision4`.`entite` (`entite_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_entite_has_formation_formation1`
    FOREIGN KEY (`formation_id` )
    REFERENCES `pmqvision4`.`formation` (`formation_id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;

CREATE INDEX `fk_entite_has_formation_entite1` ON `pmqvision4`.`entite_formation` (`entite_id` ASC) ;

CREATE INDEX `fk_entite_has_formation_formation1` ON `pmqvision4`.`entite_formation` (`formation_id` ASC) ;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
