<?php

namespace app;

use Exception;

class Logger {
    private $logFile;
    private $fileHandle;

    /**
     * @throws Exception
     */
    public function __construct($logFile = 'log.txt') {
        $this->logFile = $logFile;
        $this->openLogFile();
    }

    /**
     * @throws Exception
     */
    private function openLogFile() {
        $this->fileHandle = fopen($this->logFile, 'a'); // 'a' mode appends to the file or creates it if it doesn't exist
        if ($this->fileHandle === false) {
            throw new Exception("Failed to open log file: $this->logFile");
        }
    }

    public function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message\n";
        fwrite($this->fileHandle, $logMessage);
    }

    public function __destruct() {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}