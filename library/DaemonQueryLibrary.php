<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 16:05
 */

namespace sinri\InfuraOffice\library;


use sinri\enoch\helper\CommonHelper;
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
     * @throws \Exception
     */
    public function query($content, $timeoutInSeconds = 1)
    {
        return $this->socketAgent->runClient(function ($client) use ($content, $timeoutInSeconds) {
            $pairName = stream_socket_get_name($client, true);
            DaemonHelper::clientLog("INFO", "Client linked to " . $pairName);
            //stream_set_timeout($client, 0, 100000);
            stream_set_timeout($client, $timeoutInSeconds);//timeout as five seconds by default

            $content = json_encode($content);
            fwrite($client, $content);
            fflush($client);
            $response = '';
            DaemonHelper::clientLog("INFO", "Request Sent", $content);
            $one_month_size = 1024;
            while (!feof($client)) {
                DaemonHelper::clientLog("DEBUG", "Waiting for response... one month size = {$one_month_size}");
                $meta = stream_get_meta_data($client);
                DaemonHelper::clientLog('DEBUG', 'read once meta before: ' . json_encode($meta));

                $got = fread($client, $one_month_size);
                $response .= $got;
                DaemonHelper::clientLog("DEBUG", "read from [{$pairName}] : " . json_encode($got));

                $meta = stream_get_meta_data($client);
                DaemonHelper::clientLog('DEBUG', 'read once meta after: ' . json_encode($meta));

                if ($meta['unread_bytes'] == 0) {
                    DaemonHelper::clientLog('DEBUG', 'meta unread bytes zero now!');
                } elseif ($meta['unread_bytes'] < $one_month_size) {
                    $one_month_size = $meta['unread_bytes'];
                }

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

    /**
     * @param $response
     * @param null $error
     * @return bool|string|null
     */
    public function parseResponse($response, &$error = null)
    {
        $error = null;
        $parsed = json_decode($response, true);
        if (!$parsed) {
            $error = 'response is not json';
            return false;
        }
        $query_code = CommonHelper::safeReadArray($parsed, 'code', 600);
        $return_var = CommonHelper::safeReadNDArray($parsed, ['data', 'return_var'], 255);
        $output = CommonHelper::safeReadNDArray($parsed, ['data', 'output'], '');
        if ($query_code != 200) {
            $error = CommonHelper::safeReadArray($parsed, 'data', 'code is not 200');
            return false;
        }
        if ($return_var != 0) {
            $error = "command not exit with 0 ({$return_var})";
        }
        return $output;
    }
}