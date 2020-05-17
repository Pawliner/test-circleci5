
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
    $query             = ('query' === $type) ? $value : '';
    $songid            = ('songid' === $type || 'lrc' === $type) ? $value : '';
    $radio_search_urls = [
        'netease'            => [
            'method'         => 'POST',
            'url'            => 'http://music.163.com/api/linux/forward',
            'referer'        => 'http://music.163.com/',
            'proxy'          => false,
            'body'           => encode_netease_data([
                'method'     => 'POST',
                'url'        => 'http://music.163.com/api/cloudsearch/pc',
                'params'     => [
                    's'      => $query,
                    'type'   => 1,
                    'offset' => $page * 10 - 10,
                    'limit'  => 10
                ]
            ])
        ],
        '1ting'              => [
            'method'         => 'GET',
            'url'            => 'http://so.1ting.com/song/json',
            'referer'        => 'http://h5.1ting.com/',
            'proxy'          => false,
            'body'           => [
                'q'          => $query,
                'page'       => $page,
                'size'       => 10
            ]
        ],
        'baidu'              => [
            'method'         => 'GET',
            'url'            => 'http://musicapi.qianqian.com/v1/restserver/ting',
            'referer'        => 'http://music.baidu.com/',
            'proxy'          => false,
            'body'           => [
                'method'    => 'baidu.ting.search.common',
                'query'     => $query,
                'format'    => 'json',
                'page_no'   => $page,
                'page_size' => 10
            ]
        ],
        'kugou'              => [
            'method'         => 'GET',
            'url'            => MC_INTERNAL ?
                'http://songsearch.kugou.com/song_search_v2' :
                'http://mobilecdn.kugou.com/api/v3/search/song',
            'referer'        => MC_INTERNAL ? 'http://www.kugou.com' : 'http://m.kugou.com',
            'proxy'          => false,
            'body'           => [
                'keyword'    => $query,
                'platform'   => 'WebFilter',
                'format'     => 'json',
                'page'       => $page,
                'pagesize'   => 10
            ]
        ],
        'kuwo'               => [
            'method'         => 'GET',
            'url'            => 'http://search.kuwo.cn/r.s',
            'referer'        => 'http://player.kuwo.cn/webmusic/play',
            'proxy'          => false,
            'body'           => [
                'all'        => $query,
                'ft'         => 'music',
                'itemset'    => 'web_2013',
                'pn'         => $page - 1,
                'rn'         => 10,
                'rformat'    => 'json',
                'encoding'   => 'utf8'
            ]
        ],
        'qq'                 => [
            'method'         => 'GET',
            'url'            => 'http://c.y.qq.com/soso/fcgi-bin/search_for_qq_cp',
            'referer'        => 'http://m.y.qq.com',
            'proxy'          => false,
            'body'           => [
                'w'          => $query,
                'p'          => $page,
                'n'          => 10,
                'format'     => 'json'
            ],
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'              => [
            'method'         => 'GET',
            'url'            => 'http://api.xiami.com/web',
            'referer'        => 'http://m.xiami.com',