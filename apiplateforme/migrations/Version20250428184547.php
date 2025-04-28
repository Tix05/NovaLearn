<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250428184547 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE INDEX ue_name_idx ON ue (name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ue_code_idx ON ue (code)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP INDEX ue_name_idx ON ue
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX ue_code_idx ON ue
        SQL);
    }
}
