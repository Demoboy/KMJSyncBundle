KMJSyncBundle
================================

This bundle comes with a few configuration options.

<pre>
api_syncing:
   dir:                 #The working directory for the bundle, no data is stored here. Defaults to %kernel.root_dir%/cache/sync
   backups:             #The directory to store all the backups. Defaults to %kernel.root_dir%/Resources/backups
   compression: tar     #The compression method to be used for the backup files. Only supported one at the moment is tar
   numberofbackups: 3   #The number of backups to keep
   paths:               #Array of directories to compress
      - path: %kernel.root_dir%/../web/uploads
   

   ## ALL OF THESE ARE REQUIRED ##

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