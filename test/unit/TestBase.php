<?php

if (!defined('APPROOT'))
{
    define('APPROOT', dirname(dirname(__DIR__)));
}

//echo dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';
//require_once dirname(dirname(dirname(__FILE__))) . '/vendor/autoload.php';

class TestBase extends PHPUnit_Framework_TestCase {

}