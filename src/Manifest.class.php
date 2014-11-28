<?php


namespace Manifesto;


class Manifest
{

    protected $callbackLocation;
    protected $callbackMethod = 'GET';
    protected $fileCount;
    protected $files = array();
    protected $format;
    protected $customData = array();
    protected $safeMode = false;

    public function __construct($safeMode = false)
    {
        $this->safeMode = $safeMode;
    }

    /**
     * @return boolean
     */
    public function getSafeMode()
    {
        return $this->safeMode;
    }

    /**
     * @param boolean $safeMode
     */
    public function setSafeMode($safeMode)
    {
        $this->safeMode = $safeMode;
    }

    /**
     * @return int
     */
    public function getFileCount()
    {
        return $this->fileCount;
    }

    /**
     * @param int $fileCount
     */
    public function setFileCount($fileCount)
    {
        $this->fileCount = $fileCount;
    }


}