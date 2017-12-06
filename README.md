# InfuraOffice
A Mix of Infura Management

## Third Party Library Declaration

PECL

* [SSH2](http://php.net/manual/en/ssh2.installation.php)

Packagist

* sinri/enoch 

Node Package Manager

* iview 
* jquery
* js-cookie 
* vue 

See the package config file for more details.

## Functions

* Check server disk space usage with `df` and `du` commands;
* See file system details of server with `ls` command.
* See the process list of database and kill any of them if permitted.

## Install

1. Get the source code from GitHub
1. Run `php composer.phar install` in the project root directory
1. Run `npm install` in the `site/frontend` directory

## Config

The config file is `config/config.php`. A sample file given for reference.

### Daemon

You should set the daemon address and port, and the path of SSH key file (commonly `id_rsa`).

* address: 127.0.0.1
* port: 12345
* ssh_key_file: ~/.ssh/id_rsa

### Log

You should determine a directory to store logs.

* dir: /var/log/InfuraOffice

## Site

* Set the web root directory as `site`.
* Ensure `data` exists and writable.
* Check `config/config.php` and make it correctly there.

## CLI

Run `php cli/InfuraOfficeDaemon.php` . You might use `nohup` in product environment.

## Job Config


 * @property string job_name
 * @property string job_type
 * @property string cron_time_minute
 * @property string cron_time_hour
 * @property string cron_time_day_of_month
 * @property string cron_time_month
 * @property string cron_time_day_of_week
 * @property int last_run_timestamp
 * @property string[] server_list
 * @property bool stopped

### ShellCommand

 * @property string command_content

### ExplodeLog

 * @property string file
 * @property int left_tail_lines
 * @property bool keep_backup

### RemoveAntiquity

* file
* keep_days
* date_format : Y(2017) y(17) m(12) d(31)

### RemoveZombie

* file
* keep_days 