<?php

namespace KMJ\SyncBundle\Service;

class SyncService {
    protected $config;
    protected $kernel;
    protected $doctrine;
    protected $currentFolder;

    public function __construct($config, $kernel, $doctrine) {
        $this->kernel = $kernel;
        $this->doctrine = $doctrine;
        $this->loadConfig($config);
        $this->config = $config;
        
        if (!is_dir($this->getBackupDir())) {
            mkdir($this->getBackupDir());
        }
    }

    public function loadConfig($config) {
        //test config for parameters
        @mkdir($config['dir']);
    }

    public function getRootDir() {
        return $this->config['dir'];
    }

    public function createBackupDir() {
        if ($this->currentFolder == null) {
            $folderPath = $this->getRootDir() . '/' . date('Ymdgis');
            @mkdir($folderPath);
            $this->currentFolder = $folderPath;
        }

        return $this->currentFolder;
    }

    public function getCurrentBackupFolder() {
        return $this->currentFolder;
    }

    public function getDatabaseUser() {
        return $this->config['database']['user'];
    }

    public function getDatabaseHost() {
        return $this->config['database']['host'];
    }

    public function getDatabasePassword() {
        return $this->config['database']['password'];
    }

    public function getDatabaseName() {
        return $this->config['database']['database'];
    }

    public function getDatabaseType() {
        return $this->config['database']['type'];
    }

    public function getBackupDir() {
        return $this->config['backups'];
    }

    public function getSSHUserName() {
        return $this->config['ssh']['username'];
    }

    public function getSSHHost() {
        return $this->config['ssh']['host'];
    }

    public function getSSHPassword() {
        return $this->config['ssh']['password'];
    }

    public function getSSHPath() {
        return $this->config['ssh']['path'];
    }
    
    public function getSSHPort() {
        return $this->config['ssh']['port'];
    }
    
    public function getNumberOfBackupsToKeep() {
        return $this->config['numberofbackups'];
    }

    public function getPaths() {
        $paths = array();

        foreach ($this->config['paths'] as $path) {                        
            $paths[] = array(
                'path' => $path['path'],
                'key' => md5(str_replace($this->kernel->getRootDir(), "", $path['path'])),
            );
        }

        return $paths;
    }
    
    public function getPathForKey($key) {
        foreach ($this->getPaths() as $path) {
            if ($path['key'] == $key) {
                return $path['path'];
            }
        }
        
        return null;
    }

}

?>