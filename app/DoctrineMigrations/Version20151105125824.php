<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151105125824 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE opinion_answer (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, INDEX IDX_D39474A5F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE opinion_answer ADD CONSTRAINT FK_D39474A5F675F31B FOREIGN KEY (author_id) REFERENCES fos_user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE opinion ADD answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE opinion ADD CONSTRAINT FK_AB02B027AA334807 FOREIGN KEY (answer_id) REFERENCES opinion_answer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AB02B027AA334807 ON opinion (answer_id)');
        $this->addSql('ALTER TABLE opinion_version ADD answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE opinion_version ADD CONSTRAINT FK_52AD19DDAA334807 FOREIGN KEY (answer_id) REFERENCES opinion_answer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52AD19DDAA334807 ON opinion_version (answer_id)');
        $this->addSql('ALTER TABLE proposal ADD answer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE proposal ADD CONSTRAINT FK_BFE59472AA334807 FOREIGN KEY (answer_id) REFERENCES opinion_answer (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BFE59472AA334807 ON proposal (answer_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE opinion DROP FOREIGN KEY FK_AB02B027AA334807');
        $this->addSql('ALTER TABLE opinion_version DROP FOREIGN KEY FK_52AD19DDAA334807');
        $this->addSql('ALTER TABLE proposal DROP FOREIGN KEY FK_BFE59472AA334807');
        $this->addSql('DROP TABLE opinion_answer');
        $this->addSql('DROP INDEX UNIQ_AB02B027AA334807 ON opinion');
        $this->addSql('ALTER TABLE opinion DROP answer_id');
        $this->addSql('DROP INDEX UNIQ_52AD19DDAA334807 ON opinion_version');
        $this->addSql('ALTER TABLE opinion_version DROP answer_id');
        $this->addSql('DROP INDEX UNIQ_BFE59472AA334807 ON proposal');
        $this->addSql('ALTER TABLE proposal DROP answer_id');
    }
}
