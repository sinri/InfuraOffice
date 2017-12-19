#!/usr/bin/env bash

# change the path to InfuraOfficeDaemon to actual one!

echo Call kill ...
ps aux|grep InfuraOfficeDaemon.php |awk '{print("kill -9 ",$2)}'|bash
echo Nohup run...
nohup php /var/www/InfuraOffice/cli/InfuraOfficeDaemon.php > /var/log/InfuraOfficeDaemon.log 2>&1 &
echo Over