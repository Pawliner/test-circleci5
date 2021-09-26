<?php

namespace Curl;

class MultiCurl
{
    public $baseUrl = null;
    public $multiCurl;

    private $curls = array();
    private $activeCurls = a