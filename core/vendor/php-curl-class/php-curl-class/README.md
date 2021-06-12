
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