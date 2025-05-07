<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429123529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add foreign key constraint to semestre table';
    }

    public function up(Schema $schema): void
    {
        // Vérifier si la contrainte existe avant de l'ajouter
        $this->addSql(<<<'SQL'
            SET @constraint_exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
                                     WHERE TABLE_NAME = 'semestre' AND CONSTRAINT_NAME = 'FK_SEMESTRE_NIVEAU');
            SET @sql = IF(@constraint_exists = 0, 
                         'ALTER TABLE semestre ADD CONSTRAINT FK_SEMESTRE_NIVEAU FOREIGN KEY (niveau_id) REFERENCES niveau (id)', 
                         'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE semestre DROP FOREIGN KEY FK_SEMESTRE_NIVEAU');
    }
}