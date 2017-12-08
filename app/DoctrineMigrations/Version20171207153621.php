<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171207153621 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site_image ADD is_social_network_thumbnail TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE site_parameter ADD is_social_network_description TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE project ADD meta_description VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE theme ADD meta_description VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE step ADD meta_description VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE page ADD meta_description VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD meta_description VARCHAR(160) DEFAULT NULL');
        $this->addSql('ALTER TABLE blog_post ADD meta_description VARCHAR(160) DEFAULT NULL');

        $this->addSql('ALTER TABLE page ADD cover_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620922726E9 FOREIGN KEY (cover_id) REFERENCES media__media (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_140AB620922726E9 ON page (cover_id)');
    }

    public function postUp(Schema $schema)
    {
        $this->setPosition('ideas.jumbotron.title', 1);
        $this->setPosition('ideas.jumbotron.body', 2);
        $this->setPosition('ideas.content.body', 3);
        $this->setPosition('ideas.pagination', 4);
        $this->setPosition('ideas.metadescription', 5);

        $this->setPosition('blog.jumbotron.title', 1);
        $this->setPosition('blog.jumbotron.body', 2);
        $this->setPosition('blog.content.body', 3);
        $this->setPosition('blog.pagination.size', 4);
        $this->setPosition('blog.metadescription', 5);

        $this->setPosition('events.jumbotron.title', 1);
        $this->setPosition('events.jumbotron.body', 2);
        $this->setPosition('events.content.body', 3);
        $this->setPosition('events.metadescription', 4);

        $this->setPosition('members.jumbotron.title', 1);
        $this->setPosition('members.jumbotron.body', 2);
        $this->setPosition('members.content.body', 3);
        $this->setPosition('members.pagination.size', 4);
        $this->setPosition('members.metadescription', 5);

        $this->setPosition('contact.jumbotron.title', 1);
        $this->setPosition('contact.jumbotron.body', 2);
        $this->setPosition('contact.content.body', 3);
        $this->setPosition('contact.content.phone_number', 4);
        $this->setPosition('admin.mail.contact', 5);
        $this->setPosition('contact.metadescription', 6);

        $this->setPosition('ideas.trashed.jumbotron.title', 1);
        $this->setPosition('ideas.trashed.content.body', 2);
        $this->setPosition('ideas_trash.metadescription', 3);
    }

    private function setPosition($keyname, $position)
    {
        echo "-> Updating $keyname with position $position " . PHP_EOL;
        echo $this->connection->update('site_parameter', ['position' => $position], ['keyname' => $keyname]) . ' rows updated';
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE site_image DROP is_social_network_thumbnail');
        $this->addSql('ALTER TABLE site_parameter DROP is_social_network_description');
        $this->addSql('ALTER TABLE blog_post DROP meta_description');
        $this->addSql('ALTER TABLE event DROP meta_description');
        $this->addSql('ALTER TABLE page DROP meta_description');
        $this->addSql('ALTER TABLE step DROP meta_description');
        $this->addSql('ALTER TABLE theme DROP meta_description');
        $this->addSql('ALTER TABLE project DROP meta_description');

        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620922726E9');
        $this->addSql('DROP INDEX IDX_140AB620922726E9 ON page');
        $this->addSql('ALTER TABLE page DROP cover_id');
    }
}
