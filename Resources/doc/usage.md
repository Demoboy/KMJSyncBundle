KMJSyncBundle
================================

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