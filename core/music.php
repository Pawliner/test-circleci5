
<?php
/**
 *
 * 音乐搜索器 - 函数声明
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.6.2
 *
 */

// 非我族类
if (!defined('MC_CORE')) {
    header("Location: /");
    exit();
}

// 显示 PHP 错误报告
error_reporting(MC_DEBUG);

// 引入 Curl
require MC_CORE_DIR . '/vendor/autoload.php';

// 使用 Curl
use \Curl\Curl;

// Curl 内容获取
function mc_curl($args = [])
{
    $default = [
        'method'     => 'GET',
        'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36',
        'url'        => null,
        'referer'    => 'https://www.google.co.uk',
        'headers'    => null,
        'body'       => null,
        'proxy'      => false
    ];
    $args         = array_merge($default, $args);
    $method       = mb_strtolower($args['method']);
    $method_allow = ['get', 'post'];
    if (null === $args['url'] || !in_array($method, $method_allow, true)) {
        return;
    }
    $curl = new Curl();
    $curl->setUserAgent($args['user-agent']);
    $curl->setReferrer($args['referer']);
    $curl->setTimeout(15);
    $curl->setHeader('X-Requested-With', 'XMLHttpRequest');
    $curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
    if ($args['proxy'] && MC_PROXY) {
        $curl->setOpt(CURLOPT_HTTPPROXYTUNNEL, 1);
        $curl->setOpt(CURLOPT_PROXY, MC_PROXY);
        $curl->setOpt(CURLOPT_PROXYUSERPWD, MC_PROXYUSERPWD);
    }
    if (!empty($args['headers'])) {
        $curl->setHeaders($args['headers']);
    }
    $curl->$method($args['url'], $args['body']);
    $curl->close();
    if (!$curl->error) {
        return $curl->rawResponse;
    }
}

// 判断地址是否有误
function mc_is_error($url) {
    $curl = new Curl();
    $curl->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.50 Safari/537.36');
    $curl->head($url);
    $curl->close();
    return $curl->errorCode;
}

// 音频数据接口地址
function mc_song_urls($value, $type = 'query', $site = 'netease', $page = 1)
{
    if (!$value) {
        return;
    }