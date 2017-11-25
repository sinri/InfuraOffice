# InfuraOffice
A Mix of Infura Management

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