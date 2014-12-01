<?php

namespace Manifesto;

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/Client.class.php';
require_once dirname(__FILE__) . '/Manifest.class.php';
require_once dirname(__FILE__) . '/exceptions/ManifestValidationException.class.php';

define('FORMAT_ZIP', 'zip');
define('FORMAT_TARGZ', 'targz');
define('FORMAT_TARBZ', 'tarbz');

define('FILE_TYPE_S3', 's3');
define('FILE_TYPE_CF', 'cloudfiles');