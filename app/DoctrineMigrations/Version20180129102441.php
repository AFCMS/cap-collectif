<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180129102441 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $this->connection->delete('site_image', ['keyname' => 'image.picto']);
        $this->write('-> Deleted old "image.picto" from site_image parameter. Now replaced by homepage.picto');
    }

    public function postDown(Schema $schema)
    {
        $this->connection->insert('site_image', [
            'keyname' => 'image.picto',
            'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updated_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            'is_enabled' => true,
            'position' => 3,
            'category' => 'pages.homepage',
            'is_social_network_thumbnail' => false
        ]);
        $this->write('-> Reverted "image.picto" into site_image parameter.');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
