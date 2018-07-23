# InfuraOffice
A Mix of Infura Management

https://sinri.github.io/InfuraOffice/

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

Run `php cli/InfuraOfficeDaemon.php` to start the daemon, run with `nohup` is recommended.

### Log

You should determine a directory to store logs.

* dir: /var/log/InfuraOffice

## Site

* Set the web root directory as `site`.
* Ensure `data` exists and writable.
* Check `config/config.php` and make it correctly there.

## CLI

Run `php cli/InfuraOfficeDaemon.php` . You might use `nohup` in product environment.

# About new daemon written in Java `JSSHAgent`

InfuraOfficeJavaDaemon.jar is provided in `cli/JSSHAgent`.

Run as 

```bash
java -jar InfuraOfficeJavaDaemon.jar [[(*|ip):]port] [key_path] [thread_limit] [DEBUG|INFO|WARN|ERROR]
```

Arguments:

* Listen address and port. Address is optional for localhost. A star(`*`) means listen to the whole web. Note, a warning would be shown when listen to non-local address.
* RSA Private Key Path. By default use the Linux Default path for current user.
* Thread Limit. How many workers to work.
* Minimal Log Level.   

> Its source code is not completely done, so only jar file provided along with release.