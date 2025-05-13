<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250512174543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE correction_examen (id INT AUTO_INCREMENT NOT NULL, examen_id INT NOT NULL, etudiant_id INT NOT NULL, note_totale DOUBLE PRECISION NOT NULL, fichier_rapport VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A75199CC5C8659A (examen_id), INDEX IDX_A75199CCDDEAB1A3 (etudiant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE examen (id INT AUTO_INCREMENT NOT NULL, ec_id INT NOT NULL, auteur_id INT NOT NULL, agenda_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, fichier VARCHAR(255) DEFAULT NULL, statut VARCHAR(20) NOT NULL, type VARCHAR(20) NOT NULL, duree INT NOT NULL, date_creation DATETIME NOT NULL, date_publication DATETIME DEFAULT NULL, date_expiration DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_514C8FEC27634BEF (ec_id), INDEX IDX_514C8FEC60BB6FE6 (auteur_id), INDEX IDX_514C8FECEA67784A (agenda_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE option_question (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, texte VARCHAR(255) NOT NULL, valeur VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_968038EA1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, examen_id INT NOT NULL, texte LONGTEXT NOT NULL, type VARCHAR(20) NOT NULL, reponse_correcte LONGTEXT DEFAULT NULL, points INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B6F7494E5C8659A (examen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reponse_etudiant (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, etudiant_id INT NOT NULL, examen_id INT NOT NULL, valeur LONGTEXT NOT NULL, note DOUBLE PRECISION DEFAULT NULL, commentaire LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_2EF2AD941E27F6BF (question_id), INDEX IDX_2EF2AD94DDEAB1A3 (etudiant_id), INDEX IDX_2EF2AD945C8659A (examen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction_examen ADD CONSTRAINT FK_A75199CC5C8659A FOREIGN KEY (examen_id) REFERENCES examen (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction_examen ADD CONSTRAINT FK_A75199CCDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen ADD CONSTRAINT FK_514C8FEC27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen ADD CONSTRAINT FK_514C8FEC60BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen ADD CONSTRAINT FK_514C8FECEA67784A FOREIGN KEY (agenda_id) REFERENCES agenda (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE option_question ADD CONSTRAINT FK_968038EA1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question ADD CONSTRAINT FK_B6F7494E5C8659A FOREIGN KEY (examen_id) REFERENCES examen (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant ADD CONSTRAINT FK_2EF2AD941E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant ADD CONSTRAINT FK_2EF2AD94DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant ADD CONSTRAINT FK_2EF2AD945C8659A FOREIGN KEY (examen_id) REFERENCES examen (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE correction_examen DROP FOREIGN KEY FK_A75199CC5C8659A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE correction_examen DROP FOREIGN KEY FK_A75199CCDDEAB1A3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen DROP FOREIGN KEY FK_514C8FEC27634BEF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen DROP FOREIGN KEY FK_514C8FEC60BB6FE6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE examen DROP FOREIGN KEY FK_514C8FECEA67784A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE option_question DROP FOREIGN KEY FK_968038EA1E27F6BF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E5C8659A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant DROP FOREIGN KEY FK_2EF2AD941E27F6BF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant DROP FOREIGN KEY FK_2EF2AD94DDEAB1A3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse_etudiant DROP FOREIGN KEY FK_2EF2AD945C8659A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE correction_examen
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE examen
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE option_question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE question
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reponse_etudiant
        SQL);
    }
}
