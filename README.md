README
This executable requires access to a mysql service, preferably on the same server.
It requires PHP version 5.6 or better

Files setup.sh, setup.sql, cleanup.sh and cleanup.sql must be in a single directory on the mysql server.
setup.sh and cleanup.sh must have execution permissions set and have mysql login credentials added
setup.sql and cleanup.sql must have read permissions set.

Files zips.csv, plans.csv, slcsp.csv and homework.php must be in a single directory.
The user testing the software must have read and write privledges for this directory
All the files must have read and write permissions.

setup.sh must be run first.
homework.php is run by $ php -f homework.php
cleanup.sh should be run after the test
