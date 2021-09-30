<?php

namespace Curl;

class MultiCurl
{
    public $baseUrl = null;
    public $multiCurl;

    private $curls = array();
    private $activeCurls = array();
    private $isStarted = false;
    private $concurrency = 25;
    private $nextCurlId = 0;

    private $beforeSendFunction = null;
    private $successFunction = null;
    private $errorFunction = null;
    private $completeFunction = 