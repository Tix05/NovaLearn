<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250415110153 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC87760BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC8777A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC8776E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE agenda ADD CONSTRAINT FK_2CEDC877B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('CREATE INDEX IDX_2CEDC87760BB6FE6 ON agenda (auteur_id)');
        $this->addSql('CREATE INDEX IDX_2CEDC8777A4147F0 ON agenda (mention_id)');
        $this->addSql('CREATE INDEX IDX_2CEDC8776E38C0DB ON agenda (parcours_id)');
        $this->addSql('CREATE INDEX IDX_2CEDC877B3E9C81 ON agenda (niveau_id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE bibliotheque ADD CONSTRAINT FK_4690D34D27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)');
        $this->addSql('CREATE INDEX IDX_4690D34D7A4147F0 ON bibliotheque (mention_id)');
        $this->addSql('CREATE INDEX IDX_4690D34D6E38C0DB ON bibliotheque (parcours_id)');
        $this->addSql('CREATE INDEX IDX_4690D34D27634BEF ON bibliotheque (ec_id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)');
        $this->addSql('CREATE INDEX IDX_67F068BCA76ED395 ON commentaire (user_id)');
        $this->addSql('CREATE INDEX IDX_67F068BC27634BEF ON commentaire (ec_id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_D8698A76A76ED395 ON document (user_id)');
        $this->addSql('ALTER TABLE ec ADD CONSTRAINT FK_8DE8BDFF62E883B1 FOREIGN KEY (ue_id) REFERENCES ue (id)');
        $this->addSql('ALTER TABLE ec ADD CONSTRAINT FK_8DE8BDFFABC1F7FE FOREIGN KEY (prof_id) REFERENCES prof (id)');
        $this->addSql('CREATE INDEX IDX_8DE8BDFF62E883B1 ON ec (ue_id)');
        $this->addSql('CREATE INDEX IDX_8DE8BDFFABC1F7FE ON ec (prof_id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E37A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E36E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3B3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E340C1FEA7 FOREIGN KEY (year_id) REFERENCES years (id)');
        $this->addSql('CREATE INDEX IDX_717E22E3A76ED395 ON etudiant (user_id)');
        $this->addSql('CREATE INDEX IDX_717E22E37A4147F0 ON etudiant (mention_id)');
        $this->addSql('CREATE INDEX IDX_717E22E36E38C0DB ON etudiant (parcours_id)');
        $this->addSql('CREATE INDEX IDX_717E22E3B3E9C81 ON etudiant (niveau_id)');
        $this->addSql('CREATE INDEX IDX_717E22E340C1FEA7 ON etudiant (year_id)');
        $this->addSql('ALTER TABLE fichier_support ADD CONSTRAINT FK_5B97179227634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)');
        $this->addSql('ALTER TABLE fichier_support ADD CONSTRAINT FK_5B97179260BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5B97179227634BEF ON fichier_support (ec_id)');
        $this->addSql('CREATE INDEX IDX_5B97179260BB6FE6 ON fichier_support (auteur_id)');
        $this->addSql('ALTER TABLE prof ADD CONSTRAINT FK_5BBA70BBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE prof ADD CONSTRAINT FK_5BBA70BB7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE prof ADD CONSTRAINT FK_5BBA70BB27634BEF FOREIGN KEY (ec_id) REFERENCES ec (id)');
        $this->addSql('ALTER TABLE prof ADD CONSTRAINT FK_5BBA70BB6E38C0DB FOREIGN KEY (parcours_id) REFERENCES parcours (id)');
        $this->addSql('CREATE INDEX IDX_5BBA70BBA76ED395 ON prof (user_id)');
        $this->addSql('CREATE INDEX IDX_5BBA70BB7A4147F0 ON prof (mention_id)');
        $this->addSql('CREATE INDEX IDX_5BBA70BB27634BEF ON prof (ec_id)');
        $this->addSql('CREATE INDEX IDX_5BBA70BB6E38C0DB ON prof (parcours_id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B7A4147F0 FOREIGN KEY (mention_id) REFERENCES mention (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9B5577AFDB FOREIGN KEY (semestre_id) REFERENCES semestre (id)');
        $this->addSql('ALTER TABLE ue ADD CONSTRAINT FK_2E490A9BB3E9C81 FOREIGN KEY (niveau_id) REFERENCES niveau (id)');
        $this->addSql('CREATE INDEX IDX_2E490A9B7A4147F0 ON ue (mention_id)');
        $this->addSql('CREATE INDEX IDX_2E490A9B5577AFDB ON ue (semestre_id)');
        $this->addSql('CREATE INDEX IDX_2E490A9BB3E9C81 ON ue (niveau_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC87760BB6FE6');
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC8777A4147F0');
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC8776E38C0DB');
        $this->addSql('ALTER TABLE agenda DROP FOREIGN KEY FK_2CEDC877B3E9C81');
        $this->addSql('DROP INDEX IDX_2CEDC87760BB6FE6 ON agenda');
        $this->addSql('DROP INDEX IDX_2CEDC8777A4147F0 ON agenda');
        $this->addSql('DROP INDEX IDX_2CEDC8776E38C0DB ON agenda');
        $this->addSql('DROP INDEX IDX_2CEDC877B3E9C81 ON agenda');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D7A4147F0');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D6E38C0DB');
        $this->addSql('ALTER TABLE bibliotheque DROP FOREIGN KEY FK_4690D34D27634BEF');
        $this->addSql('DROP INDEX IDX_4690D34D7A4147F0 ON bibliotheque');
        $this->addSql('DROP INDEX IDX_4690D34D6E38C0DB ON bibliotheque');
        $this->addSql('DROP INDEX IDX_4690D34D27634BEF ON bibliotheque');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCA76ED395');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC27634BEF');
        $this->addSql('DROP INDEX IDX_67F068BCA76ED395 ON commentaire');
        $this->addSql('DROP INDEX IDX_67F068BC27634BEF ON commentaire');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76A76ED395');
        $this->addSql('DROP INDEX IDX_D8698A76A76ED395 ON document');
        $this->addSql('ALTER TABLE ec DROP FOREIGN KEY FK_8DE8BDFF62E883B1');
        $this->addSql('ALTER TABLE ec DROP FOREIGN KEY FK_8DE8BDFFABC1F7FE');
        $this->addSql('DROP INDEX IDX_8DE8BDFF62E883B1 ON ec');
        $this->addSql('DROP INDEX IDX_8DE8BDFFABC1F7FE ON ec');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3A76ED395');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E37A4147F0');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E36E38C0DB');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3B3E9C81');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E340C1FEA7');
        $this->addSql('DROP INDEX IDX_717E22E3A76ED395 ON etudiant');
        $this->addSql('DROP INDEX IDX_717E22E37A4147F0 ON etudiant');
        $this->addSql('DROP INDEX IDX_717E22E36E38C0DB ON etudiant');
        $this->addSql('DROP INDEX IDX_717E22E3B3E9C81 ON etudiant');
        $this->addSql('DROP INDEX IDX_717E22E340C1FEA7 ON etudiant');
        $this->addSql('ALTER TABLE fichier_support DROP FOREIGN KEY FK_5B97179227634BEF');
        $this->addSql('ALTER TABLE fichier_support DROP FOREIGN KEY FK_5B97179260BB6FE6');
        $this->addSql('DROP INDEX IDX_5B97179227634BEF ON fichier_support');
        $this->addSql('DROP INDEX IDX_5B97179260BB6FE6 ON fichier_support');
        $this->addSql('ALTER TABLE prof DROP FOREIGN KEY FK_5BBA70BBA76ED395');
        $this->addSql('ALTER TABLE prof DROP FOREIGN KEY FK_5BBA70BB7A4147F0');
        $this->addSql('ALTER TABLE prof DROP FOREIGN KEY FK_5BBA70BB27634BEF');
        $this->addSql('ALTER TABLE prof DROP FOREIGN KEY FK_5BBA70BB6E38C0DB');
        $this->addSql('DROP INDEX IDX_5BBA70BBA76ED395 ON prof');
        $this->addSql('DROP INDEX IDX_5BBA70BB7A4147F0 ON prof');
        $this->addSql('DROP INDEX IDX_5BBA70BB27634BEF ON prof');
        $this->addSql('DROP INDEX IDX_5BBA70BB6E38C0DB ON prof');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B7A4147F0');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9B5577AFDB');
        $this->addSql('ALTER TABLE ue DROP FOREIGN KEY FK_2E490A9BB3E9C81');
        $this->addSql('DROP INDEX IDX_2E490A9B7A4147F0 ON ue');
        $this->addSql('DROP INDEX IDX_2E490A9B5577AFDB ON ue');
        $this->addSql('DROP INDEX IDX_2E490A9BB3E9C81 ON ue');
    }
}
