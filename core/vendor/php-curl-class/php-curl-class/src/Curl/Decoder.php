<?php

namespace Curl;

class Decoder
{
    /**
     * Decode JSON
     *
     * @access public
     * @param  $json
     * @param  $assoc
     * @param  $depth
     * @param  $options
     */
    public static function decodeJson()
    {
        $args = func_get_args();

        // Call json_decode() without the $options parameter in PHP
        // versions less than 5.4.0 as the $options parameter was added in
        // PHP version 5.4.0.
       