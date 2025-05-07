<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to update message table
 */
final class Version20250507184021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update message table: modify lu column and rename indexes if necessary';
    }

    public function up(Schema $schema): void
    {
        // Modifier la colonne lu pour ajouter une valeur par défaut
        $this->addSql(<<<'SQL'
            ALTER TABLE message CHANGE lu lu TINYINT(1) DEFAULT 0 NOT NULL
        SQL);

        // Vérifier si les index cibles existent avant de renommer
        $this->addSql(<<<'SQL'
            SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
                                 WHERE TABLE_NAME = 'message' AND INDEX_NAME = 'idx_conversation');
            SET @sql = IF(@index_exists = 0, 
                         'ALTER TABLE message RENAME INDEX idx_b6bd307f9ac0396 TO idx_conversation', 
                         'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);

        $this->addSql(<<<'SQL'
            SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
                                 WHERE TABLE_NAME = 'message' AND INDEX_NAME = 'idx_expediteur');
            SET @sql = IF(@index_exists = 0, 
                         'ALTER TABLE message RENAME INDEX idx_b6bd307f10335f61 TO idx_expediteur', 
                         'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }

    public function down(Schema $schema): void
    {
        // Revenir à l'état initial de la colonne lu
        $this->addSql(<<<'SQL'
            ALTER TABLE message CHANGE lu lu TINYINT(1) NOT NULL
        SQL);

        // Restaurer les anciens noms des index
        $this->addSql(<<<'SQL'
            SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
                                 WHERE TABLE_NAME = 'message' AND INDEX_NAME = 'idx_b6bd307f9ac0396');
            SET @sql = IF(@index_exists = 0, 
                         'ALTER TABLE message RENAME INDEX idx_conversation TO idx_b6bd307f9ac0396', 
                         'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);

        $this->addSql(<<<'SQL'
            SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS 
                                 WHERE TABLE_NAME = 'message' AND INDEX_NAME = 'idx_b6bd307f10335f61');
            SET @sql = IF(@index_exists = 0, 
                         'ALTER TABLE message RENAME INDEX idx_expediteur TO idx_b6bd307f10335f61', 
                         'SELECT 1');
            PREPARE stmt FROM @sql;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        SQL);
    }
}