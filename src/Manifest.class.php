<?php

namespace Manifesto;

require_once dirname(__FILE__) . '/common.inc.php';

class Manifest
{

    protected $callbackLocation;
    protected $callbackMethod = 'GET';
    protected $fileCount;
    protected $files = array();
    protected $format;
    protected $customData = array();
    protected $safeMode = false;

    protected $validFormats = array(FORMAT_ZIP, FORMAT_TARGZ, FORMAT_TARBZ);

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

    /**
     * @return array
     * @throws Exceptions\ManifestValidationException
     */
    public function generateManifest()
    {
        if(empty($this->files))
        {
            throw new \Manifesto\Exceptions\ManifestValidationException("No files have been added to manifest");
        }

        if(!in_array($this->format, $this->validFormats))
        {
            throw new \Manifesto\Exceptions\ManifestValidationException("Output format has not been set");
        }

        if($this->safeMode && (!isset($this->fileCount) || empty($this->fileCount)))
        {
            throw new \Manifesto\Exceptions\ManifestValidationException("File count must be set in safe mode");
        }
        elseif(!($this->safeMode && isset($this->fileCount)))
        {
            $this->fileCount = count($this->files);
        }

        if($this->fileCount != count($this->files))
        {
            throw new \Manifesto\Exceptions\ManifestValidationException("Number of files does not equal fileCount");
        }

        $manifest = array();
        if(isset($this->callbackLocation))
        {
            $manifest['callback'] = array('url'=>$this->callbackLocation, 'method'=>$this->callbackMethod);
        }

        if(isset($this->customData) && !empty($this->customData))
        {
            $manifest['customData'] = $this->customData;
        }

        $manifest['format'] = $this->format;

        $manifest['fileCount'] = $this->fileCount;

        $manifest['files'] = $this->files;

        return $manifest;
    }

    /**
     * @return array
     */
    public function getCustomData()
    {
        return $this->customData;
    }

    /**
     * @param array $customData
     * @throws \InvalidArgumentException
     */
    public function setCustomData(array $customData)
    {
        foreach($customData as $key=>$val)
        {
            if(!is_scalar($val))
            {
                throw new \InvalidArgumentException("Values of custom data must be a string, numeric, or boolean");
            }
        }
        $this->customData = $customData;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param array $file
     * @throws \InvalidArgumentException
     */
    public function addFile(array $file)
    {
        if(!array_key_exists('file', $file) || empty($file['file']))
        {
            throw new \InvalidArgumentException("Files must contain a file key and value");
        }

        if(array_key_exists('type', $file) && !in_array($file['type'], array(FILE_TYPE_S3, FILE_TYPE_CF)))
        {
            throw new \InvalidArgumentException("Unsupported file 'type'");
        }
        $this->files[] = $file;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @throws \InvalidArgumentException
     */
    public function setFormat($format)
    {
        if(!in_array($format, $this->validFormats))
        {
            throw new \InvalidArgumentException("'{$format}' is not supported");
        }
        $this->format = $format;
    }

    /**
     * @return mixed
     */
    public function getCallbackLocation()
    {
        return $this->callbackLocation;
    }

    /**
     * @param mixed $callbackLocation
     * @throws \InvalidArgumentException
     */
    public function setCallbackLocation($callbackLocation)
    {
        if(!(filter_var($callbackLocation, FILTER_VALIDATE_URL) && preg_match("/^https?:\/\//", $callbackLocation)))
        {
            throw new \InvalidArgumentException("Callback location must be an http or https url");
        }
        $this->callbackLocation = $callbackLocation;
    }

    /**
     * @return string
     */
    public function getCallbackMethod()
    {
        return $this->callbackMethod;
    }

    /**
     * @param string $callbackMethod
     * @throws \InvalidArgumentException
     */
    public function setCallbackMethod($callbackMethod)
    {
        if(!in_array(strtoupper($callbackMethod), array("GET", "POST")))
        {
            throw new \InvalidArgumentException("Callback method must be GET or POST");
        }
        $this->callbackMethod = strtoupper($callbackMethod);
    }

}