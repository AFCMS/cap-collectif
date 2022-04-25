<?php

namespace Capco\AdminBundle\Admin;

use Capco\AppBundle\Elasticsearch\ElasticsearchDoctrineListener;
use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\District\ProjectDistrict;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Enum\ProjectVisibilityMode;
use Capco\AppBundle\Repository\ProjectDistrictRepository;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Capco\AppBundle\Repository\ProposalCommentRepository;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;
use Capco\AppBundle\Toggle\Manager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Required;

final class ProjectAdmin extends CapcoAdmin
{
    protected $classnameLabel = 'project';
    protected $datagridValues = ['_sort_order' => 'DESC', '_sort_by' => 'publishedAt'];

    protected $formOptions = ['cascade_validation' => true];
    private $tokenStorage;
    private $indexer;
    private $projectDistrictRepository;
    private $entityManager;
    private $manager;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        TokenStorageInterface $tokenStorage,
        ProjectDistrictRepository $projectDistrictRepository,
        Indexer $indexer,
        EntityManagerInterface $entityManager,
        Manager $manager
    ) {
        parent::__construct($code, $class, $baseControllerName);
        $this->tokenStorage = $tokenStorage;
        $this->indexer = $indexer;
        $this->projectDistrictRepository = $projectDistrictRepository;
        $this->entityManager = $entityManager;
        $this->manager = $manager;
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        if (empty($object->getAuthors())) {
            $errorElement
                ->with('Author')
                ->addConstraint(new Required())
                ->addViolation('global.required')
                ->end();
        }
    }

    /** @var Project */
    public function preRemove($object)
    {
        foreach ($object->getRealSteps() as $step) {
            if ($step instanceof CollectStep || $step instanceof SelectionStep) {
                $step->setDefaultStatus(null);
            }
        }
        $this->entityManager->flush();

        $this->indexer->remove(\get_class($object), $object->getId());
        $this->indexer->finishBulk();
        parent::preRemove($object);
    }

    public function postUpdate($object)
    {
        /** @var Project $object */
        $container = $this->getConfigurationPool()->getContainer();
        if ($container) {
            $elasticsearchDoctrineListener = $container->get(ElasticsearchDoctrineListener::class);
            // Index project
            $elasticsearchDoctrineListener->addToMessageStack($object);

            // Index comments
            $comments =
                $container->get(ProposalCommentRepository::class)->getCommentsByProject($object) ??
                [];
            if (!empty($comments)) {
                array_map(static function ($comment) use ($elasticsearchDoctrineListener) {
                    $elasticsearchDoctrineListener->addToMessageStack($comment);
                }, $comments);
            }

            // Index Votes
            $selectionVotes =
                $container
                    ->get(ProposalSelectionVoteRepository::class)
                    ->getVotesByProject($object) ?? [];
            $collectVotes =
                $container->get(ProposalCollectVoteRepository::class)->getVotesByProject($object) ??
                [];
            $votes = array_merge($collectVotes, $selectionVotes);
            if (!empty($votes)) {
                array_map(static function ($vote) use ($elasticsearchDoctrineListener) {
                    $elasticsearchDoctrineListener->addToMessageStack($vote);
                }, $votes);
            }
        }
        parent::postUpdate($object);
    }

    // For mosaic view
    public function getObjectMetadata($object)
    {
        $cover = $object->getCover();
        if ($cover) {
            $provider = $this->getConfigurationPool()
                ->getContainer()
                ->get($cover->getProviderName());
            $format = $provider->getFormatName($cover, 'form');
            $url = $provider->generatePublicUrl($cover, $format);

            return new Metadata($object->getTitle(), null, $url);
        }

        return parent::getObjectMetadata($object);
    }

    public function getExportFormats(): array
    {
        return [];
    }

    public function getTemplate($name)
    {
        if ('edit' === $name) {
            return 'CapcoAdminBundle:Project:edit.html.twig';
        }

        return $this->getTemplateRegistry()->getTemplate($name);
    }

    /**
     * if user is supper admin return all else return only what I can see.
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
            ->leftJoin($query->getRootAliases()[0] . '.authors', 'authors')
            ->andWhere(
                $query
                    ->expr()
                    ->andX(
                        $query->expr()->eq('authors.user', ':author'),
                        $query->expr()->eq($query->getRootAliases()[0] . '.visibility', 0)
                    )
            );
        $query->orWhere($query->expr()->gte($query->getRootAliases()[0] . '.visibility', 1));
        $query->setParameter('author', $user);

        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, ['label' => 'global.title'])
            ->add('steps', null, ['label' => 'project.show.meta.step.title'])
            ->add('events', null, ['label' => 'global.events'])
            ->add('posts', null, ['label' => 'global.articles'])
            ->add('visibility', null, ['label' => 'who-can-see-this-project'])
            ->add('publishedAt', null, ['label' => 'global.publication'])
            ->add('updatedAt', null, ['label' => 'global.maj'])
            ->add('opinionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.opinions_threshold',
            ])
            ->add('versionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.versions_threshold',
            ])
            ->add('includeAuthorInRanking', null, [
                'label' => 'admin.fields.project.ranking.include_author',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);
        $listMapper->addIdentifier('title', null, ['label' => 'global.title']);
        if ($this->manager->isActive('themes')) {
            $listMapper->add('themes', null, ['label' => 'global.themes']);
        }

        $actions = [
            'display' => [
                'template' => 'CapcoAdminBundle:Project:list__action_display.html.twig',
            ],
            'download' => [
                'template' => 'CapcoAdminBundle:CRUD:list__action_download.html.twig',
            ],
            'delete' => [
                'template' => 'CapcoAdminBundle:CRUD:list__action_delete.html.twig',
            ],
        ];

        $currentUser = $this->tokenStorage->getToken()->getUser();
        if ($currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $actions['duplicate'] = [
                'template' => 'CapcoAdminBundle:Project:list__action_duplicate.html.twig',
            ];
        }

        $listMapper
            ->add('visibility', ChoiceType::class, [
                'template' => 'CapcoAdminBundle:Project:visibility_list_field.html.twig',
                'choices' => ProjectVisibilityMode::REVERSE_KEY_VISIBILITY,
                'label' => 'project-access',
                'catalogue' => 'CapcoAppBundle',
            ])
            ->add('publishedAt', null, ['label' => 'global.publication'])
            ->add('_action', 'actions', [
                'label' => 'link_actions',
                'actions' => $actions
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $formMapper
            ->with('admin.fields.project.group_meta', ['class' => 'col-md-6'])
            ->end()
            ->with('admin.fields.project.group_ranking', ['class' => 'col-md-6'])
            ->end()
            ->with('admin.fields.project.group_steps', ['class' => 'col-md-12'])
            ->end()
            ->with('project-access', ['class' => 'col-md-6'])
            ->end()
            ->with('admin.fields.project.advanced', ['class' => 'col-md-6'])
            ->end();
        if ($currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper->with('group.admin.parameters', ['class' => 'col-md-6'])->end();
        }
        $formMapper
            ->end()
            ->with('admin.fields.project.group_meta')
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'global.publication',
                'required' => true,
                'format' => 'dd/MM/yyyy HH:mm',
                'attr' => ['data-date-format' => 'DD/MM/YYYY HH:mm'],
            ]);

        if ($this->manager->isActive('themes')) {
            $formMapper->add('themes', ModelType::class, [
                'label' => 'global.themes',
                'required' => false,
                'multiple' => true,
                'by_reference' => false,
            ]);
        }

        // Ranking
        // Steps
        $formMapper
            ->add(
                'Cover',
                ModelListType::class,
                ['required' => false, 'label' => 'global.image'],
                [
                    'link_parameters' => [
                        'context' => 'default',
                        'hide_context' => true,
                        'provider' => 'sonata.media.provider.image',
                    ],
                ]
            )
            ->add(
                'video',
                null,
                [
                    'label' => 'admin.fields.project.video',
                    'required' => false,
                    'help' => 'admin.help.project.video',
                ],
                ['link_parameters' => ['context' => 'project']]
            );
        if ($this->subject) {
            $formMapper->add('districts', EntityType::class, [
                'mapped' => false,
                'class' => ProjectDistrict::class,
                'required' => false,
                'label' => 'proposal_form.districts',
                'data' => $this->projectDistrictRepository
                    ->createQueryBuilder('d')
                    ->leftJoin('d.projectDistrictPositioners', 'positioner')
                    ->andWhere('positioner.project = :project')
                    ->setParameter('project', $this->subject->getId())
                    ->orderBy('positioner.position', 'ASC')
                    ->getQuery()
                    ->getResult(),
                'choices' => $this->projectDistrictRepository->findAllOrderedByPosition(
                    $this->subject->getId()
                ),
                'multiple' => true,
                'choice_label' => function (ProjectDistrict $district) {
                    return $district->getName();
                },
            ]);
        }
        $formMapper
            ->end()
            ->with('admin.fields.project.group_ranking')
            ->add('opinionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.opinions_threshold',
                'required' => false,
            ])
            ->add('versionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.versions_threshold',
                'required' => false,
            ])
            ->add('includeAuthorInRanking', null, [
                'label' => 'admin.fields.project.ranking.include_author',
                'required' => false,
            ])
            ->end()
            ->with('admin.fields.project.group_steps')
            ->add(
                'steps',
                CollectionType::class,
                [
                    'label' => 'project.show.meta.step.title',
                    'by_reference' => false,
                    'required' => false,
                    'type_options' => [
                        'delete' => true,
                        'delete_options' => [
                            'type' => CheckboxType::class,
                            'type_options' => [
                                'mapped' => false,
                                'required' => false,
                                // we need to pass an empty label, without we got an error 500
                                // where is the logic, idk, this is sonata
                                'label' => '',
                            ],
                        ],
                    ],
                ],
                ['edit' => 'inline', 'inline' => 'table', 'sortable' => 'position']
            )
            ->end();

        $formMapper
            ->with('project-access')
            ->add('visibility', ChoiceType::class, [
                'choices' => ProjectVisibilityMode::VISIBILITY_WITH_HELP_TEXT,
                'label' => 'who-can-see-this-project',
                'multiple' => false,
                'expanded' => true,
                'required' => true,

                'choice_translation_domain' => 'CapcoAppBundle',
                'attr' => ['class' => 'project-visibility-selector'],
            ])
            ->add('restrictedViewerGroups', null, [
                'attr' => ['class' => 'project-visibility-group-selector'],
            ])
            ->end()
            ->with('admin.fields.project.advanced')
            ->add('metaDescription', null, [
                'label' => 'global.meta.description',
                'required' => false,
                'help' => 'admin.help.metadescription',
            ])
            ->end();
        if ($currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $formMapper->with('group.admin.parameters');
            $formMapper->add('opinionCanBeFollowed', null, [
                'label' => 'enable-proposal-tracking',
                'required' => false,
            ]);
            $formMapper->end();
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->with('admin.fields.project.general')->end();

        $showMapper
            ->with('admin.fields.project.general')
            ->add('title', null, ['label' => 'global.title'])
            ->add('visibility', null, ['label' => 'who-can-see-this-project'])
            ->add('publishedAt', null, ['label' => 'global.publication'])
            ->add('Cover', null, [
                'template' => 'CapcoAdminBundle:Project:cover_show_field.html.twig',
                'label' => 'global.image',
            ])
            ->add('video', null, ['label' => 'admin.fields.project.video']);

        if ($this->manager->isActive('themes')) {
            $showMapper->add('themes', null, ['label' => 'global.themes']);
        }

        $showMapper
            ->add('steps', null, ['label' => 'project.show.meta.step.title'])
            ->add('events', null, ['label' => 'global.events'])
            ->add('posts', null, ['label' => 'global.articles'])
            ->add('createdAt', null, ['label' => 'global.creation'])
            ->add('updatedAt', null, ['label' => 'global.maj'])
            ->add('opinionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.opinions_threshold',
            ])
            ->add('versionsRankingThreshold', null, [
                'label' => 'admin.fields.project.ranking.versions_threshold',
            ])
            ->add('includeAuthorInRanking', null, [
                'label' => 'admin.fields.project.ranking.include_author',
            ])
            ->end();
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('duplicate');
        $collection->clearExcept(['batch', 'list', 'edit', 'delete', 'show', 'duplicate']);
        $collection->add('preview', $this->getRouterIdParameter() . '/preview');
    }
}
