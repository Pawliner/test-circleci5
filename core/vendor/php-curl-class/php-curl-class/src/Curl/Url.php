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
        $this->relativeUrl = $relative_url;
    }

    public function __toString()
    {
        return $this->absolutizeUrl();
    }

    /**
     * Remove dot segments.
     *
     * Interpret and remove the special "." and ".." path segments from a referenced path.
     */
    public static function removeDotSegments($input)
    {
        // 1.  The input buffer is initialized with the now-appended path
        //     components and the output buffer is initialized to the empty
        //     string.
        $output = '';

        // 2.  While the input buffer is not empty, loop as follows:
        while (!empty($input)) {
            // A.  If the input buffer begins with a prefix of "../" or "./",
            //     then remove that prefix from the input buffer; otherwise,
            if (StrUtil::startsWith($input, '../')) {
                $input = substr($input, 3);
            } elseif (StrUtil::startsWith($input, './')) {
                $input = substr($input, 2);

            // B.  if the input buffer begins with a prefix of "/./" or "/.",
            //     where "." is a complete path segment, then replace that
            //     prefix with "/" in the input buffer; otherwise,
            } elseif (StrUtil::startsWith($input, '/./')) {
                $input = substr($input, 2);
            } elseif ($input === '/.') {
                $input = '/';

            // C.  if the input buffer begins with a prefix of "/../" or "/..",
            //     where ".." is a complete path segment, then replace that
            //     prefix with "/" in the input buffer and remove the last
            //     segment and its preceding "/" (if any) from the output
            //     buffer; otherwise,
            } elseif (StrUtil::startsWith($input, '/../')) {
                $input = substr($input, 3);
                $output = substr_replace($output, '', mb_strrpos($output, '/'));
            } else