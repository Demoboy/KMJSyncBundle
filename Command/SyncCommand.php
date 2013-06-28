<?php

namespace KMJ\SyncBundle\Command;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Description of CreateBackupCommand
 *
 * @author kaelinjacobson
 */
class SyncCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('kmj:sync:sync')
                ->setDescription('Syncs the current server with the production server');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->getContainer()->get('kernel')->getEnvironment() == "prod") {
            $output->writeln('<error>Cannot sync in current environment');
            return;
        }

        $sync = $this->getContainer()->get('sync');

        $output->writeln("<info>Determining last backup date");

        //get the most recent backup on the server call 
        //change the directory to the path var and execute kmj:sync:last command and read input
        $backupFilePath = new Process("ssh {$sync->getSSHUserName()}@{$sync->getSSHHost()} -p {$sync->getSSHPort()} 'cd {$sync->getSSHPath()} && app/console --env=prod kmj:sync:last'");
        $backupFilePath->run();

        if (!$backupFilePath->isSuccessful()) {
            $output->writeln('<error>Could not determine last backup date. Error output:');
            $output->writeln($backupFilePath->getErrorOutput());
            $output->writeln('</error>');
            return;
        }

        $file = trim($backupFilePath->getOutput());

        if ($file == "No backups are available") {
            $output->writeln('<error>Backups have not been completed');
            return;
        }
        
        $lastBackupDate = new \DateTime(str_replace("_", " ", substr(basename($file), 0, -4)));
        $output->writeln("<info>Last backup date was {$lastBackupDate->format("Y-m-d")} at {$lastBackupDate->format("g:i:s")}");
        $output->writeln("<info>Downloading payload");

        $copyFile = new Process("scp -P {$sync->getSSHPort()} {$sync->getSSHUserName()}@{$sync->getSSHHost()}:{$file} {$sync->createBackupDir()}/backup.tar");
        $copyFile->setTimeout(3600);
        $copyFile->run();

        if (!$copyFile->isSuccessful()) {
            $output->writeln('<error>Could not copy backup file. Error output:');
            $output->writeln($copyFile->getErrorOutput());
            $output->writeln('</error>');
            return;
        }

        $extract = new Process("cd {$sync->getCurrentBackupFolder()} && mkdir backup && tar -C backup/ -xf *.tar");
        $extract->setTimeout(3600);
        $extract->run();
        //dump current database

        if (!$extract->isSuccessful()) {
            $output->writeln('<error>Could not extract file. Error output:');
            $output->writeln($extract->getErrorOutput());
            $output->writeln('</error>');
            return;
        }

        $output->writeln("<info>Removing database");

        $em = $this->getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($em);
        $tool->dropDatabase();


        $output->writeln("<info>Importing database");

        $mysqlImportProcess = new Process("mysql -h {$sync->getDatabaseHost()} --user={$sync->getDatabaseUser()} --password='{$sync->getDatabasePassword()}' {$sync->getDatabaseName()} < {$sync->getCurrentBackupFolder()}/backup/export.sql");
        $mysqlImportProcess->setTimeout(3600);
        $mysqlImportProcess->run();

        if (!$mysqlImportProcess->isSuccessful()) {
            $output->writeln('<error>Could not import file. Error output:');
            $output->writeln($mysqlImportProcess->getErrorOutput());
            $output->writeln('</error>');
            return;
        }
        //check the folder names

        $output->writeln("<info>Moving files into location");

        $finder = new Finder();
        $finder->directories()->in($sync->getCurrentBackupFolder() . '/backup');

        foreach ($finder as $dir) {
            $path = $sync->getPathForKey($dir->getFileName());

            if ($path == null) {
                continue;
            }
            //move file

            $moveFile = new Process("rm -rf {$path} && cp -r {$dir->getPathName()} {$path}");
            $moveFile->setTimeout(3600);
            $moveFile->run();

            if (!$moveFile->isSuccessful()) {
                $output->writeln('<error>Could not move file. Error output:');
                $output->writeln($moveFile->getErrorOutput());
                $output->writeln('</error>');
                return;
            }
        }
        // clean up and remove current backup dir

        $output->writeln("<info>Cleaning up files");

        $cleanUp = new Process("rm -rf {$sync->getCurrentBackupFolder()}");
        $cleanUp->run();
    }

}