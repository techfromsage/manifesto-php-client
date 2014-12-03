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


    }

}