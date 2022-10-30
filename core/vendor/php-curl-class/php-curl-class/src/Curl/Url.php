<?php

namespace Curl;

use Curl\StrUtil;

class Url
{
    private $baseUrl = null;
    private $relativeUrl = null;

    public function __construct($base_url, $relative_url = null)
    {
        $this->baseUrl = $base_url;
        $this->r