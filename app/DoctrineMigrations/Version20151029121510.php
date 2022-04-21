<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151029121510 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE collect_step_statuses');
        $this->addSql('ALTER TABLE status ADD step_id INT NOT NULL, ADD position INT NOT NULL');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C73B21E9C FOREIGN KEY (step_id) REFERENCES step (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_7B00651C73B21E9C ON status (step_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE collect_step_statuses (collect_step_id INT NOT NULL, status_id INT NOT NULL, INDEX IDX_64238CD3330C62D6 (collect_step_id), INDEX IDX_64238CD36BF700BD (status_id), PRIMARY KEY(collect_step_id, status_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE collect_step_statuses ADD CONSTRAINT FK_64238CD3330C62D6 FOREIGN KEY (collect_step_id) REFERENCES step (id)');
        $this->addSql('ALTER TABLE collect_step_statuses ADD CONSTRAINT FK_64238CD36BF700BD FOREIGN KEY (status_id) REFERENCES status (id)');
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY FK_7B00651C73B21E9C');
        $this->addSql('DROP INDEX IDX_7B00651C73B21E9C ON status');
        $this->addSql('ALTER TABLE status DROP step_id, DROP position');
    }
}
