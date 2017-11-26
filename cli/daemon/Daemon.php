<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 11:50
 */

namespace sinri\InfuraOffice\cli\daemon;


class Daemon
{
    /**
     * @var SocketAgent
     */
    protected $socketAgent;

    public function __construct($socketAgent)
    {
        $this->socketAgent = $socketAgent;
    }

    public function listen()
    {
        $this->socketAgent->runServer(function ($client) {
            $pairName = stream_socket_get_name($client, true);
            DaemonHelper::log("INFO", 'Accepted from ' . $pairName);

            stream_set_timeout($client, 0, 100000);

            $content = '';
            while (!feof($client)) {
                $got = fread($client, 1024);
                $content .= $got;

                $json = json_decode($content, true);
                if (is_array($json)) {
                    // over
                    break;
                }
            }
            DaemonHelper::log("DEBUG", "Yomi received data: " . PHP_EOL . $content . PHP_EOL);

            $contentParsed = json_decode($content, true);
            if (!is_array($contentParsed)) {
                DaemonHelper::log("ERROR", "YomiSingle [{$pairName}] Cannot parse as JSON!");
                fwrite($client, json_encode(['code' => '400', 'data' => 'NOT JSON:' . PHP_EOL . $contentParsed]));
                return SocketAgent::SERVER_CALLBACK_COMMAND_CLOSE_CLIENT;
            }

            if (!isset($contentParsed['type']) || !isset($contentParsed['data'])) {
                DaemonHelper::log("ERROR", "YomiSingle [{$pairName}] Not a correct input!");
                fwrite($client, json_encode(['code' => '400', 'data' => 'NOT CORRECT INPUT']));
                return SocketAgent::SERVER_CALLBACK_COMMAND_CLOSE_CLIENT;
            }

            $type = $contentParsed['type'];
            $data = $contentParsed['data'];

            $request = new DaemonRequest($type, $data);

            $code = $this->handleRequest($request, $responseBody);

            if ($code == '300') {
                DaemonHelper::log("INFO", "For [{$pairName}] has forked a client [{$responseBody}] to handle, parent leaves.");
                return SocketAgent::SERVER_CALLBACK_COMMAND_NONE;
            } elseif ($code == '200') {
                fwrite($client, json_encode(['code' => $code, 'data' => $responseBody]));
                fflush($client);
                $closed = fclose($client);
                DaemonHelper::log("DEBUG", "Try to close client [{$pairName}] and die... closed? " . json_encode($closed));
                exit(0);
            } else {
                //exception, often 500
                fwrite($client, json_encode(['code' => $code, 'data' => $responseBody]));
            }
            return SocketAgent::SERVER_CALLBACK_COMMAND_CLOSE_CLIENT;
        });
    }

    /**
     * @param DaemonRequest $request
     * @param string $body
     * @return int
     * @throws \Exception
     */
    protected function handleRequest($request, &$body = '')
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            //failed
            $body = "CANNOT START HANDLE PROCESS!";
            return '500';
        } elseif ($pid) {
            //as parent
            DaemonHelper::log("INFO", "YomiSingle Created child process [{$pid}]!");
            $body = $pid;
            return '300';
        } else {
            //as child
            $child_pid = getmypid();
            try {
                $handler = $request->getHandlerClass();
                $body = $request->handle(function ($data) use ($child_pid, $handler) {
                    DaemonHelper::log("INFO", "child [{$child_pid}] handle data: " . json_encode($data));
//                    if ($data == 'error') {
//                        throw new \Exception("data is error");
//                    }
                    return $handler->handle($data);
                });
                //$body=json_encode($body);
                return '200';
            } catch (\Exception $exception) {
                $body = $exception->getMessage();
                return '500';
            }
        }
    }
}