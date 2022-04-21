<?php

namespace Application\Migrations;

use Capco\AppBundle\Entity\Menu;
use Capco\AppBundle\Entity\MenuItem;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150213174404 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema)
    {
        $toggleManager = $this->container->get('capco.toggle.manager');
        $toggleManager->activate('themes');

        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT mi.id FROM Capco\AppBundle\Entity\MenuItem mi WHERE mi.link = :link AND mi.isDeletable = :isDeletable");
        $query->setParameter('link','themes');
        $query->setParameter('isDeletable', false);
        $themeMI = $query->getOneOrNullResult();

        if (null == $themeMI) {
            $query = $em->createQuery("SELECT m.id FROM Capco\AppBundle\Entity\Menu m WHERE m.type = :type");
            $query->setParameter('type',1);
            $header = $query->getOneOrNullResult();

            // If we havn't a header yet, we want to get one
            if (null === $header) {
                $this->connection->insert('menu', array('type' => 1));
                $headerId = $this->connection->lastInsertId();
            } else {
                $headerId = $header['id'];
            }

            $date = (new \DateTime())->format('Y-m-d H:i:s');
            $this->connection->insert('menu_item', array('title' => 'Thèmes', 'link' => 'themes', 'is_enabled' => true, 'is_deletable' => false, 'isFullyModifiable' => false, 'position' => 2, 'parent_id' => null, 'menu_id' => $headerId, 'created_at' => $date, 'updated_at' => $date));
        }

        $this->connection->update('menu_item', array('associated_features' => 'themes'), array('link' => 'themes', 'is_deletable' => false));
    }


    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postDown(Schema $schema)
    {

        $em = $this->container->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("SELECT mi.id FROM Capco\AppBundle\Entity\MenuItem mi WHERE mi.link = :link AND mi.isDeletable = :isDeletable");
        $query->setParameter('link','themes');
        $query->setParameter('isDeletable', false);
        $themeMI = $query->getOneOrNullResult();

        if (null != $themeMI) {
            $this->connection->update('menu_item', array('associated_features' => 'themes'), array('id' => $themeMI['id']));
        }
    }
}
