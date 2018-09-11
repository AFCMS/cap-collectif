<?php

namespace Capco\AppBundle\Command\Maker\Makers;

use Capco\AppBundle\Command\Maker\AbstractMaker;
use Capco\AppBundle\Utils\Text;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeProcessor extends AbstractMaker
{
    public function getTemplate(): string
    {
        return self::TEMPLATE_PATH . '/Processor.tpl.php';
    }

    public function getOutputDirectory(): string
    {
        return $this->getSourcePath() . '/Capco/AppBundle/Processor';
    }

    public function getTemplateVars(): array
    {
        return [
            'command_class_name' => $this->className,
            'command_entity_name' => $this->entity ? $this->entity->getShortName() : null,
        ];
    }

    protected function configure()
    {
        $this->setName('capco:make:processor')
            ->setDescription('Generate a processor')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                "The name of your processor. You don't need to put the suffix Processor, it will be automatically added."
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('name')) {
            $input->setArgument(
                'name',
                $this->askSimpleQuestion(
                    $input,
                    $output,
                    'Please specify the name of your processor. Note that Processor suffix will be automatically added. <info>(e.g ProposalCreate)</info>'
                )
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->className = $input->getArgument('name');
        $this->className = str_replace('Processor', '', $this->className);
        $this->className .= 'Processor';

        $this->entity = $this->askEntity(
            $input,
            $output,
            'Please type the related entity of your message <info>(e.g Proposal)</info>'
        );

        $path = $this->makeFile();

        // TODO We should delete this function du of autowire
        //        $service = $this->configureService();

        $output->writeln('<info>File successfully written at ' . realpath($path) . '</info>');
        //        $output->writeln(
        //            '<info>Successfully added service "' .
        //                $service .
        //                '" to ' .
        //                $this->getProcessorsPath() .
        //                '</info>'
        //        );
    }

    private function getProcessorsPath(): string
    {
        return realpath(
            $this->sourcePath . '/Capco/AppBundle/Resources/config/services/processors.yml'
        );
    }

    private function configureService(): string
    {
        $shortname = $this->entity->getShortname();
        $shortnameSnakeCased = Text::snakeCase($shortname);
        $service = Text::snakeCase(str_replace('Processor', '', $this->className)) . '.processor';
        $processorPath = "\\$shortname\\{$this->className}";
        $yml = <<<EOF
  {$service}:
    class: Capco\AppBundle\Processor{$processorPath}
    arguments:
      - '@capco.$shortnameSnakeCased.repository'
      - '@capco.{$shortnameSnakeCased}_notifier'
EOF;
        if (false === file_put_contents($this->getProcessorsPath(), $yml, FILE_APPEND)) {
            throw new \RuntimeException(
                sprintf('Error during writing of file %s', $this->getProcessorsPath())
            );
        }

        return $service;
    }
}
