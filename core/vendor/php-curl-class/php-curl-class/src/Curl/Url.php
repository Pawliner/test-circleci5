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
            } elseif ($input === '/..') {
                $input = '/';
                $output = substr_replace($output, '', mb_strrpos($output, '/'));

            // D.  if the input buffer consists only of "." or "..", then remove
            //     that from the input buffer; otherwise,
            } elseif ($input === '.' || $input === '..') {
                $input = '';

            // E.  move the first path segment in the input buffer to the end of
            //     the output buffer, including the initial "/" character (if
            //     any) and any subsequent characters up to, but not including,
            //     the next "/" character or the end of the input buffer.
            } elseif (!(($pos = mb_strpos($input, '/', 1)) === false)) {
                $output .= substr($input, 0, $pos);
                $input = substr_replace($input, '', 0, $pos);
            } else {
                $output .= $input;
                $input = '';
            }
        }

        // 3.  Finally, the output buffer is returned as the result of
        //     remove_dot_segments.
        return $output . $input;
    }

    /**
     * Absolutize url.
     *
     * Combine the base and relative url into an absolute url.
     */
    private function absolutizeUrl()
    {
        $b = $this->parseUrl($this->baseUrl);

        if (!($this->relativeUrl === null)) {
            $r = $this->parseUrl($this->relativeUrl);

            // Copy relative parts to base url.
            if (isset($r['scheme'])) {
                $b['scheme'] = $r['scheme'];
            }
            if (isset($r['host'])) {
                $b['host'] = $r['host'];
    