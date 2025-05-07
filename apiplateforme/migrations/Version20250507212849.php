<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507212849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX idx_conversation_date ON message
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message DROP created_at, DROP updated_at, CHANGE lu lu TINYINT(1) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME INDEX idx_conversation TO IDX_B6BD307F9AC0396
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME INDEX idx_expediteur TO IDX_B6BD307F10335F61
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE message ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE lu lu TINYINT(1) DEFAULT 0 NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_conversation_date ON message (conversation_id, date_envoi)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME INDEX idx_b6bd307f10335f61 TO idx_expediteur
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE message RENAME INDEX idx_b6bd307f9ac0396 TO idx_conversation
        SQL);
    }
}
