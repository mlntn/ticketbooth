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
            $this->renew();
        }
        else {
            throw new TicketException("Another process has the ticket right now");
        }
    }

    public function renew()
    {
        touch($this->getFilePath());
    }

    public function release()
    {
        unlink($this->getFilePath());
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

