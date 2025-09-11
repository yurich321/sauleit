<?php

namespace App\Core;

class Logger
{
    private static Logger $instance;
    private ?string $logFile = null;
    private ?int $logLevel = null;

    private array $logData = [];
    const DEBUG = 1;
    const INFO = 2;
    const WARNING = 4;
    const ERROR = 8;
    const CRITICAL = 16;
    const ALL = 31;

    public static function getInstance(): Logger
    {
        if (!isset(self::$instance)) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    private function __construct()
    {
        //echo $this->logFile = constant('ROOT_DIR') . '/storage/logs/' . date('Y-m-d');
        if (!dir(constant('ROOT_DIR') . '/storage/logs/')) {
            mkdir(constant('ROOT_DIR') . '/storage/logs/', 0775, true);
        }
        $this->logFile = constant('ROOT_DIR') . '/storage/logs/' . date('Y-m-d') . '.log';
        $this->logLevel = self::ALL;

        register_shutdown_function([$this, 'writeToFile']);
    }

    public function writeToFile(): void
    {
        file_put_contents($this->logFile, implode('', $this->logData), FILE_APPEND);

        // clear log data
        $this->logData = [];
    }


    public function log(mixed $message, int $level = self::DEBUG): void
    {
        // bitwise AND log level comparison
        if ($this->logLevel & $level) {
            $this->write($message, $level);
        }
    }

    private function write(mixed $message, int $level = self::DEBUG): void
    {
        // level int to string
        $levelStr = match ($level) {
            self::INFO => 'INFO',
            self::WARNING => 'WARNING',
            self::ERROR => 'ERROR',
            self::CRITICAL => 'CRITICAL',
            default => 'DEBUG',
        };

        if (is_array($message) && count($message) === 1) {
            $message = $message[0];
        }

        if (is_array($message) || is_object($message)) {

            // loop over array or object and add timestamp
            $message = array_map(function ($line) {
                return print_r($line, true);
            }, (array)$message);

            // join lines with new line
            $message = implode(PHP_EOL, $message);
        }

        $log = date('Y-m-d H:i:s') . ' [' . $levelStr . '] ' . $message . PHP_EOL;

        $this->logData[] = $log;
    }

    public function debug(...$message): void
    {
        $this->log($message);
    }

    public function info(...$message): void
    {
        $this->log($message, self::INFO);
    }

    public function warning(...$message): void
    {
        $this->log($message, self::WARNING);
    }

    public function error(...$message): void
    {
        $this->log($message, self::ERROR);
    }

    public function critical(...$message): void
    {
        $this->log($message, self::CRITICAL);
    }

}