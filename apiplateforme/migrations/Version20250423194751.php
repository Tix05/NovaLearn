<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250423194751 extends AbstractMigration
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
            CREATE TABLE conversation (id INT AUTO_INCREMENT NOT NULL, ec_id INT DEFAULT NULL, parcours_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, sujet VARCHAR(255) NOT NULL, type VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_8A8E26E927634BEF (ec_id), INDEX IDX_8A8E26E96E38C0DB (parcours_id), INDEX IDX_8A8E26E9B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, expediteur_id INT NOT NULL, contenu LONGTEXT NOT NULL, lu TINYINT(1) NOT NULL, date_envoi DATETIME NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_B6BD307F9AC0396 (conversation_id), INDEX IDX_B6BD307F10335F61 (expediteur_id), INDEX idx_conversation_date (conversation_id, date_envoi), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, conversation_id INT DEFAULT NULL, message_id INT DEFAULT NULL, agenda_id INT DEFAULT NULL, document_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, lien VARCHAR(255) NOT NULL, lue TINYINT(1) NOT NULL, date_creation DATETIME NOT NULL, date_lecture DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA9AC0396 (conversation_id), INDEX IDX_BF5476CA537A1329 (message_id), INDEX IDX_BF5476CAEA67784A (agenda_id), INDEX IDX_BF5476CAC33F7837 (document_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notification_groupe (id INT AUTO_INCREMENT NOT NULL, notification_id INT NOT NULL, parcours_id INT DEFAULT NULL, niveau_id INT DEFAULT NULL, mention_id INT DEFAULT NULL, ec_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_C01E727C7A4147F0 (mention_id), INDEX IDX_C01E727C27634BEF (ec_id), INDEX idx_notification (notification_id), INDEX idx_parcours (parcours_id), INDEX idx_niveau (niveau_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE participant_conversation (id INT AUTO_INCREMENT NOT NULL, conversation_id INT NOT NULL, user_id INT NOT NULL, role VARCHAR(20) NOT NULL, date_dernier_vu DATETIME DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_17A662BB9AC0396 (conversation_id), INDEX IDX_17A662BBA76ED395 (user_id), UNIQUE INDEX participant_unique (conversation_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_notification ADD CONSTRAINT FK_F3CB4D78A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
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
            ALTER TABLE participant_conversation ADD CONSTRAINT FK_17A662BB9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation ADD CONSTRAINT FK_17A662BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda CHANGE description description LONGTEXT NOT NULL, CHANGE type type VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque CHANGE type type VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE contenu contenu LONGTEXT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document CHANGE type_document type_document VARCHAR(50) NOT NULL, CHANGE fichier file_path VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec CHANGE ue_id ue_id INT NOT NULL, CHANGE prof_id prof_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_717E22E312B2DC9C ON etudiant (matricule)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support CHANGE type type VARCHAR(20) NOT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE est_publique est_publique TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_4BDFF36B77153098 ON niveau (code)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours ADD CONSTRAINT FK_99B1DEE37A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_99B1DEE37A4147F0 ON parcours (mention_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours ADD CONSTRAINT FK_82440F4462E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours ADD CONSTRAINT FK_82440F446E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours ADD CONSTRAINT FK_82440F44B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82440F4462E883B1 ON ue_parcours (ue_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82440F446E38C0DB ON ue_parcours (parcours_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_82440F44B3E9C81 ON ue_parcours (niveau_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX ue_parcours_unique ON ue_parcours (ue_id, parcours_id, niveau_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD preferences_notification JSON DEFAULT NULL, CHANGE name name VARCHAR(60) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE years CHANGE current current TINYINT(1) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE abonnement_notification DROP FOREIGN KEY FK_F3CB4D78A76ED395
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
            ALTER TABLE participant_conversation DROP FOREIGN KEY FK_17A662BB9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation DROP FOREIGN KEY FK_17A662BBA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE abonnement_notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notification_groupe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE participant_conversation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE agenda CHANGE description description VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bibliotheque CHANGE type type VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commentaire CHANGE contenu contenu VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document CHANGE type_document type_document VARCHAR(255) NOT NULL, CHANGE file_path fichier VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ec CHANGE ue_id ue_id INT DEFAULT NULL, CHANGE prof_id prof_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_717E22E312B2DC9C ON etudiant
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE fichier_support CHANGE type type VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL, CHANGE est_publique est_publique TINYINT(1) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_4BDFF36B77153098 ON niveau
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE parcours DROP FOREIGN KEY FK_99B1DEE37A4147F0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_99B1DEE37A4147F0 ON parcours
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours DROP FOREIGN KEY FK_82440F4462E883B1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours DROP FOREIGN KEY FK_82440F446E38C0DB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue_parcours DROP FOREIGN KEY FK_82440F44B3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_82440F4462E883B1 ON ue_parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_82440F446E38C0DB ON ue_parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_82440F44B3E9C81 ON ue_parcours
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX ue_parcours_unique ON ue_parcours
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP preferences_notification, CHANGE name name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE years CHANGE current current SMALLINT NOT NULL
        SQL);
    }
}
