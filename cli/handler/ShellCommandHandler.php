<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 14:26
 */

namespace sinri\InfuraOffice\cli\handler;


use sinri\enoch\core\LibLog;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\cli\daemon\DaemonHelper;
use sinri\InfuraOffice\cli\daemon\SSHToolkit;
use sinri\InfuraOffice\entity\ServerEntity;
use sinri\InfuraOffice\library\ServerLibrary;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

class ShellCommandHandler implements RequestHandlerInterface
{
    const HANDLER_TYPE = "ShellCommand";

    /**
     * ['type' => 'ShellCommand', 'data' => ["method"=>'sync','server_name'=>'SinriAtHZ','command'=>'echo A']]
     * SSH command as soon as possible
     * @param $server_name
     * @param $command
     * @param array $output
     * @return bool
     */
    public function sync($server_name, $command, &$output = [])
    {
        DaemonHelper::log(LibLog::LOG_INFO, "Run command on server " . $server_name . " : " . $command);

        $server_library = new ServerLibrary();
        $server_entity = $server_library->readEntityByName($server_name);
        if (!$server_entity) {
            DaemonHelper::log(LibLog::LOG_ERROR, "No such server: " . $server_name);
            return false;
        }

        /*
        $ssh_key_file = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_key_file'], null);
        $option_i = "";
        if ($ssh_key_file && file_exists($ssh_key_file)) {
            $option_i = '-i ' . escapeshellarg($ssh_key_file);
        }

        $command = "ssh " . $option_i . " " . escapeshellarg($server_entity->ssh_user) . "@" . escapeshellarg($server_entity->connect_ip) . " " . escapeshellarg($command);
        exec($command, $output, $return_var);
        */

        $return_var = $this->runCommandThroughSSH($server_entity, $command, $output);

        return $return_var;
    }

    /**
     * @param ServerEntity $serverEntity
     * @param string $command
     * @param array $output
     * @return int|string
     */
    protected function runCommandThroughSSH($serverEntity, $command, &$output = [])
    {
        try {
            $host = $serverEntity->connect_ip;
            $user = $serverEntity->ssh_user;
            $port = $serverEntity->connect_port;
            $rsa_public_file = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_public_file'], '~/.ssh/id_rsa.pub');
            $rsa_private_file = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_key_file'], '~/.ssh/id_rsa');
            $rsa_pass_phrase = InfuraOfficeToolkit::readConfig(['daemon', 'ssh_pass_phrase'], null);
            DaemonHelper::log(LibLog::LOG_DEBUG, 'ssh parameters', [$host, $user, $port, $rsa_public_file, $rsa_private_file, $rsa_pass_phrase]);
            $ssh_toolkit = new SSHToolkit($host, $user, $port, $rsa_public_file, $rsa_private_file, $rsa_pass_phrase);
            $ssh_toolkit->connect();
            $output = $ssh_toolkit->exec($command);
            $return_var = $ssh_toolkit->exec("echo $?");
            return $return_var;
        } catch (\Exception $exception) {
            $output = $exception->getMessage();
            return -1;
        }
    }

    /**
     * @param $server_name
     * @param $command
     * @param bool $mixErrOutput
     * @return array
     */
    public static function buildQueryForSync($server_name, $command, $mixErrOutput = false)
    {
        return [
            'type' => self::HANDLER_TYPE,
            'data' => ["method" => 'sync', 'server_name' => $server_name, 'command' => $command . ($mixErrOutput ? " 2>&1" : '')],
        ];
    }

    /**
     * @param array $data
     * @return array|null
     * @throws \Exception
     */
    public function handle($data)
    {
        DaemonHelper::log(LibLog::LOG_DEBUG, __METHOD__ . ' ' . json_encode($data));
        $method = CommonHelper::safeReadArray($data, 'method');
        $result = null;
        switch ($method) {
            case 'sync':
                $return_var = $this->sync(
                    CommonHelper::safeReadArray($data, 'server_name'),
                    CommonHelper::safeReadArray($data, 'command'),
                    $output
                );
                $result = [
                    'return_var' => $return_var,
                    'output' => $output,
                ];
                break;
            default:
                throw new \Exception("No such method defined");
                break;
        }
        return $result;
    }
}