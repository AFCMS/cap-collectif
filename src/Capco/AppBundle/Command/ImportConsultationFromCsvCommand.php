<?php
namespace Capco\AppBundle\Command;

use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionAppendix;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportConsultationFromCsvCommand extends ContainerAwareCommand
{
    const HEADERS = ['titre', 'type', 'contenu'];
    private $filePath;
    private $delimiter;

    protected function configure()
    {
        $this->setName('capco:import:consultation-from-csv')
            ->setDescription(
                'Import consultation from CSV file with specified author and consultation step'
            )
            ->addArgument(
                'filePath',
                InputArgument::REQUIRED,
                'Please provide the path of the file you want to use.'
            )
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Please provide the email of the author you want to use.'
            )
            ->addArgument(
                'step',
                InputArgument::REQUIRED,
                'Please provide the slug of the consultation step you want to use'
            )
            ->addArgument('delimiter', InputArgument::OPTIONAL, ', or ;')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Set this option to force data import even if opinion with same title are found.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->import($input, $output);
    }

    protected function import(InputInterface $input, OutputInterface $output): int
    {
        $this->filePath = $input->getArgument('filePath');
        $this->delimiter = $input->getArgument('delimiter');
        $userEmail = $input->getArgument('user');
        $consultationStepSlug = $input->getArgument('step');

        $em = $this->getContainer()
            ->get('doctrine')
            ->getManager();

        $user = $this->getContainer()
            ->get('fos_user.user_manager')
            ->findUserByEmail($userEmail);
        $consultationStep = $em
            ->getRepository('CapcoAppBundle:Steps\ConsultationStep')
            ->findOneBy(['slug' => $consultationStepSlug]);
        if (!$user) {
            $output->writeln(
                '<error>Unknown user' .
                    $userEmail .
                    '. Please provide an existing user email.</error>'
            );
            $output->writeln('<error>Import cancelled. No opinion created.</error>');

            return 1;
        }

        if (!$consultationStep) {
            $output->writeln(
                '<error>Unknown consultation step' .
                    $consultationStepSlug .
                    '. Please provide an existing consultation step slug.</error>'
            );
            $output->writeln('<error>Import cancelled. No opinion created.</error>');

            return 1;
        }

        if (!$consultationStep->getConsultationStepType()) {
            $output->writeln(
                '<error>Consultation step' .
                    $consultationStepSlug .
                    ' does not have a consultation step type associated Please create it then try importing data again.</error>'
            );
            $output->writeln('<error>Import cancelled. No opinion created.</error>');

            return 1;
        }

        $opinions = $this->getOpinions();

        if (!$opinions || 0 === \count($opinions)) {
            $output->writeln(
                '<error>File "opinions.csv" is not provided, is empty or could not be parsed.</error>'
            );
            $output->writeln('<error>Import cancelled. No opinion created.</error>');

            return 1;
        }

        $count = \count($opinions);
        $progress = new ProgressBar($output, $count);
        $progress->start();

        $i = 1;
        foreach ($opinions as $key => $row) {
            if (0 === $key) {
                continue;
            }
            $opinionType = null;
            foreach (explode('|', $row[1]) as $index => $ot) {
                if (0 === $index) {
                    $opinionType = $em
                        ->getRepository('CapcoAppBundle:OpinionType')
                        ->findOneBy([
                            'title' => $ot,
                            'parent' => null,
                            'consultationStepType' => $consultationStep->getConsultationStepType(),
                        ]);
                } else {
                    $opinionType = $em
                        ->getRepository('CapcoAppBundle:OpinionType')
                        ->findOneBy(['title' => $ot, 'parent' => $opinionType]);
                }
            }

            if (!$opinionType) {
                $output->writeln(
                    '<error>Opinion type with path ' .
                        $row[1] .
                        ' does not exist for this consultation step (specified for opinion ' .
                        $row[0] .
                        ').</error>'
                );
                $output->writeln('<error>Import cancelled. No opinion created.</error>');

                return 1;
            }

            $opinion = $em
                ->getRepository('CapcoAppBundle:Opinion')
                ->findOneBy(['title' => $row[0], 'step' => $consultationStep]);
            if (\is_object($opinion) && !$input->getOption('force')) {
                $output->writeln(
                    '<error>Opinion with title "' .
                        $row[0] .
                        '" already exists in this consultation step. Please change the title or specify the force option to import it anyway.</error>'
                );
                $output->writeln('<error>Import cancelled. No opinion created.</error>');

                return 1;
            }

            if (!\is_object($opinion)) {
                $opinion = new Opinion();
            }

            $opinion->setTitle($row[0]);
            $opinion->setBody($row[2]);
            $opinion->setStep($consultationStep);

            $opinion->setOpinionType($opinionType);
            $opinion->setAuthor($user);
            $opinion->setPosition($i);
            $opinion->setIsEnabled(true);
            ++$i;

            $em->persist($opinion);

            // if (array_key_exists('contexte', $row)) {
            //     $opinionTypeAppendixType = $em
            //         ->getRepository('CapcoAppBundle:OpinionTypeAppendixType')
            //         ->findOneBy([
            //             'opinionType' => $opinion->getOpinionType(),
            //         ])
            //     ;
            //     if (!is_object($opinionTypeAppendixType)) {
            //         $output->writeln(
            //             '<error>No appendix type defined for opinion type '
            //             . $opinion->getOpinionType()->getTitle() .
            //             '.</error>'
            //         );
            //         $output->writeln('<error>Import cancelled. No opinions created.</error>');
            //
            //         return 1;
            //     }
            //
            //     if (0 === count($opinion->getAppendices())) {
            //         $appendix = new OpinionAppendix();
            //         $appendix->setAppendixType($opinionTypeAppendixType->getAppendixType());
            //         $opinion->addAppendice($appendix);
            //     } else {
            //         $appendix = $opinion->getAppendices()[0];
            //     }
            //     $appendix->setBody('<p>' . nl2br(htmlspecialchars($row['contexte'])) . '</p>');
            // }
            $progress->advance();
        }

        $em->flush();
        $progress->finish();

        $output->writeln(
            '<info>' . \count($opinions) - 1 . ' opinions successfully created.</info>'
        );

        return 0;
    }

    protected function getOpinions(): array
    {
        return Reader::createFromPath($this->filePath)
            ->setDelimiter($this->delimiter ?? ';')
            ->fetchAll();
    }
}
