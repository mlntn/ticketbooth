<?php

namespace Mlntn;

class TicketBooth
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $time_limit;

    public function __construct($time_limit = 60, $path = '.', $file = '.ticket')
    {
        $this->path = $path;
        $this->file = $file;
        $this->time_limit = $time_limit;
    }

    /**
     * @throws TicketException
     */
    public function claim()
    {
        if ($this->check()) {
            $this->touch();

            register_shutdown_function([ $this, 'release']);
        }
        else {
            throw new TicketException("Another process has the ticket right now");
        }
    }

    public function renew()
    {
        if ($this->checkPid()) {
            $this->touch();
        }
        else {
            throw new TicketException("Another process has the ticket right now");
        }
    }

    protected function touch()
    {
        $this->setPid();
    }

    public function release()
    {
        unlink($this->getFilePath());
    }

    protected function checkPid()
    {
        return $this->getPid() == getmypid();
    }

    protected function getPid()
    {
        $h = fopen($this->getFilePath(), 'r');

        $pid = fread($h, 16);

        fclose($h);

        return (int) $pid;
    }

    protected function setPid()
    {
        $h = fopen($this->getFilePath(), 'w');

        fwrite($h, getmypid());

        fclose($h);
    }

    /**
     * @return bool
     */
    public function check()
    {
        return $this->getFileTime() + $this->time_limit < time();
    }

    /**
     * @return int
     */
    protected function getFileTime()
    {
        $file = $this->getFilePath();

        if (file_exists($file)) {
            return filemtime($file);
        }

        return 0;
    }

    /**
     * @return string
     */
    protected function getFilePath()
    {
        return realpath($this->path) . '/' . $this->file;
    }

}
