<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180202163622 extends AbstractMigration
{

    protected $siteParamer = [
        'keyname' => 'homepage.meta_description',
        'category' => 'pages.homepage',
        'value' => "",
        'position' => 101,
        'is_social_network_description' => true,
        'type' => 0,
    ];

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $this->connection->delete('site_parameter', ['keyname' => 'homepage.meta_description']);
        $this->write('-> Removed old "homepage.meta_description". Now using "homepage.metadescription"');
    }

    public function postDown(Schema $schema)
    {
        $this->connection->insert('site_parameter', $this->siteParamer);
        $this->write('-> Re-added old "homepage.meta_description"');
    }
}
