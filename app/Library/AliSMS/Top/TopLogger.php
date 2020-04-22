<?php namespace App\Library\AliSMS\Top;

class TopLogger
{
    public $conf = [
        "separator" => "\t",
        "log_file"  => "",
    ];

    private $fileHandle;

    public function log($logData)
    {
        if ("" == $logData || [] == $logData) {
            return FALSE;
        }
        if (is_array($logData)) {
            $logData = implode($this->conf["separator"], $logData);
        }
        $logData = $logData."\n";
        fwrite($this->getFileHandle(), $logData);
    }

    protected function getFileHandle()
    {
        if (NULL === $this->fileHandle) {
            if (empty($this->conf["log_file"])) {
                trigger_error("no log file spcified.");
            }
            $logDir = dirname($this->conf["log_file"]);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, TRUE);
            }
            $this->fileHandle = fopen($this->conf["log_file"], "a");
        }

        return $this->fileHandle;
    }
}

?>