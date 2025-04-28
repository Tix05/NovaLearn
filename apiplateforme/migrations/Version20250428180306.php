<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428180306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE abonnement_notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type_notification VARCHAR(255) NOT NULL, canal VARCHAR(20) NOT NULL, actif TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_F3CB4D78A76ED395 (user_id), UNIQUE INDEX user_type_canal_unique (user_id, type_notification, canal), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE agenda (id INT AUTO_INCREMENT NOT NULL, auteur_id INT NOT NULL, mention_id INT DEFAULT NULL, parcours_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, date DATE NOT NULL, date_expiration DATE NOT NULL, description LONGTEXT NOT NULL, image VARCHAR(255) DEFAULT NULL, video VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2CEDC87760BB6FE6 (auteur_id), INDEX IDX_2CEDC8777A4147F0 (mention_id), INDEX IDX_2CEDC8776E38C0DB (parcours_id), INDEX IDX_2CEDC877B3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE bibliotheque (id INT AUTO_INCREMENT NOT NULL, mention_id INT DEFAULT NULL, parcours_id INT DEFAULT NULL, ec_id INT NOT NULL, titre VARCHAR(255) NOT NULL, fichier VARCHAR(255) NOT NULL, status TINYINT(1) NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_4690D34D7A4147F0 (mention_id), INDEX IDX_4690D34D6E38C0DB (parcours_id), INDEX IDX_4690D34D27634BEF (ec_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, ec_id INT NOT NULL, contenu LONGTEXT NOT NULL, time DATETIME NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_67F068BCA76ED395 (user_id), INDEX IDX_67F068BC27634BEF (ec_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, ec_id INT DEFAULT NULL, parcours_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, sujet VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_8A8E26E927634BEF (ec_id), INDEX IDX_8A8E26E96E38C0DB (parcours_id), INDEX IDX_8A8E26E9B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, titre VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, type_document VARCHAR(50) NOT NULL, est_valide TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_D8698A76A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ec (id INT AUTO_INCREMENT NOT NULL, prof_id INT NOT NULL, ue_id INT NOT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, coeff INT NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_8DE8BDFFABC1F7FE (prof_id), INDEX IDX_8DE8BDFF62E883B1 (ue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, mention_id INT NOT NULL, parcours_id INT NOT NULL, niveau_id INT NOT NULL, year_id INT NOT NULL, matricule VARCHAR(255) NOT NULL, date_inscription DATE NOT NULL, status TINYINT(1) NOT NULL, fichier_virement VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, type_payement VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_717E22E312B2DC9C (matricule), INDEX IDX_717E22E3A76ED395 (user_id), INDEX IDX_717E22E37A4147F0 (mention_id), INDEX IDX_717E22E36E38C0DB (parcours_id), INDEX IDX_717E22E3B3E9C81 (niveau_id), INDEX IDX_717E22E340C1FEA7 (year_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE fichier_support (id INT AUTO_INCREMENT NOT NULL, ec_id INT NOT NULL, auteur_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, fichier VARCHAR(255) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, date_ajout DATETIME NOT NULL, est_publique TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5B97179227634BEF (ec_id), INDEX IDX_5B97179260BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mention (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, expediteur_id INT NOT NULL, contenu LONGTEXT NOT NULL, lu TINYINT(1) NOT NULL, date_envoi DATETIME NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX idx_conversation_date (conversation_id, date_envoi), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE niveau (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, nom VARCHAR(100) NOT NULL, cycle VARCHAR(2) NOT NULL, ordre INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_4BDFF36B77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, conversation_id INT DEFAULT NULL, message_id INT DEFAULT NULL, agenda_id INT DEFAULT NULL, document_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, lien VARCHAR(255) NOT NULL, lue TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_lecture DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA9AC0396 (conversation_id), INDEX IDX_BF5476CA537A1329 (message_id), INDEX IDX_BF5476CAEA67784A (agenda_id), INDEX IDX_BF5476CAC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_groupe (id INT AUTO_INCREMENT NOT NULL, notification_id INT NOT NULL, parcours_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, mention_id INT DEFAULT NULL, ec_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_C01E727C7A4147F0 (mention_id), INDEX IDX_C01E727C27634BEF (ec_id), INDEX idx_notification (notification_id), INDEX idx_parcours (parcours_id), INDEX idx_niveau (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE parcours (id INT AUTO_INCREMENT NOT NULL, niveau_id INT DEFAULT NULL, mention_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_99B1DEE3B3E9C81 (niveau_id), INDEX IDX_99B1DEE37A4147F0 (mention_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participant_conversation (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(20) NOT NULL, date_dernier_vu DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_17A662BB9AC0396 (conversation_id), INDEX IDX_17A662BBA76ED395 (user_id), UNIQUE INDEX participant_unique (conversation_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE prof (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, status TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_5BBA70BBA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE province (id INT AUTO_INCREMENT NOT NULL, region VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE semestre (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ue (id INT AUTO_INCREMENT NOT NULL, mention_id INT NOT NULL, semestre_id INT NOT NULL, niveau_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_2E490A9B7A4147F0 (mention_id), INDEX IDX_2E490A9B5577AFDB (semestre_id), INDEX IDX_2E490A9BB3E9C81 (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE ue_parcours (id INT AUTO_INCREMENT NOT NULL, ue_id INT NOT NULL, parcours_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_82440F4462E883B1 (ue_id), INDEX IDX_82440F446E38C0DB (parcours_id), UNIQUE INDEX ue_parcours_unique (ue_id, parcours_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, province_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(60) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, avatar VARCHAR(125) DEFAULT NULL, ville VARCHAR(255) DEFAULT NULL, reset_token VARCHAR(255) DEFAULT NULL, status TINYINT(1) NOT NULL, adresse VARCHAR(255) DEFAULT NULL, preferences_notification JSON DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649E946114A (province_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE years (id INT AUTO_INCREMENT NOT NULL, current TINYINT(1) NOT NULL, year VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_notification ADD CONSTRAINT FK_F3CB4D78A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC87760BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC8777A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC8776E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC877B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E927634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E96E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document ADD CONSTRAINT FK_D8698A76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec ADD CONSTRAINT FK_8DE8BDFFABC1F7FE FOREIGN KEY (prof_id) REFERENCES prof (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec ADD CONSTRAINT FK_8DE8BDFF62E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E37A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E36E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E340C1FEA7 FOREIGN KEY (year_id) REFERENCES years (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support ADD CONSTRAINT FK_5B97179227634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support ADD CONSTRAINT FK_5B97179260BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD CONSTRAINT FK_B6BD307F10335F61 FOREIGN KEY (expediteur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA537A1329 FOREIGN KEY (message_id) REFERENCES message (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAEA67784A FOREIGN KEY (agenda_id) REFERENCES agenda (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAC33F7837 FOREIGN KEY (document_id) REFERENCES document (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe ADD CONSTRAINT FK_C01E727CEF1A9D84 FOREIGN KEY (notification_id) REFERENCES notification (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe ADD CONSTRAINT FK_C01E727C6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe ADD CONSTRAINT FK_C01E727CB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe ADD CONSTRAINT FK_C01E727C7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe ADD CONSTRAINT FK_C01E727C27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours ADD CONSTRAINT FK_99B1DEE3B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours ADD CONSTRAINT FK_99B1DEE37A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation ADD CONSTRAINT FK_17A662BB9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation ADD CONSTRAINT FK_17A662BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prof ADD CONSTRAINT FK_5BBA70BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B5577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours ADD CONSTRAINT FK_82440F4462E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours ADD CONSTRAINT FK_82440F446E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D649E946114A FOREIGN KEY (province_id) REFERENCES province (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_notification DROP FOREIGN KEY FK_F3CB4D78A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC87760BB6FE6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC8777A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC8776E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC877B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D7A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D6E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D27634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC27634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E927634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E96E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document DROP FOREIGN KEY FK_D8698A76A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec DROP FOREIGN KEY FK_8DE8BDFFABC1F7FE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec DROP FOREIGN KEY FK_8DE8BDFF62E883B1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E37A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E36E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E340C1FEA7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support DROP FOREIGN KEY FK_5B97179227634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support DROP FOREIGN KEY FK_5B97179260BB6FE6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP FOREIGN KEY FK_B6BD307F10335F61
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA537A1329
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAEA67784A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAC33F7837
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe DROP FOREIGN KEY FK_C01E727CEF1A9D84
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe DROP FOREIGN KEY FK_C01E727C6E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe DROP FOREIGN KEY FK_C01E727CB3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe DROP FOREIGN KEY FK_C01E727C7A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification_groupe DROP FOREIGN KEY FK_C01E727C27634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours DROP FOREIGN KEY FK_99B1DEE3B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours DROP FOREIGN KEY FK_99B1DEE37A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation DROP FOREIGN KEY FK_17A662BB9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation DROP FOREIGN KEY FK_17A662BBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE prof DROP FOREIGN KEY FK_5BBA70BBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B7A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B5577AFDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BB3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours DROP FOREIGN KEY FK_82440F4462E883B1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours DROP FOREIGN KEY FK_82440F446E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D649E946114A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE abonnement_notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE agenda
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bibliotheque
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE commentaire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE document
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ec
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE etudiant
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE fichier_support
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mention
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE niveau
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification_groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participant_conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE prof
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE province
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE semestre
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ue
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE ue_parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE years
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
