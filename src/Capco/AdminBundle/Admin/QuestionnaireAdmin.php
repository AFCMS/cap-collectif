<?php
namespace Capco\AdminBundle\Admin;

use Doctrine\DBAL\Query\QueryBuilder;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Capco\AppBundle\Enum\ProjectVisibilityMode;

class QuestionnaireAdmin extends CapcoAdmin
{
    protected $datagridValues = ['_sort_order' => 'ASC', '_sort_by' => 'title'];

    protected $formOptions = ['cascade_validation' => true];
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
    public function preUpdate($object)
    {
        // We must make sure a question position by questionnaire is unique
        $questionRepo = $this->getConfigurationPool()
            ->getContainer()
            ->get('capco.questionnaire_abstract_question.repository');
        $delta = $questionRepo->getCurrentMaxPositionForQuestionnaire($object->getId());

        foreach ($object->getQuestions() as $question) {
            $question->setPosition($question->getPosition() + $delta);
        }
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('admin.fields.questionnaire.group_general')
            ->add('title', null, ['label' => 'admin.fields.questionnaire.title'])
            ->add('description', CKEditorType::class, [
                'label' => 'admin.fields.questionnaire.description',
                'config_name' => 'admin_editor',
                'required' => false,
            ])
            ->end();
        $formMapper
            ->with('admin.fields.questionnaire.group_questions')
            ->add(
                'questions',
                'sonata_type_collection',
                [
                    'label' => 'admin.fields.questionnaire.questions',
                    'by_reference' => false,
                    'required' => false,
                ],
                ['edit' => 'inline', 'inline' => 'table', 'sortable' => 'position']
            )
            ->end();
        $formMapper
            ->with('user.profile.notifications.title')
            ->add('acknowledgeReplies', CheckboxType::class, [
                'label' => 'admin.fields.questionnaire.acknowledge_replies',
                'required' => false,
            ])
            ->end()
            ->with('proposal_form.admin.settings.options')
            ->add('anonymousAllowed', CheckboxType::class, [
                'label' => 'reply-anonymously',
                'required' => false,
            ])
            ->add('multipleRepliesAllowed', CheckboxType::class, [
                'label' => 'answer-several-times',
                'required' => false,
            ])
            ->end()
            ->with('requirements')
            ->add('phoneConfirmation', CheckboxType::class, [
                'label' => 'phone-number-verified-by-sms',
                'required' => false,
            ]);
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, ['label' => 'admin.fields.questionnaire.title'])
            ->add('updatedAt', null, ['label' => 'admin.fields.questionnaire.updated_at']);
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper
            ->addIdentifier('title', null, ['label' => 'admin.fields.questionnaire.title'])
            ->add('updatedAt', null, ['label' => 'admin.fields.questionnaire.updated_at'])
            ->add('_action', 'actions', [
                'actions' => ['show' => [], 'edit' => [], 'delete' => []],
            ]);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('title', null, ['label' => 'admin.fields.questionnaire.title'])
            ->add('enabled', null, ['label' => 'admin.fields.questionnaire.enabled'])
            ->add('createdAt', null, ['label' => 'admin.fields.questionnaire.updated_at'])
            ->add('updatedAt', null, ['label' => 'admin.fields.questionnaire.updated_at']);
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
            ->leftJoin($query->getRootAliases()[0] . '.step', 's')
            ->leftJoin('s.projectAbstractStep', 'pAs')
            ->leftJoin('pAs.project', 'p')
            ->orWhere(
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
