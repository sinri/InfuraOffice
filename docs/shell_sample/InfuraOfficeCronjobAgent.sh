#!/usr/bin/env bash

# change the path to the actual one

if [ $1 == 'restart' ]; then
	echo RESTART InfuraOffice Cronjob Daemon...
	/etc/init.d/InfuraOfficeCronjob stop
	sleep 30
	echo "Stopped? Check process..." `ps aux|grep InfuraOfficeCronjob.php|grep $1|wc -l`

	ps uax|grep InfuraOfficeCronjob|grep php|awk '{print "kill -9 ", $2}'|bash

	/etc/init.d/InfuraOfficeCronjob start
else
	echo "InfuraOfficeCronjob Daemon " $1
	nohup php /var/www/InfuraOffice/cli/InfuraOfficeCronjob.php $1 >> /var/log/InfuraOfficeCronjob.log 2>&1 &
	sleep 3
	echo "Check process..."
	ps aux|grep InfuraOfficeCronjob.php|grep $1
fi
