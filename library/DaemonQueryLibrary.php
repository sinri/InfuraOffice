<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 16:05
 */

namespace sinri\InfuraOffice\library;


use sinri\InfuraOffice\cli\daemon\DaemonHelper;
use sinri\InfuraOffice\cli\daemon\SocketAgent;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

class DaemonQueryLibrary
{
    protected $socketAgent;

    public function __construct()
    {
        $address = InfuraOfficeToolkit::readConfig(['daemon', 'address'], '127.0.0.1');
        $port = InfuraOfficeToolkit::readConfig(['daemon', 'port'], '12345');

        $this->socketAgent = new SocketAgent();
        $this->socketAgent->configSocketAsTcpIp($address, $port);
    }

    /**
     * @param $content
     * @param int $timeoutInSeconds
     * @return array|string
     */
    public function query($content, $timeoutInSeconds = 20)
    {
        return $this->socketAgent->runClient(function ($client) use ($content, $timeoutInSeconds) {
            $pairName = stream_socket_get_name($client, true);
            DaemonHelper::clientLog("INFO", "Client linked to " . $pairName);
            //stream_set_timeout($client, 0, 100000);
            stream_set_timeout($client, $timeoutInSeconds);//timeout as 20seconds by default

            $content = json_encode($content);
            fwrite($client, $content);
            fflush($client);
            $response = '';
            DaemonHelper::clientLog("INFO", "Request Sent");
            while (!feof($client)) {
                DaemonHelper::clientLog("DEBUG", "Waiting for response...");
                $meta = stream_get_meta_data($client);
                DaemonHelper::clientLog('DEBUG', 'read once meta: ' . json_encode($meta));

                $got = fread($client, 1024);
                $response .= $got;
                DaemonHelper::clientLog("DEBUG", "read from [{$pairName}] : " . json_encode($got));

                $json = json_decode($response, true);
                if (is_array($json)) {
                    // over
                    break;
                }
            }

            // this might got none
            //$response=stream_get_contents($client);

            DaemonHelper::clientLog("DEBUG", "GET RESPONSE: ", $response);

            return $response;
        });
    }
}