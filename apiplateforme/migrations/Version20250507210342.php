<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507210342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E927634BEF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A8E26E927634BEF ON conversation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP ec_id, DROP created_at, DROP updated_at, CHANGE created_by_id created_by_id INT NOT NULL, CHANGE sujet sujet VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(50) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX participant_unique ON participant_conversation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation DROP date_dernier_vu, DROP created_at, DROP updated_at, CHANGE role role VARCHAR(50) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD ec_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE created_by_id created_by_id INT DEFAULT NULL, CHANGE sujet sujet VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E927634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A8E26E927634BEF ON conversation (ec_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE participant_conversation ADD date_dernier_vu DATETIME DEFAULT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE role role VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX participant_unique ON participant_conversation (conversation_id, user_id)
        SQL);
    }
}
