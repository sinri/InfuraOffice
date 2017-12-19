#!/usr/bin/env bash

# change the path to the actual one

echo checking running Dothan instance...
ps aux|grep Dothan.jar|grep java|awk '{print "kill -9 ",$2}'
echo kill them...
ps aux|grep Dothan.jar|grep java|awk '{print "kill -9 ",$2}'|bash
echo restart
nohup /opt/jdk1.8.0_121/bin/java -jar /opt/Dothan/Dothan.jar -c /var/www/InfuraOffice/data/dothan.config -d >> /var/log/Dothan.log 2>&1 &