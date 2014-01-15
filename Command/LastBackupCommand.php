<?php

namespace KMJ\SyncBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of CreateBackupCommand
 *
 * @author kaelinjacobson
 */
class LastBackupCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('kmj:sync:last')
                ->setDescription('Returns the most recent backup available on the server');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->getContainer()->get('kernel')->getEnvironment() != "prod") {
            $output->writeln('<error>Backups only happen in production enviroment');
            return;
        }

        $sync = $this->getContainer()->get('sync');

        $backups = scandir($sync->getBackupDir());
         
        $recentTime = null;
        
        foreach ($backups as $backup) {
            if (substr($backup, -3) == "tar") {
                $backup = str_replace("_", " ", $backup);
                $currentTime = substr($backup, 0, -4);
                if ($currentTime != 0 && is_numeric($currentTime)) {
                    if ($currentTime > $recentTime) {
                        $recentTime = $currentTime;
                    }
                }
            }
        }
        
        if ($recentTime == null) {
            $output->writeln("<error>No backups are available");
        } else {
            $output->writeln($sync->getBackupDir()."/".$recentTime.".tar");
        }
    }

}