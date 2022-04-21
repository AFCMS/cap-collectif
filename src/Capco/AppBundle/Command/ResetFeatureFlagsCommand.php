<?php

namespace Capco\AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetFeatureFlagsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('capco:reset-feature-flags')
            ->setDescription('Reset the feature flags to default values')
            ->addOption('force', false, InputOption::VALUE_NONE, 'set this option to force the reinit. Warning, this may de/activate some features')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln('Please set the --force option to run this command');

            return;
        }

        $output->writeln('');
        $output->writeln('Resetting the feature toggles to the default configuration');

        $toggleManager = $this->getApplication()->getKernel()->getContainer()->get('capco.toggle.manager');
        $toggleManager->activate('blog');
        $toggleManager->activate('calendar');
        $toggleManager->activate('newsletter');
        $toggleManager->activate('ideas');
        $toggleManager->activate('themes');
        $toggleManager->activate('registration');
        $toggleManager->activate('login_facebook');
        $toggleManager->activate('login_gplus');
        $toggleManager->activate('login_twitter');
        $toggleManager->activate('user_type');
        $toggleManager->activate('members_list');
        $toggleManager->activate('consultations_form');

        $toggleManager->deactivate('shield_mode');

        $output->writeln('Feature flags reseted');
    }
}
