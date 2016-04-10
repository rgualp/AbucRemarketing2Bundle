#!/bin/sh

echo 'flush_all' | nc localhost 11211
/usr/local/bin/supervisorctl update
/usr/local/bin/supervisorctl start all
mv app.php app_maintenance.php 
mv app.original.php app.php
