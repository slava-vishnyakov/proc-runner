<?php

require __DIR__ . '/RunnerInterface.php';

class Runner implements RunnerInterface
{
    const PROCESS_WENT_AWAY = "Process went away, see readStderrLineIfAny() for any hints";

    private $returnCode;

    public function __construct($command, $commandArguments = null, $workingDirectory = null, $env = [], $procOpenOptions = [])
    {
        $descriptor = [["pipe", "r"], ["pipe", "w"], ["pipe", "w"]];

        $pipes = [];

        $escapedCommands = [$command];
        if (is_array($commandArguments)) {
            foreach ($commandArguments as $arg) {
                array_push($escapedCommands, escapeshellarg($arg));
            }
        }

        $cmd = join(' ', $escapedCommands);
        $this->process = proc_open($cmd, $descriptor, $pipes, $workingDirectory, $env, $procOpenOptions);

        $this->stdin = $pipes[0];
        $this->stdout = $pipes[1];
        $this->stderr = $pipes[2];

        stream_set_blocking($this->stdin, 0);
        stream_set_blocking($this->stdout, 0);
        stream_set_blocking($this->stderr, 0);

        if (!$this->isAlive()) {
            throw new RuntimeException("Process cannot be started");
        }
    }

    public function isAlive()
    {
        $status = proc_get_status($this->process);
        return $status['running'] === true;
    }

    public function send($string)
    {
        $bytesSent = fwrite($this->stdin, $string);
        if ($bytesSent == 0 && strlen($string) > 0) {
            throw new RuntimeException(self::PROCESS_WENT_AWAY);
        }
    }

    protected function _readLine($pipe, $timeout = null)
    {
        $start = microtime(true);
        while (true) {
            if (!$this->isAlive()) {
                throw new RuntimeException(self::PROCESS_WENT_AWAY);
            }
            $string = fgets($pipe);
            if ($string != '') {
                return $string;
            }

            $null = [];
            $read = [$pipe];
            stream_select($read, $null, $null, $timeout == null ? 1.0 : ($timeout / 3));

            if (microtime(true) - $start > $timeout) {
                return '';
            }
        }
    }

    public function readStderrLine($timeout = null)
    {
        return $this->_readLine($this->stderr, $timeout);
    }

    public function readLine($timeout = null)
    {
        return $this->_readLine($this->stdout, $timeout);
    }

    public function readLineIfAny()
    {
        if (!is_resource($this->stdout)) {
            return '';
        }
        return fgets($this->stdout);
    }

    public function readStderrLineIfAny()
    {
        if (!is_resource($this->stderr)) {
            return '';
        }
        return fgets($this->stderr);
    }

    public function close()
    {
        $this->returnCode = proc_close($this->process);
        return $this->returnCode;
    }

    public function getReturnValue()
    {
        if (isset($this->returnCode)) {
            return $this->returnCode;
        }
        $status = proc_get_status($this->process);
        return $status['exitcode'];
    }
}
