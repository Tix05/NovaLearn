<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250531205954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD ec_id INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL, CHANGE created_by_id created_by INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E927634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A8E26E9DE12AB56 ON conversation (created_by)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_8A8E26E927634BEF ON conversation (ec_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E9DE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP FOREIGN KEY FK_8A8E26E927634BEF
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A8E26E9DE12AB56 ON conversation
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_8A8E26E927634BEF ON conversation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE conversation DROP ec_id, DROP created_at, DROP updated_at, CHANGE created_by created_by_id INT NOT NULL
        SQL);
    }
}
