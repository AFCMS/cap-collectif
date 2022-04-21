<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150220180550 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $parameters = array(
            'login.text.top',
            'login.text.bottom',
            'signin.text.top',
            'signin.text.bottom',
        );

        foreach ($parameters as $keyname) {
            $this->connection->update('site_parameter', array('type' => 1), array('keyname' => $keyname));
        }
    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
