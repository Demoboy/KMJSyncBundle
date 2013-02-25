KMJSyncBundle
================================


Welcome to the KMJSyncBundle. The goal of this project to ease the hassle of getting live data to your development and testing servers.
This bundle takes the specified folders and the production database and compresses them into a tar file. 
The development servers can then request the latest backup, download it, and install it.


1) Installation
----------------------------------

KMJSyncBundle can conveniently be installed via Composer. Just add the following to your composer.json file:

<pre>
// composer.json
{
    // ...
    require: {
        // ..
        "kmj/syncbundle": "dev-master"
    }
}
</pre>


Then, you can install the new dependencies by running Composer's update command from the directory where your composer.json file is located:

<pre>
    php composer.phar update
</pre>

Now, Composer will automatically download all required files, and install them for you. All that is left to do is to update your AppKernel.php file, and register the new bundle:

<pre>
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new KMJ\SyncBundle\KMJSyncBundle(),
    // ...
);
</pre>

2) Configuration
----------------------------------

This bundle comes with a few configuration options.

<pre>
api_syncing:
   dir:                 #The working directory for the bundle, no data is stored here. Defaults to %kernel.root_dir%/cache/sync
   backups:             #The directory to store all the backups. Defaults to %kernel.root_dir%/Resources/backups
   compression:         #The compression method to be used for the backup files. Only supported one at the moment is tar
   numberofbackups:     #The number of backups to keep Defaults to 3
  
   ## ALL OF THESE ARE REQUIRED ##

paths:                  #Array of directories to compress
      - path:           #The path for the folder to be backed up. Example:%kernel.root_dir%/../web/uploads

   database:            #Production database credentials
      type: mysql       #Mysql is the only supported type
      host:             #Production server database host
      database:         #Production server database name
      user:             #Production server database username
      password:         #Production server database password 
   ssh:
     host:              #Production server host name
     port: 2100         #Production server port
     username:          #Production server username
     path:              #Path to the root of the site
</pre>

3) Usage
----------------------------------

On the production server a command like 

<pre>
app/console kmj:sync:backup --env=prod
</pre>

will generate a backup file to that the other development and testing servers can download it



On your testing servers all you need to do is run
<pre>
app/console kmj:sync:sync
</pre>

This will download the backup file, extract it's contents, dump the current database and reload it with production data and finally move any folders to their original location.