<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/23
 * Time: 11:06
 */

namespace sinri\InfuraOffice\library;


use sinri\enoch\core\LibRequest;
use sinri\enoch\helper\CommonHelper;
use sinri\InfuraOffice\entity\JSSHAgentTaskStatusEntity;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

class JSSHAgentLibrary
{
    protected $proxyUrlBase;
    protected $error;


    public function __construct()
    {
        $this->proxyUrlBase = InfuraOfficeToolkit::readConfig(['jsshagent', 'proxy'], 'http://localhost:9999/');
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return bool|mixed
     */
    public function proxy($url, $parameters)
    {
        $response = CommonHelper::executeCurl(LibRequest::METHOD_POST, $this->proxyUrlBase . $url, $parameters);
        $response = json_decode($response, true);
        $code = CommonHelper::safeReadArray($response, 'code', 'FAIL');
        $data = CommonHelper::safeReadArray($response, 'data', 'No DATA found');
        if ($code !== 'OK') {
            $this->error = $data;
            return false;
        } elseif ($data === false || $data === null) {
            return true;
        }
        return $data;
    }

    /**
     * @param string $serverName
     * @param string $command
     * @return bool|String
     * @throws \sinri\enoch\mvc\BaseCodedException
     */
    public function newTaskForRegisteredServer($serverName, $command)
    {
        $serverEntity = (new ServerLibrary())->readEntityByName($serverName);
        CommonHelper::assertNotEmpty($serverEntity, 'no such server');
        return $this->newTask($serverEntity->connect_ip, $serverEntity->ssh_user, $command);
    }

    /**
     * @param string $ip
     * @param string $user
     * @param string $command
     * @return String|bool
     */
    public function newTask($ip, $user, $command)
    {
        $url = 'newTask';
        $parameters = [
            "type" => "command",
            "ip" => $ip,
            "port" => 22,
            "user" => $user,
            "command" => $command,
        ];
        $result = $this->proxy($url, $parameters);
        return CommonHelper::safeReadArray($result, 'task_index', false);
    }

    /**
     * @param string $task_index
     * @return JSSHAgentTaskStatusEntity
     */
    public function checkTask($task_index)
    {
        $url = 'checkTask';
        $parameters = [
            "task_index" => $task_index,
        ];
        $result = $this->proxy($url, $parameters);

        $entity = JSSHAgentTaskStatusEntity::buildFromSeverResponse($result);

        return $entity;
    }
}