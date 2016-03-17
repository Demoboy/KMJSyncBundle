<?php

namespace KMJ\SyncBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of CreateBackupCommand
 *
 * @author kaelinjacobson
 */
class CleanBackupCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('kmj:sync:clean')
                ->setDescription('Cleans backups that are not needed anymore')
                ->addArgument('backups', InputArgument::OPTIONAL, 'Number of backups to keep');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->getContainer()->get('kernel')->getEnvironment() != "prod") {
            $output->writeln('<error>Backups do not exist in this enviroment');
            return;
        }
        
        $sync = $this->getContainer()->get('sync');        
        $numberOfBackupsToKeep = $input->getArgument('backups');
        
        if ($numberOfBackupsToKeep == "") { 
            $numberOfBackupsToKeep = $sync->getNumberOfBackupsToKeep();
        }
        
        //read the backup file dir and get the $numberOfBackupsToKeep most recent
        
        $backups = scandir($sync->getBackupDir());
        
        $backupList = array();
        
        foreach ($backups as $backup) {
            if (substr($backup, -3) == "tar") {
                $backupList[] = $backup;
            }
        }
        
        $backupsToRemove = array_slice($backupList, 0, sizeof($backupList) - $numberOfBackupsToKeep);
        
        foreach ($backupsToRemove as $backup) {
            $remove = new Process("rm -f {$sync->getBackupDir()}/{$backup}");
            $remove->run();
        }
        
        $numRemoved = sizeof($backupsToRemove);
        
        $output->writeln("<info>{$numRemoved} Backups removed");
    } 
}
