<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519170228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE etudiant_examen_statut (id INT AUTO_INCREMENT NOT NULL, etudiant_id INT NOT NULL, examen_id INT NOT NULL, statut VARCHAR(20) NOT NULL, debut_examen DATETIME DEFAULT NULL, temps_restant INT DEFAULT NULL, reponses JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_24698484DDEAB1A3 (etudiant_id), INDEX IDX_246984845C8659A (examen_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant_examen_statut ADD CONSTRAINT FK_24698484DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant_examen_statut ADD CONSTRAINT FK_246984845C8659A FOREIGN KEY (examen_id) REFERENCES examen (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant_examen_statut DROP FOREIGN KEY FK_24698484DDEAB1A3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE etudiant_examen_statut DROP FOREIGN KEY FK_246984845C8659A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE etudiant_examen_statut
        SQL);
    }
}
