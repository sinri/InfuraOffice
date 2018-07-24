<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/18
 * Time: 15:41
 */

namespace sinri\InfuraOffice\cli\handler;


use sinri\enoch\core\LibLog;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\cli\daemon\DaemonHelper;

class AnsibleCommandHandler implements RequestHandlerInterface
{
    const HANDLER_TYPE = "AnsibleCommand";

    protected $ansibleOutput;
    protected $ansibleReturn;

    protected function callAnsible($module, $arguments, $ips, $remoteUser = 'admin')
    {
        $ips = (count($ips) === 1 ? ($ips . ',') : implode(',', $ips));
        $command = ' ansible all -i ' . escapeshellarg($ips) . ', -u ' . escapeshellarg($remoteUser) . ' -m ' . escapeshellarg($module) . ' -a ' . escapeshellarg($arguments);
        exec($command, $this->ansibleOutput, $this->ansibleReturn);
        return $this->ansibleReturn;
    }

    protected function callAnsibleShell($command, $ips, $remoteUser = 'admin')
    {
        return $this->callAnsible('shell', $command, $ips, $remoteUser);
    }

    /**
     * @param $data
     * @return array object to be encoded into JSON finally in Daemon
     * @throws \Exception
     */
    public function handle($data)
    {
        DaemonHelper::log(LibLog::LOG_DEBUG, __METHOD__ . ' ' . json_encode($data));
        $module = CommonHelper::safeReadArray($data, 'module');
        $result = null;
        switch ($module) {
            case 'shell':
                $this->callAnsibleShell(
                    CommonHelper::safeReadArray($data, 'command'),
                    CommonHelper::safeReadArray($data, 'server_name')
                );
                $result = [
                    'return_var' => $this->ansibleReturn,
                    'output' => $this->ansibleOutput,
                ];
                break;
            default:
                throw new \Exception("No such module defined");
                break;
        }
        return $result;
    }
}