<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/27
 * Time: 22:09
 */

namespace sinri\InfuraOffice\cli\daemon;


use sinri\enoch\core\LibLog;

class SSHToolkit
{
    // SSH Host
    private $ssh_host = 'myserver.example.com';
    // SSH Port
    private $ssh_port = 22;
    // SSH Server Fingerprint
    //private $ssh_server_fp = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    // SSH Username
    private $ssh_auth_user = 'username';
    // SSH Public Key File
    private $ssh_auth_pub = '/home/username/.ssh/id_rsa.pub';
    // SSH Private Key File
    private $ssh_auth_priv = '/home/username/.ssh/id_rsa';
    // SSH Private Key Passphrase (null == no passphrase)
    private $ssh_auth_pass;
    // SSH Connection
    private $connection;

    /**
     * SSHToolkit constructor.
     * @param string $host
     * @param string $user
     * @param int $port
     * @param string $rsa_public_file
     * @param string $rsa_private_file
     * @param null|string $rsa_pass_phrase
     */
    public function __construct($host, $user = 'root', $port = 22, $rsa_public_file = '~/.ssh/id_rsa.pub', $rsa_private_file = '~/.ssh/id_rsa', $rsa_pass_phrase = null)
    {
        $this->ssh_host = $host;
        $this->ssh_port = $port;
        $this->ssh_auth_user = $user;
        $this->ssh_auth_pub = $rsa_public_file;
        $this->ssh_auth_priv = $rsa_private_file;
        $this->ssh_auth_pass = $rsa_pass_phrase;
    }

    /**
     * @throws \Exception
     */
    public function connect()
    {
        DaemonHelper::log(LibLog::LOG_INFO, __METHOD__ . "@" . __LINE__);
        $this->connection = ssh2_connect($this->ssh_host, $this->ssh_port);
        if (!($this->connection)) {
            throw new \Exception('Cannot connect to server: ' . $this->ssh_host . ":" . $this->ssh_port);
        }
        //$fingerprint = ssh2_fingerprint($this->connection, SSH2_FINGERPRINT_MD5 | SSH2_FINGERPRINT_HEX);
        //if (strcmp($this->ssh_server_fp, $fingerprint) !== 0) {
        //    throw new \Exception('Unable to verify server identity!');
        //}
        if (!ssh2_auth_pubkey_file($this->connection, $this->ssh_auth_user, $this->ssh_auth_pub, $this->ssh_auth_priv, $this->ssh_auth_pass)) {
            throw new \Exception('Authentication rejected by server');
        }
    }

    /**
     * @param string $cmd
     * @return string
     * @throws \Exception
     */
    public function exec($cmd)
    {
        //DaemonHelper::log(LibLog::LOG_INFO, __METHOD__ . "@" . __LINE__);
        if (!($stream = ssh2_exec($this->connection, $cmd))) {
            throw new \Exception('SSH command failed: ' . $cmd);
        }
        stream_set_blocking($stream, true);
        $data = "";
        while ($buf = fread($stream, 4096)) {
            $data .= $buf;
        }
        fclose($stream);
        return $data;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getLastExecReturnVar()
    {
        $r = $this->exec("echo $?");
        return intval($r, 10);
    }

    /**
     * @param $localFilePath
     * @param $remoteFilePath
     * @param int $mask
     * @return bool
     */
    public function scpSend($localFilePath, $remoteFilePath, $mask = 0777)
    {
        return ssh2_scp_send($this->connection, $localFilePath, $remoteFilePath, $mask);
    }

    /**
     * @param $remoteFilePath
     * @param $localFilePath
     * @return bool
     */
    public function scpReceive($remoteFilePath, $localFilePath)
    {
        return ssh2_scp_recv($this->connection, $remoteFilePath, $localFilePath);
    }

    /**
     * @throws \Exception
     */
    public function disconnect()
    {
        DaemonHelper::log(LibLog::LOG_INFO, __METHOD__ . "@" . __LINE__);
        if ($this->connection) {
            $this->exec('echo "EXITING" && exit;');
        }
        $this->connection = null;
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    // try to use SFTP

    protected $sftp;

    /**
     * @throws \Exception
     */
    public function establishSFTP()
    {
        $this->sftp = ssh2_sftp($this->connection);
        if (!$this->sftp) {
            throw new \Exception("Could not initialize SFTP subsystem.");
        }
    }

    /**
     * @param $localFilePath
     * @param $remoteFilePath
     * @param null|int $mask
     * @return bool
     * @throws \Exception
     */
    public function sftpSend($localFilePath, $remoteFilePath, $mask = null)
    {
        $data_to_send = @file_get_contents($localFilePath);
        if ($data_to_send === false) {
            throw new \Exception("Could not open local file: " . $localFilePath);
        }

        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . $remoteFilePath, 'w');

        if (!$stream) {
            throw new \Exception("Could not open remote file: " . $remoteFilePath);
        }

        $written = fwrite($stream, $data_to_send);

        fclose($stream);

        if ($mask !== null) {
            $this->sftpChmod($remoteFilePath, $mask);
        }

        return ($written !== false);
    }

    /**
     * @param $remoteFilePath
     * @param $localFilePath
     * @param int $mask
     * @return bool
     * @throws \Exception
     */
    public function sftpReceive($remoteFilePath, $localFilePath, $mask = 0777)
    {
        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . $remoteFilePath, 'r');
        if (!$stream) {
            throw new \Exception("Could not open file: " . $remoteFilePath);
        }
        $size = $this->sftpFileSize($remoteFilePath);
        $contents = '';
        $read = 0;
        $len = $size;
        while ($read < $len && ($buf = fread($stream, $len - $read))) {
            $read += strlen($buf);
            $contents .= $buf;
        }
        $written = file_put_contents($localFilePath, $contents);
        fclose($stream);

        if ($mask !== null) {
            chmod($localFilePath, $mask);
        }

        return ($written !== false);
    }

    /**
     * @param $remoteFilePath
     * @return bool
     */
    public function sftpUnlink($remoteFilePath)
    {
        return ssh2_sftp_unlink($this->sftp, $remoteFilePath);
    }

    /**
     * @param $file
     * @return int
     */
    public function sftpFileSize($file)
    {
        return filesize("ssh2.sftp://" . intval($this->sftp) . $file);
    }

    public function sftpChmod($remoteFilePath, $mask)
    {
        return ssh2_sftp_chmod($this->sftp, $remoteFilePath, $mask);
    }
}