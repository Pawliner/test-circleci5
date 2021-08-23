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

        // Call json_dec