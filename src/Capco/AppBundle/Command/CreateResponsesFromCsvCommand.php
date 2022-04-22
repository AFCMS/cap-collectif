<?php

namespace Capco\AppBundle\Command;

use Capco\AppBundle\Entity\Reply;
use Capco\AppBundle\Entity\Responses\ValueResponse;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateResponsesFromCsvCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('capco:import:responses-from-csv')
            ->setDescription('Import responses from CSV file')
            ->addOption(
                'force',
                false,
                InputOption::VALUE_NONE,
                'set this option to force the rebuild'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->import($input, $output);
    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln('Please set the --force option to run this command');

            return;
        }

        $em = $this->getContainer()
            ->get('doctrine')
            ->getManager();

        $responses = $this->getContainer()
            ->get('Capco\AppBundle\Helper\ConvertCsvToArray')
            ->convert('pjl/responses.csv', ';');
        foreach ($responses as $row) {
            $author = $em
                ->getRepository('CapcoUserBundle:User')
                ->findOneBy(['email' => $row['email']]);
            if (!$author) {
                $output->writeln(
                    'Author ' .
                        $row['email'] .
                        ' does not exist. Create it manually before importing.'
                );

                return 1;
            }
            $questionnaire = $em
                ->getRepository('CapcoAppBundle:Questionnaire')
                ->find($row['questionnaire_id']);
            if (!$questionnaire) {
                $output->writeln(
                    'Questionnaire ' .
                        $row['questionnaire_id'] .
                        ' does not exist. Create it manually before importing.'
                );

                return 1;
            }
            $reply = $em->getRepository('CapcoAppBundle:Reply')->findOneBy([
                'author' => $author,
                'questionnaire' => $questionnaire,
            ]);
            if (!$reply) {
                $reply = new Reply();
                $reply->setAuthor($author);
                $reply->setQuestionnaire($questionnaire);
                $reply->setCreatedAt(
                    \DateTime::createFromFormat('Y-m-d H:i:s', $row['created_at'])
                );
                $reply->setPrivate($row['private']);
                $em->persist($reply);
            }
            $question = $em
                ->getRepository('CapcoAppBundle:Questions\AbstractQuestion')
                ->find($row['question_id']);
            $response = new ValueResponse();
            $response->setReply($reply);
            $response->setQuestion($question);
            $response->setValue($row['value']);
            $em->persist($response);
            $em->flush();
        }

        $output->writeln(\count($responses) . ' responses have been created !');
    }
}
