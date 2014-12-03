<?php

require_once 'TestBase.php';

class ManifestTest extends TestBase {
    public function testGetSetSafeMode()
    {
        $m = new \Manifesto\Manifest();
        $this->assertFalse($m->getSafeMode());
        $m->setSafeMode(true);
        $this->assertTrue($m->getSafeMode());

        $m2 = new \Manifesto\Manifest(true);
        $this->assertTrue($m2->getSafeMode());
    }

    public function testGetSetFileCount()
    {
        $m = new \Manifesto\Manifest();
        $m->setFileCount(12);
        $this->assertEquals(12, $m->getFileCount());
    }

    public function testGetSetCustomData()
    {
        $m = new \Manifesto\Manifest();
        $customData = array('email'=>'user@example.com','foo'=>3, 'bar'=>true);
        $m->setCustomData($customData);
        $this->assertEquals($customData, $m->getCustomData());
    }

    public function testValidateCustomData()
    {
        $this->setExpectedException('InvalidArgumentException', 'Values of custom data must be a string, numeric, or boolean');
        $m = new \Manifesto\Manifest();
        $m->setCustomData(array('foo'=>array('bar')));
    }

    public function testGetSetFormat()
    {
        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $this->assertEquals(FORMAT_ZIP, $m->getFormat());
    }

    public function testValidateSetFormat()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "'wibble' is not supported");
        $m->setFormat('wibble');
    }

    public function testGetSetCallbackLocation()
    {
        $m = new \Manifesto\Manifest();
        $m->setCallbackLocation('https://example.com/callback.cgi');
        $this->assertEquals('https://example.com/callback.cgi', $m->getCallbackLocation());
    }

    public function testValidateSetCallbackLocation()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "Callback location must be an http or https url");
        $m->setCallbackLocation('telnet://wibble');
    }

    public function testGetSetCallbackMethod()
    {
        $m = new \Manifesto\Manifest();
        $this->assertEquals('GET', $m->getCallbackMethod());
        $m->setCallbackMethod('POST');
        $this->assertEquals('POST', $m->getCallbackMethod());
        $m->setCallbackMethod('get');
        $this->assertEquals('GET', $m->getCallbackMethod());
    }

    public function testValidateSetCallbackMethod()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "Callback method must be GET or POST");
        $m->setCallbackMethod("PUT");
    }

    public function testAddGetFiles()
    {
        $m = new \Manifesto\Manifest();
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->assertEquals($files, $m->getFiles());
    }

    public function testValidateAddFileWithNoFileKey()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "Files must contain a file key and value");
        $m->addFile(array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'destinationPath'=>'foobar.txt'));
    }

    public function testValidateAddFileWithNoFileValue()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "Files must contain a file key and value");
        $m->addFile(array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>null, 'destinationPath'=>'foobar.txt'));
    }

    public function testValidateAddFileWithUnsupportedFileType()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('InvalidArgumentException', "Unsupported file 'type'");
        $m->addFile(array('type'=>"MY_FOO_CLOUD", 'container'=>'myBucket', 'file'=>'/path/to/file.txt', 'destinationPath'=>'foobar.txt'));
    }

    public function testGenerateManifestNoFiles()
    {
        $m = new \Manifesto\Manifest();
        $this->setExpectedException('Manifesto\Exceptions\ManifestValidationException', 'No files have been added to manifest');
        $m->generateManifest();
    }

    public function testGenerateManifestNoFormat()
    {
        $m = new \Manifesto\Manifest();
        $m->addFile(array('file'=>'/path/to/file1.txt'));
        $this->setExpectedException('Manifesto\Exceptions\ManifestValidationException', 'Output format has not been set');
        $m->generateManifest();
    }

    public function testGenerateManifestNoFileCountInSafeMode()
    {
        $m = new \Manifesto\Manifest(true);
        $m->addFile(array('file'=>'/path/to/file1.txt'));
        $m->setFormat(FORMAT_TARBZ);
        $this->setExpectedException('Manifesto\Exceptions\ManifestValidationException', 'File count must be set in safe mode');
        $m->generateManifest();
    }

    public function testGenerateManifestWrongFileCountInSafeMode()
    {
        $m = new \Manifesto\Manifest(true);
        $m->addFile(array('file'=>'/path/to/file1.txt'));
        $m->setFormat(FORMAT_TARBZ);
        $m->setFileCount(3);
        $this->setExpectedException('Manifesto\Exceptions\ManifestValidationException', 'Number of files does not equal fileCount');
        $m->generateManifest();
    }

    public function testBasicManifest()
    {
        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $expectedManifest = array(
            'format'=>FORMAT_ZIP,
            'fileCount'=>3,
            'files'=>$files
        );

        $this->assertEquals($expectedManifest, $m->generateManifest());
    }

    public function testAdvancedManifest()
    {
        $m = new \Manifesto\Manifest(true);
        $m->setFormat(FORMAT_TARBZ);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $m->setFileCount(3);

        $m->setCallbackLocation('https://example.com/callback.cgi');
        $m->setCallbackMethod('post');

        $customData = array(
            'email'=>'user@example.com',
            'foo'=>'bar',
            'baz'=>true
        );

        $m->setCustomData($customData);

        $expectedManifest = array(
            'callback'=>array('url'=>'https://example.com/callback.cgi', 'method'=>'POST'),
            'customData'=>$customData,
            'format'=>FORMAT_TARBZ,
            'fileCount'=>3,
            'files'=>$files
        );

        $this->assertEquals($expectedManifest, $m->generateManifest());
    }
}