<?php

namespace Capco\AppBundle\Resolver;

use Capco\AppBundle\Utils\Text;
use Liuggio\ExcelBundle\Factory;
use Capco\AppBundle\Entity\Reply;
use Doctrine\ORM\EntityManagerInterface;
use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Command\Utils\ExportUtils;
use Overblog\GraphQLBundle\Definition\Argument;
use Capco\AppBundle\Entity\Responses\MediaResponse;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Capco\AppBundle\GraphQL\Resolver\Media\MediaUrlResolver;
use Capco\AppBundle\GraphQL\Resolver\Questionnaire\QuestionnaireExportResultsUrlResolver;

class ProjectDownloadResolver
{
    protected $em;
    protected $translator;
    protected $urlArrayResolver;
    protected $urlResolver;
    protected $phpexcel;
    protected $headers;
    protected $data;
    protected $withVote;
    protected $customFields;
    protected $httpFoundExtension;
    private $exportUrlResolver;

    public function __construct(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        UrlArrayResolver $urlArrayResolver,
        MediaUrlResolver $urlResolver,
        Factory $phpexcel,
        QuestionnaireExportResultsUrlResolver $exportUrlResolver
    ) {
        $this->em = $em;
        $this->translator = $translator;
        $this->urlArrayResolver = $urlArrayResolver;
        $this->urlResolver = $urlResolver;
        $this->phpexcel = $phpexcel;
        $this->headers = [];
        $this->customFields = [];
        $this->data = [];
        $this->exportUrlResolver = $exportUrlResolver;
    }

    public function getQuestionnaireHeaders(Questionnaire $questionnaire): array
    {
        $headers = [
            'id',
            'published',
            'author',
            'author_id',
            'author_email',
            'phone',
            'created',
            'updated',
            'anonymous',
            'draft',
        ];

        foreach ($questionnaire->getRealQuestions() as $question) {
            $headers[] = ['label' => Text::unslug($question->getSlug()), 'raw' => true];
        }

        return $headers;
    }

    public function getContent(
        Questionnaire $questionnaire,
        ExportUtils $exportUtils
    ): \PHPExcel_Writer_IWriter {
        $this->headers = $this->getQuestionnaireHeaders($questionnaire);
        $data = $this->getQuestionnaireData($questionnaire);
        $title = $this->exportUrlResolver->getFileName($questionnaire);

        foreach ($data as &$d) {
            foreach ($d as $key => $value) {
                $d[$key] = $exportUtils->parseCellValue($this->formatText($value));
            }
        }

        return $this->getWriterFromData($data, $this->headers, $title);
    }

    // Add item in correct section
    public function addItemToData($item): void
    {
        $this->data[] = $item;
    }

    public function getQuestionnaireData(Questionnaire $questionnaire): array
    {
        $this->data = [];
        $replies = $this->em
            ->getRepository(Reply::class)
            ->getEnabledByQuestionnaireAsArray($questionnaire);

        $this->getRepliesData($replies);

        foreach ($this->data as &$answers) {
            foreach ($answers as $key => $value) {
                $answers[$key] = $this->formatText($value);
            }
        }

        return $this->data;
    }

    public function getRepliesData(iterable $replies): void
    {
        foreach ($replies as $reply) {
            $responses = $this->em
                ->getRepository(AbstractResponse::class)
                ->getByReplyAsArray($reply['id']);
            $this->addItemToData($this->getReplyItem($reply, $responses));
        }
    }

    public function formatText($text): string
    {
        $oneBreak = ['<br>', '<br/>', '&nbsp;'];
        $twoBreaks = ['</p>'];
        $text = str_ireplace($oneBreak, "\r", $text);
        $text = str_ireplace($twoBreaks, "\r\n", $text);
        $text = strip_tags($text);

        return html_entity_decode($text, ENT_QUOTES);
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
            'updated' => $this->dateToString($reply['updatedAt']),
            'anonymous' => $this->booleanToString($reply['private']),
            'draft' => $this->booleanToString($reply['draft']),
        ];

        foreach ($responses as $response) {
            $question = $response['question'];
            $item[Text::unslug($question['slug'])] = $this->getResponseValue($response);
        }

        foreach ($this->headers as $header) {
            if (\is_array($header) && !isset($item[$header['label']])) {
                $item[$header['label']] = '';
            }
        }

        return $item;
    }

    private function getResponseValue(array $response)
    {
        $responseMedia = null;
        $mediasUrl = [];
        if ('media' === $response['response_type']) {
            $responseMedia = $this->em->getRepository(MediaResponse::class)->findOneBy([
                'id' => $response['id'],
            ]);

            foreach ($responseMedia->getMedias() as $media) {
                $mediasUrl[] = $this->urlResolver->__invoke(
                    $media,
                    new Argument(['format' => 'reference'])
                );
            }
        }

        $originalValue = $responseMedia ? implode(' ; ', $mediasUrl) : $response['value'];
        if (\is_array($originalValue)) {
            $values = $originalValue['labels'];
            if (isset($originalValue['other'])) {
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
        $sheet->setTitle($this->translator->trans('global.contribution', [], 'CapcoAppBundle'));
        \PHPExcel_Settings::setCacheStorageMethod(
            \PHPExcel_CachedObjectStorageFactory::cache_in_memory,
            ['memoryCacheSize' => '512M']
        );
        $nbCols = \count($headers);
        // Add headers
        list($startColumn, $startRow) = \PHPExcel_Cell::coordinateFromString();
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
        list($startColumn, $startRow) = \PHPExcel_Cell::coordinateFromString('A2');
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

    private function booleanToString($boolean): string
    {
        if ($boolean) {
            return 'Yes';
        }

        return 'No';
    }

    private function dateToString(?\DateTime $date = null): string
    {
        if ($date) {
            return $date->format('Y-m-d H:i:s');
        }

        return '';
    }
}
