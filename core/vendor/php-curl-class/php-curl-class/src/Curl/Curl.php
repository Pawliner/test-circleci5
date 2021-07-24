
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
    public $curlErrorCode = 0;
    public $curlErrorMessage = null;

    public $httpError = false;
    public $httpStatusCode = 0;
    public $httpErrorMessage = null;

    public $url = null;
    public $requestHeaders = null;
    public $responseHeaders = null;
    public $rawResponseHeaders = '';
    public $responseCookies = array();
    public $response = null;
    public $rawResponse = null;

    public $beforeSendFunction = null;
    public $downloadCompleteFunction = null;
    public $successFunction = null;
    public $errorFunction = null;
    public $completeFunction = null;
    public $fileHandle = null;

    public $attempts = 0;
    public $retries = 0;
    public $isChildOfMultiCurl = false;
    public $remainingRetries = 0;
    public $retryDecider = null;

    private $cookies = array();
    private $headers = array();
    private $options = array();

    private $jsonDecoder = '\Curl\Decoder::decodeJson';
    private $jsonDecoderArgs = array();
    private $jsonPattern = '/^(?:application|text)\/(?:[a-z]+(?:[\.-][0-9a-z]+){0,}[\+\.]|x-)?json(?:-[a-z]+)?/i';
    private $xmlDecoder = '\Curl\Decoder::decodeXml';
    private $xmlPattern = '~^(?:text/|application/(?:atom\+|rss\+)?)xml~i';
    private $defaultDecoder = null;

    public static $RFC2616 = array(
        // RFC 2616: "any CHAR except CTLs or separators".
        // CHAR           = <any US-ASCII character (octets 0 - 127)>
        // CTL            = <any US-ASCII control character
        //                  (octets 0 - 31) and DEL (127)>
        // separators     = "(" | ")" | "<" | ">" | "@"
        //                | "," | ";" | ":" | "\" | <">
        //                | "/" | "[" | "]" | "?" | "="
        //                | "{" | "}" | SP | HT
        // SP             = <US-ASCII SP, space (32)>
        // HT             = <US-ASCII HT, horizontal-tab (9)>
        // <">            = <US-ASCII double-quote mark (34)>
        '!', '#', '$', '%', '&', "'", '*', '+', '-', '.', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B',
        'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q',
        'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '|', '~',
    );
    public static $RFC6265 = array(
        // RFC 6265: "US-ASCII characters excluding CTLs, whitespace DQUOTE, comma, semicolon, and backslash".
        // %x21
        '!',
        // %x23-2B
        '#', '$', '%', '&', "'", '(', ')', '*', '+',
        // %x2D-3A
        '-', '.', '/', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':',
        // %x3C-5B
        '<', '=', '>', '?', '@', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q',
        'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '[',
        // %x5D-7E
        ']', '^', '_', '`', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r',
        's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '{', '|', '}', '~',
    );

    private static $deferredProperties = array(
        'effectiveUrl',
        'rfc2616',
        'rfc6265',
        'totalTime',
    );

    /**
     * Construct
     *
     * @access public
     * @param  $base_url
     * @throws \ErrorException
     */
    public function __construct($base_url = null)
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        $this->curl = curl_init();
        $this->id = uniqid('', true);
        $this->setDefaultUserAgent();
        $this->setDefaultTimeout();
        $this->setOpt(CURLINFO_HEADER_OUT, true);

        // Create a placeholder to temporarily store the header callback data.
        $header_callback_data = new \stdClass();
        $header_callback_data->rawResponseHeaders = '';
        $header_callback_data->responseCookies = array();
        $this->headerCallbackData = $header_callback_data;
        $this->setOpt(CURLOPT_HEADERFUNCTION, $this->createHeaderCallback($header_callback_data));

        $this->setOpt(CURLOPT_RETURNTRANSFER, true);
        $this->headers = new CaseInsensitiveArray();
        $this->setUrl($base_url);
    }

    /**
     * Before Send
     *
     * @access public
     * @param  $callback
     */
    public function beforeSend($callback)
    {
        $this->beforeSendFunction = $callback;
    }

    /**
     * Build Post Data
     *
     * @access public
     * @param  $data
     *
     * @return array|string
     */
    public function buildPostData($data)
    {
        $binary_data = false;
        if (is_array($data)) {
            // Return JSON-encoded string when the request's content-type is JSON.
            if (isset($this->headers['Content-Type']) &&
                preg_match($this->jsonPattern, $this->headers['Content-Type'])) {
                $json_str = json_encode($data);
                if (!($json_str === false)) {
                    $data = $json_str;
                }
            } else {
                // Manually build a single-dimensional array from a multi-dimensional array as using curl_setopt($ch,
                // CURLOPT_POSTFIELDS, $data) doesn't correctly handle multi-dimensional arrays when files are
                // referenced.
                if (ArrayUtil::is_array_multidim($data)) {
                    $data = ArrayUtil::array_flatten_multidim($data);
                }

                // Modify array values to ensure any referenced files are properly handled depending on the support of
                // the @filename API or CURLFile usage. This also fixes the warning "curl_setopt(): The usage of the
                // @filename API for file uploading is deprecated. Please use the CURLFile class instead". Ignore
                // non-file values prefixed with the @ character.
                foreach ($data as $key => $value) {
                    if (is_string($value) && strpos($value, '@') === 0 && is_file(substr($value, 1))) {
                        $binary_data = true;
                        if (class_exists('CURLFile')) {
                            $data[$key] = new \CURLFile(substr($value, 1));
                        }
                    } elseif ($value instanceof \CURLFile) {
                        $binary_data = true;
                    }
                }
            }
        }