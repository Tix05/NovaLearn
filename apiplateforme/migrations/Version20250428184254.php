<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428184254 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BB3E9C81
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_2E490A9BB3E9C81 ON ue
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue DROP niveau_id
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE ue ADD niveau_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_2E490A9BB3E9C81 ON ue (niveau_id)
        SQL);
    }
}
