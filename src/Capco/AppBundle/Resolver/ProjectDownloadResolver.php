<?php
namespace Capco\AppBundle\Resolver;

use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\Responses\MediaResponse;
use Capco\AppBundle\Entity\Responses\ValueResponse;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\QuestionnaireStep;
use Capco\AppBundle\Helper\EnvHelper;
use Capco\AppBundle\Utils\Map;
use Capco\AppBundle\Utils\Text;
use Doctrine\ORM\EntityManager;
use Liuggio\ExcelBundle\Factory;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Symfony\Bridge\Twig\Extension\HttpFoundationExtension;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\TranslatorInterface;

class ProjectDownloadResolver
{
    protected $em;
    protected $translator;
    protected $urlArrayResolver;
    protected $phpexcel;
    protected $headers;
    protected $data;
    protected $instanceName;
    protected $withVote;
    protected $mediaExtension;
    protected $customFields;
    protected $httpFoundExtension;

    public function __construct(
        EntityManager $em,
        TranslatorInterface $translator,
        UrlArrayResolver $urlArrayResolver,
        Factory $phpexcel,
        MediaExtension $mediaExtension,
        HttpFoundationExtension $httpFoundationExtension
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->urlArrayResolver = $urlArrayResolver;
        $this->phpexcel = $phpexcel;
        $this->headers = [];
        $this->customFields = [];
        $this->data = [];
        $this->instanceName = EnvHelper::get('SYMFONY_INSTANCE_NAME');
        $this->mediaExtension = $mediaExtension;
        $this->httpFoundExtension = $httpFoundationExtension;
    }

    public function getQuestionnaireStepHeaders(QuestionnaireStep $step): array
    {
        $headers = [
            'id',
            'published',
            'author',
            'author_id',
            'author_email',
            'phone',
            'created',
            'anonymous',
        ];

        if ($step->getQuestionnaire()) {
            foreach ($step->getQuestionnaire()->getRealQuestions() as $question) {
                $headers[] = ['label' => Text::unslug($question->getSlug()), 'raw' => true];
            }
        }

        return $headers;
    }

    public function getContent(
        AbstractStep $step,
        bool $withVote = false
    ): \PHPExcel_Writer_IWriter {
        if (!$step) {
            throw new NotFoundHttpException('Step not found');
        }

        if ($step instanceof QuestionnaireStep) {
            $this->headers = $this->getQuestionnaireStepHeaders($step);
            $data = $this->getQuestionnaireStepData($step);
        } else {
            throw new \InvalidArgumentException('Step must be of type collect or questionnaire');
        }
        $title = $step->getProject() ? $step->getProject()->getTitle() . '_' : '';
        $title .= $step->getTitle();

        foreach ($data as &$d) {
            foreach ($d as $key => $value) {
                $d[$key] = $this->formatText($value);
            }
        }

        return $this->getWriterFromData($data, $this->headers, $title);
    }

    /*
     * Add item in correct section
     */
    public function addItemToData($item)
    {
        $this->data[] = $item;
    }

    public function getQuestionnaireStepData(QuestionnaireStep $questionnaireStep): array
    {
        $this->data = [];
        $replies = [];

        if ($questionnaireStep->getQuestionnaire()) {
            // Replies
            $replies = $this->em->getRepository(
                'CapcoAppBundle:Reply'
            )->getEnabledByQuestionnaireAsArray($questionnaireStep->getQuestionnaire());
        }

        $this->getRepliesData($replies);

        foreach ($this->data as &$answers) {
            foreach ($answers as $key => $value) {
                $answers[$key] = $this->formatText($value);
            }
        }

        return $this->data;
    }

    public function getRepliesData($replies)
    {
        foreach ($replies as $reply) {
            if ($reply['published']) {
                $responses = $this->em->getRepository(
                    'CapcoAppBundle:Responses\AbstractResponse'
                )->getByReplyAsArray($reply['id']);
                $this->addItemToData($this->getReplyItem($reply, $responses));
            }
        }
    }

    // *************************** Generate items *******************************************

    private function getReplyItem(array $reply, array $responses): array
    {
        $item = [
            'id' => $reply['id'],
            'published' => $reply['published'],
            'author' => $reply['author']['username'],
            'author_id' => $reply['author']['id'],
            'author_email' => $reply['author']['email'],
            'phone' => $reply['author']['phone'] ? (string) $reply['author']['phone'] : '',
            'created' => $this->dateToString($reply['createdAt']),
            'anonymous' => $this->booleanToString($reply['private']),
        ];

        foreach ($responses as $response) {
            $question = $response['question'];
            $item[Text::unslug($question['slug'])] = $this->getResponseValue($response);
        }

        foreach ($this->headers as $header) {
            if (\is_array($header) && !array_key_exists($header['label'], $item)) {
                $item[$header['label']] = '';
            }
        }

        return $item;
    }

    private function getResponseValue(array $response)
    {
        $originalValue = $response['value'];
        if (\is_array($originalValue)) {
            $values = $originalValue['labels'];
            if (array_key_exists('other', $originalValue) && $originalValue['other']) {
                $values[] = $originalValue['other'];
            }

            return implode(';', $values);
        }

        return $originalValue;
    }

    private function getWriterFromData($data, $headers, $title): \PHPExcel_Writer_IWriter
    {
        $phpExcelObject = $this->phpexcel->createPHPExcelObject();
        $phpExcelObject->getProperties()->setTitle($title);
        $phpExcelObject->setActiveSheetIndex();
        $sheet = $phpExcelObject->getActiveSheet();
        $sheet->setTitle(
            $this->translator->trans('project_download.sheet.title', [], 'CapcoAppBundle')
        );
        \PHPExcel_Settings::setCacheStorageMethod(
            \PHPExcel_CachedObjectStorageFactory::cache_in_memory,
            ['memoryCacheSize' => '512M']
        );
        $nbCols = \count($headers);
        // Add headers
        [$startColumn, $startRow] = \PHPExcel_Cell::coordinateFromString();
        $currentColumn = $startColumn;
        foreach ($headers as $header) {
            if (\is_array($header)) {
                $header = $header['label'];
            } elseif (!\in_array($header, $this->customFields, true)) {
                $header = $this->translator->trans(
                    'project_download.label.' . $header,
                    [],
                    'CapcoAppBundle'
                );
            }
            $sheet->setCellValueExplicit($currentColumn . $startRow, $header);
            ++$currentColumn;
        }
        [$startColumn, $startRow] = \PHPExcel_Cell::coordinateFromString('A2');
        $currentRow = $startRow;
        // Loop through data
        foreach ($data as $row) {
            $currentColumn = $startColumn;
            for ($i = 0; $i < $nbCols; ++$i) {
                $headerKey = \is_array($headers[$i]) ? $headers[$i]['label'] : $headers[$i];
                $sheet->setCellValue($currentColumn . $currentRow, $row[$headerKey]);
                ++$currentColumn;
            }
            ++$currentRow;
        }

        // create the writer
        return $this->phpexcel->createWriter($phpExcelObject, 'Excel2007');
    }

    private function initCustomFieldsInHeader(ProposalForm $proposalForm): void
    {
        $this->customFields = [];
        foreach ($proposalForm->getQuestions() as $question) {
            $title = $question->getQuestion()->getTitle();
            $this->customFields[] = $title;
        }

        if ($proposalForm->getEvaluationForm()) {
            foreach ($proposalForm->getEvaluationForm()->getRealQuestions() as $question) {
                $this->customFields[] = $question->getTitle();
            }
        }

        $this->headers = array_merge($this->headers, $this->customFields);
    }

    private function booleanToString($boolean): string
    {
        if ($boolean) {
            return $this->translator->trans('project_download.values.yes', [], 'CapcoAppBundle');
        }

        return $this->translator->trans('global.no', [], 'CapcoAppBundle');
    }

    private function dateToString(\DateTime $date = null): string
    {
        if ($date) {
            return $date->format('Y-m-d H:i:s');
        }

        return '';
    }

    private function formatText($text)
    {
        $oneBreak = ['<br>', '<br/>', '&nbsp;'];
        $twoBreaks = ['</p>'];
        $text = str_ireplace($oneBreak, "\r", $text);
        $text = str_ireplace($twoBreaks, "\r\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES);

        return $text;
    }
}
