
# PHP Curl Class: HTTP requests made easy

[![](https://img.shields.io/github/release/php-curl-class/php-curl-class.svg)](https://github.com/php-curl-class/php-curl-class/releases/)
[![](https://img.shields.io/github/license/php-curl-class/php-curl-class.svg)](https://github.com/php-curl-class/php-curl-class/blob/master/LICENSE)
[![](https://img.shields.io/travis/php-curl-class/php-curl-class.svg)](https://travis-ci.org/php-curl-class/php-curl-class/)
[![](https://img.shields.io/packagist/dt/php-curl-class/php-curl-class.svg)](https://github.com/php-curl-class/php-curl-class/releases/)

PHP Curl Class makes it easy to send HTTP requests and integrate with web APIs.

![PHP Curl Class screencast](www/img/screencast.gif)

---

- [Installation](#installation)
- [Requirements](#requirements)
- [Quick Start and Examples](#quick-start-and-examples)
- [Available Methods](#available-methods)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Run Tests](#run-tests)
- [Contribute](#contribute)

---

### Installation

To install PHP Curl Class, simply:

    $ composer require php-curl-class/php-curl-class

For latest commit version:

    $ composer require php-curl-class/php-curl-class @dev

### Requirements

PHP Curl Class works with PHP 5.3, 5.4, 5.5, 5.6, 7.0, 7.1, and HHVM.

### Quick Start and Examples

More examples are available under [/examples](https://github.com/php-curl-class/php-curl-class/tree/master/examples).

```php
require __DIR__ . '/vendor/autoload.php';

use \Curl\Curl;

$curl = new Curl();
$curl->get('https://www.example.com/');

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}
```

```php
// https://www.example.com/search?q=keyword
$curl = new Curl();
$curl->get('https://www.example.com/search', array(
    'q' => 'keyword',
));
```

```php
$curl = new Curl();
$curl->post('https://www.example.com/login/', array(
    'username' => 'myusername',
    'password' => 'mypassword',
));
```

```php
$curl = new Curl();
$curl->setBasicAuthentication('username', 'password');
$curl->setUserAgent('MyUserAgent/0.0.1 (+https://www.example.com/bot.html)');
$curl->setReferrer('https://www.example.com/url?url=https%3A%2F%2Fwww.example.com%2F');
$curl->setHeader('X-Requested-With', 'XMLHttpRequest');
$curl->setCookie('key', 'value');
$curl->get('https://www.example.com/');

if ($curl->error) {
    echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
} else {
    echo 'Response:' . "\n";
    var_dump($curl->response);
}

var_dump($curl->requestHeaders);
var_dump($curl->responseHeaders);
```

```php
$curl = new Curl();
$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
$curl->get('https://shortn.example.com/bHbVsP');
```

```php
$curl = new Curl();
$curl->put('https://api.example.com/user/', array(
    'first_name' => 'Zach',
    'last_name' => 'Borboa',
));
```

```php
$curl = new Curl();
$curl->patch('https://api.example.com/profile/', array(
    'image' => '@path/to/file.jpg',
));
```

```php
$curl = new Curl();
$curl->patch('https://api.example.com/profile/', array(
    'image' => new CURLFile('path/to/file.jpg'),
));
```

```php
$curl = new Curl();
$curl->delete('https://api.example.com/user/', array(
    'id' => '1234',
));
```

```php
// Enable all supported encoding types and download a file.
$curl = new Curl();
$curl->setOpt(CURLOPT_ENCODING , '');
$curl->download('https://www.example.com/file.bin', '/tmp/myfile.bin');
```

```php
// Case-insensitive access to headers.
$curl = new Curl();
$curl->download('https://www.example.com/image.png', '/tmp/myimage.png');
echo $curl->responseHeaders['Content-Type'] . "\n"; // image/png
echo $curl->responseHeaders['CoNTeNT-TyPE'] . "\n"; // image/png
```

```php
// Clean up.
$curl->close();
```

```php
// Example access to curl object.
curl_set_opt($curl->curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1');
curl_close($curl->curl);
```