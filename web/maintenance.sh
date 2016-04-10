#!/bin/sh

mv app.php app.original.php
mv app_maintenance.php app.php
/usr/local/bin/supervisorctl stop all
