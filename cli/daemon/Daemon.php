<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2017/11/24
 * Time: 11:50
 */

namespace sinri\InfuraOffice\cli\daemon;


use sinri\enoch\core\LibLog;
use sinri\InfuraOffice\toolkit\InfuraOfficeToolkit;

class Daemon
{
    /**
     * @var SocketAgent
     */
    protected $socketAgent;

    protected $workers = [];
    protected $max_workers = 0;

    public function __construct($socketAgent)
    {
        $this->socketAgent = $socketAgent;
        $this->workers = [];
        $this->max_workers = InfuraOfficeToolkit::readConfig(['daemon', 'max_workers'], 0);
    }

    /**
     * @throws \Exception
     */
    public function listen()
    {
        $this->socketAgent->runServer(function ($client) {
            $pairName = stream_socket_get_name($client, true);
            DaemonHelper::log("INFO", 'Accepted from ' . $pairName);

            //stream_set_timeout($client, 0, 100000);
            stream_set_timeout($client, 60);

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
                if ($this->max_workers <= count($this->workers)) {
                    $options = 0;
                    while (count($this->workers) > 0) {
                        DaemonHelper::log(LibLog::LOG_WARNING, "Reached MAX-WORKERS (limit is {$this->max_workers}), waiting...", count($this->workers));
                        $done_pid = pcntl_wait($status, $options);//(WNOHANG | WUNTRACED));
                        DaemonHelper::log(LibLog::LOG_INFO, "WAITED and saw pid " . $done_pid . " exited with status ", $status);
                        DaemonHelper::log(LibLog::LOG_INFO, "STATUS DESCRIPTION: " . DaemonHelper::describePcntlWaitStatus($status));
                        if ($done_pid == 0) {
                            DaemonHelper::log(LibLog::LOG_INFO, "WNOHANG so zero returned... break");
                            break;
                        } elseif ($done_pid < 0) {
                            DaemonHelper::log(LibLog::LOG_ERROR, "pcntl error so -1 returned... break");
                            $last_error_number = "?";
                            $last_error_string = "?";
                            if (function_exists("pcntl_get_last_error")) {
                                $last_error_number = pcntl_get_last_error();
                                if (function_exists("pcntl_strerror")) {
                                    $last_error_string = pcntl_strerror($last_error_number);
                                }
                            }

                            DaemonHelper::log(LibLog::LOG_ERROR, "LAST PCNTL ERROR: " . $last_error_string, $last_error_number);
                            break;
                        }
                        if ($done_pid && isset($this->workers[$done_pid])) {
                            unset($this->workers[$done_pid]);
                        }
                        DaemonHelper::log(LibLog::LOG_INFO, "After unset worker set, current workers", count($this->workers));
                        $options = (WNOHANG | WUNTRACED);
                    }
                }
                return SocketAgent::SERVER_CALLBACK_COMMAND_NONE;
            } elseif ($code == '200') {
                $send_content = json_encode(['code' => $code, 'data' => $responseBody]);
                for ($written = 0; $written < strlen($send_content); $written += $partWrittenBytes) {
                    $partWrittenBytes = fwrite($client, substr($send_content, $written));
                    if ($partWrittenBytes === false || $partWrittenBytes === 0) {
                        // only check false would lead to infinite loop so I add zero check to break
                        DaemonHelper::log(LibLog::LOG_ERROR, "write to client failed", [$partWrittenBytes]);
                        break;
                    }
                }
                $finClientMetaData = stream_get_meta_data($client);
                DaemonHelper::log(LibLog::LOG_DEBUG, 'finClientMetaData', [$finClientMetaData]);
                //fflush($client);
                $closed = fclose($client);
                DaemonHelper::log("DEBUG", "Writing Over. Total " . strlen($send_content) . " bytes while " . $written . " bytes written!");
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
            $this->workers[$pid] = $pid;
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