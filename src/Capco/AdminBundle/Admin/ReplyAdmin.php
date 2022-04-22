<?php
namespace Capco\AdminBundle\Admin;

use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Capco\AppBundle\Enum\ProjectVisibilityMode;

class ReplyAdmin extends AbstractAdmin
{
    private $tokenStorage;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->tokenStorage = $tokenStorage;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'author',
                'doctrine_orm_model_autocomplete',
                ['label' => 'admin.fields.reply.author'],
                null,
                ['property' => 'username']
            )
            ->add('updatedAt', null, ['label' => 'admin.fields.reply.updated_at'])
            ->add('questionnaire.step', null, ['label' => 'admin.fields.reply.questionnaire_step'])
            ->add('questionnaire.step.projectAbstractStep.project', null, [
                'label' => 'admin.fields.reply.project',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('id', null, ['label' => 'admin.fields.reply.id'])
            ->add('author', 'sonata_type_model', ['label' => 'admin.fields.reply.author'])
            ->add('published', null, ['label' => 'admin.fields.reply.enabled'])
            ->add('updatedAt', null, ['label' => 'admin.fields.reply.updated_at'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => ['template' => 'CapcoAdminBundle:Reply:list__action_show.html.twig'],
                ],
            ]);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('author', 'sonata_type_model', ['label' => 'admin.fields.reply.author'])
            ->add('updatedAt', null, ['label' => 'admin.fields.reply.updated_at'])
            ->add('responses', null, [
                'label' => 'admin.fields.reply.responses',
                'template' => 'CapcoAdminBundle:Reply:responses_show_field.html.twig',
            ]);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
    }

    /**
     * if user is supper admin return all else return only what I can see
     */
    public function createQuery($context = 'list')
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return parent::createQuery($context);
        }

        /** @var QueryBuilder $query */
        $query = parent::createQuery($context);
        $query
            ->leftJoin($query->getRootAliases()[0] . '.questionnaire', 'q')
            ->leftJoin('q.step', 's')
            ->leftJoin('s.projectAbstractStep', 'pAs')
            ->leftJoin('pAs.project', 'p')
            ->andWhere(
                $query
                    ->expr()
                    ->andX(
                        $query->expr()->eq('p.Author', ':author'),
                        $query->expr()->eq('p.visibility', ProjectVisibilityMode::VISIBILITY_ME)
                    )
            );
        $query->orWhere(
            $query->expr()->gte('p.visibility', ProjectVisibilityMode::VISIBILITY_ADMIN)
        );
        $query->setParameter('author', $user);

        return $query;
    }
}
