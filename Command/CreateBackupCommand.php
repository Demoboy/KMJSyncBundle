<?php

namespace KMJ\SyncBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Description of CreateBackupCommand
 *
 * @author kaelinjacobson
 */
class CreateBackupCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('kmj:sync:backup')
                ->setDescription('Creates a backup with the other sites can pull from');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->getContainer()->get('kernel')->getEnvironment() != "prod") {
            $output->writeln('<error>Cannot create a backup from a non-production environment');
            return;
        }
        
        // create dump of mysql database and place in cache folder
        $sync = $this->getContainer()->get('sync');

        //determine if database should be backed up
        $output->writeln("<info>Exporting database to file</info>");

        $mysqlDumpProcess = new Process("mysqldump -h {$sync->getDatabaseHost()} --user={$sync->getDatabaseUser()} --password='{$sync->getDatabasePassword()}' {$sync->getDatabaseName()} > {$sync->createBackupDir()}/export.sql");
        $mysqlDumpProcess->setTimeout(3600);
        $mysqlDumpProcess->run();

        if (!$mysqlDumpProcess->isSuccessful()) {
            $output->writeln('<error>Could not export database to file. Error output:');
            $output->writeln($mysqlDumpProcess->getErrorOutput());
            $output->writeln('</error>');
            return;
        }

        $output->writeln("<info>Compressing backup folder</info>");

        //create tar file        
        $tarFile = $sync->getCurrentBackupFolder() . '/' . date('Y-m-d_H:i:s') . ".tar";

        //add sql file to tar
        $createArchive = new Process("cd {$sync->getCurrentBackupFolder()} && tar -cf {$tarFile} export.sql");
        $createArchive->run();

        if (!$createArchive->isSuccessful()) {
            $output->writeln('<error>Could not compress sql file. Error output:');
            $output->writeln($createArchive->getErrorOutput());
            $output->writeln('</error>');
            return;
        }

        //copy the requested files to the temp folder
        foreach ($sync->getPaths() as $path) {
            $explodedPaths = explode('/', $path['path']);

            $folderName = end($explodedPaths);

            $copyProcess = new Process("cd {$path['path']}/../ && cp -r {$folderName} {$path['key']} && tar -rf {$tarFile} {$path['key']} && rm -rf {$path['key']}");
            $copyProcess->setTimeout(3600);
            $copyProcess->run();

            if (!$copyProcess->isSuccessful()) {
                $output->writeln('<error>Could not compress files. Error output:');
                $output->writeln("<error>".$copyProcess->getErrorOutput());
                return;
            }
        }
        
        //move file to backups folder
        $moveFile = new Process("cd {$sync->getCurrentBackupFolder()}/ && mv *.tar {$sync->getBackupDir()}/");
        $moveFile->run();
        
        $output->writeln("<info>Cleaning up files");
        
        $cleanUp = new Process("rm -rf {$sync->getCurrentBackupFolder()}");
        $cleanUp->run();
                
        $backupCleaner = $this->getApplication()->find('kmj:sync:clean');
        $backupCleaner->run(new ArrayInput(array("backups" => $sync->getNumberOfBackupsToKeep())), $output);
    }
}