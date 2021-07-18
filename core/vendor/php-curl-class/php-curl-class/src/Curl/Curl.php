
<?php

namespace Curl;

use Curl\ArrayUtil;
use Curl\Decoder;

class Curl
{
    const VERSION = '8.0.0';
    const DEFAULT_TIMEOUT = 30;

    public $curl;
    public $id = null;

    public $error = false;
    public $errorCode = 0;
    public $errorMessage = null;

    public $curlError = false;