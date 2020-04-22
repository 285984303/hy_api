<?php namespace App\Library\AliSMS\Top;
class ApplicationVar
{
    var $save_file;
    var $application = NULL;
    var $app_data    = '';
    var $__writed    = FALSE;

    function __construct()
    {
        $this->save_file   = __DIR__.'/httpdns.conf';
        $this->application = [];
    }

    public function setValue($var_name, $var_value)
    {
        if (!is_string($var_name) || empty($var_name))
            return FALSE;

        $this->application[$var_name] = $var_value;
    }

    public function write()
    {
        $this->app_data = @serialize($this->application);
        $this->__writeToFile();
    }

    function __writeToFile()
    {
        $fp = @fopen($this->save_file, "w");
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            @fwrite($fp, $this->app_data);
            flock($fp, LOCK_UN);
        }
        @fclose($fp);
    }

    public function getValue()
    {
        if (!is_file($this->save_file))
            $this->__writeToFile();

        return @unserialize(@file_get_contents($this->save_file));
    }
}

?>