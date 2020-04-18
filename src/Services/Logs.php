<?php

namespace App\Services;

class Logs
{
    const INFO  = 'INFO';
    const ERROR = 'ERROR';
    const WARN  = 'WARNING';
    const DEL   = 'DELETION';

    /**
     * @param string|null $logType
     * @param string $data
     */
    public static function createLog(string $data, string $logType = null)
    {
        $directoryPath = ROOT . '/logs/';
        $fileName = $directoryPath . 'logs_' . date('Y-m-d') . '.log';

        Toolset::makeDirectoryIfNotExist($directoryPath);

        file_put_contents($fileName, '[' . date('Y-m-d H:i:s') . '][' . $logType . '] : ' . $data . "\n", FILE_APPEND);
    }
}