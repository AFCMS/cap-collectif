<?php

namespace Capco\AppBundle\Command;

use Capco\AppBundle\Entity\Answer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class CreateAnswersFromCsvCommand extends ContainerAwareCommand
{
    private $userEmail = 'coucou@cap-collectif.com';
    private $title = 'Réponse du gouvernement';

    protected function configure()
    {
        $this
        ->setName('capco:import:answers-from-csv')
        ->setDescription('Import answers from CSV file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->import($input, $output);
    }

    protected function import(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $answers = $this->getContainer()
            ->get('import.csvtoarray')
            ->convert('pjl/answers.csv', '|')
        ;

        $author = $this
            ->getContainer()
            ->get('fos_user.user_manager')
            ->findOneBy(['email' => $this->userEmail])
       ;

        if (!$author) {
            throw new UsernameNotFoundException('Author email does not exist in db');
        }

        $progress = new ProgressBar($output, count($answers));
        $progress->start();

        $dump = '<ul>';

        foreach ($answers as $row) {
            $answer = new Answer();
            $answer->setAuthor($author);
            $answer->setTitle($this->title);
            $answer->setBody($row['body']);

            $slug = $row['slug'];
            $slug = explode('/', $slug);
            $slug = $slug[count($slug) - 1];

            $type = in_array('versions', explode('/', $row['slug']))
                ? 'version'
                : 'opinion'
            ;

            $object = null;
            if ($type === "opinion") {
                $object = $em
                    ->getRepository('CapcoAppBundle:Opinion')
                    ->findOneBy(['slug' => $slug])
                ;
            } else if ($type === "version") {
                $object = $em
                    ->getRepository('CapcoAppBundle:OpinionVersion')
                    ->findOneBy(['slug' => $slug])
                ;
            }

            if (!$object) {
                throw new \Exception('Object '.$type.' '.$slug.' not found.');
            }

            if ($object->getAnswer()) {
                $em->remove($object->getAnswer());
            }

            $object->setAnswer($answer);

            $em->persist($object);

            $em->flush();

            $dump .=
                '<li>'
                . '<a href="'
                . $this->getContainer()->get('capco.url.resolver')->getObjectUrl($object, false)
                . '">'
                . $object->getAuthor()->getUsername()
                . ' - '
                . $object->getTitle()
                . '</a>'
                . '</li>'
            ;

            $progress->advance(1);
        }

        $dump .= '</ul>';

        $this->getContainer()->get('capco.notify_manager')->sendEmail('maxime@cap-collectif.com', 'swag@tonsite.com', 'Karamazov', '&#128169;', 'Nouvelle réponse au gouvernement');

        (new Filesystem())->dumpFile('answers_list.html', $dump);

        $progress->finish();

        $output->writeln(count($answers).' answers have been created !');
    }
}
