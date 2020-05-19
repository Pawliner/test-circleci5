
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
            'proxy'          => false,
            'body'           => [
                'key'        => $query,
                'v'          => '2.0',
                'app_key'    => '1',
                'r'          => 'search/songs',
                'page'       => $page,
                'limit'      => 10
            ],
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        '5singyc'            => [
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                't'          => '0',
                'filterType' => '1',
                'ps'         => 10,
                'pn'         => $page
            ]
        ],
        '5singfc'            => [
            'method'         => 'GET',
            'url'            => 'http://goapi.5sing.kugou.com/search/search',
            'referer'        => 'http://5sing.kugou.com/',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                't'          => '0',
                'filterType' => '2',
                'ps'         => 10,
                'pn'         => 1
            ]
        ],
        'migu'               => [
            'method'         => 'GET',
            'url'            => 'http://m.10086.cn/migu/remoting/scr_search_tag',
            'referer'        => 'http://m.10086.cn',
            'proxy'          => false,
            'body'           => [
                'keyword'    => $query,
                'type'       => '2',
                'pgc'        => $page,
                'rows'       => 10
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'lizhi'              => [
            'method'         => 'GET',
            'url'            => 'http://m.lizhi.fm/api/search_audio/' . urlencode($query) . '/' . $page,
            'referer'        => 'http://m.lizhi.fm',
            'proxy'          => false,
            'body'           => false,
            'user-agent'     => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qingting'           => [
            'method'         => 'GET',
            'url'            => 'http://i.qingting.fm/wapi/search',
            'referer'        => 'http://www.qingting.fm',
            'proxy'          => false,
            'body'           => [
                'k'          => $query,
                'page'       => $page,
                'pagesize'   => 10,
                'include'    => 'program_ondemand',
                'groups'     => 'program_ondemand'
            ]
        ],
        'ximalaya'           => [
            'method'         => 'GET',
            'url'            => 'http://search.ximalaya.com/front/v1',
            'referer'        => 'http://www.ximalaya.com',
            'proxy'          => false,
            'body'           => [
                'kw'         => $query,
                'core'       => 'all',
                'page'       => $page,
                'rows'       => 10,
                'is_paid'    => false
            ]
        ],
        'kg'                 => [
            'method'         => 'GET',
            'url'            => 'http://kg.qq.com/cgi/kg_ugc_get_homepage',
            'referer'        => 'http://kg.qq.com',
            'proxy'          => false,
            'body'           => [
                'format'     => 'json',
                'type'       => 'get_ugc',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'share_uid'  => $query,
                'start'      => $page,
                'num'        => 10
            ]
        ]
    ];
    $radio_song_urls = [
        'netease'           => [
            'method'        => 'POST',
            'url'           => 'http://music.163.com/api/linux/forward',
            'referer'       => 'http://music.163.com/',
            'proxy'         => false,
            'body'          => encode_netease_data([
                'method'    => 'GET',
                'url'       => 'http://music.163.com/api/song/detail',
                'params'    => [
                  'id'      => $songid,
                  'ids'     => '[' . $songid . ']'
                ]
            ])
        ],
        '1ting'             => [
            'method'        => 'GET',
            'url'           => 'http://h5.1ting.com/touch/api/song',
            'referer'       => 'http://h5.1ting.com/#/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'ids'       => $songid
            ]
        ],
        'baidu'             => [
            'method'        => 'GET',
            'url'           => 'http://music.baidu.com/data/music/links',
            'referer'       => 'music.baidu.com/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'songIds'   => $songid
            ]
        ],
        'kugou'             => [
            'method'        => 'GET',
            'url'           => 'http://m.kugou.com/app/i/getSongInfo.php',
            'referer'       => 'http://m.kugou.com/play/info/' . $songid,
            'proxy'         => false,
            'body'          => [
                'cmd'       => 'playInfo',
                'hash'      => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'kuwo'              => [
            'method'        => 'GET',
            'url'           => 'http://player.kuwo.cn/webmusic/st/getNewMuiseByRid',
            'referer'       => 'http://player.kuwo.cn/webmusic/play',
            'proxy'         => false,
            'body'          => [
                'rid'       => 'MUSIC_' . $songid
            ]
        ],
        'qq'                => [
            'method'        => 'GET',
            'url'           => 'http://c.y.qq.com/v8/fcg-bin/fcg_play_single_song.fcg',
            'referer'       => 'http://m.y.qq.com',
            'proxy'         => false,
            'body'          => [
                'songmid'   => $songid,
                'format'    => 'json'
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'             => [
            'method'        => 'GET',
            'url'           => 'http://www.xiami.com/song/playlist/id/' . $songid . '/type/0/cat/json',
            'referer'       => 'http://www.xiami.com',
            'proxy'         => false
        ],
        '5singyc'           => [
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/yc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => [
                'songid'    => $songid,
                'songtype'  => 'yc'
            ]
        ],
        '5singfc'           => [
            'method'        => 'GET',
            'url'           => 'http://mobileapi.5sing.kugou.com/song/newget',
            'referer'       => 'http://5sing.kugou.com/fc/' . $songid . '.html',
            'proxy'         => false,
            'body'          => [
                'songid'    => $songid,
                'songtype'  => 'fc'
            ]
        ],
        'migu'              => [
            'method'        => 'GET',
            'url'           => MC_INTERNAL ? 'http://music.migu.cn/v2/async/audioplayer/playurl/' . $songid : 'http://m.10086.cn/migu/remoting/cms_detail_tag',
            'referer'       => 'http://m.10086.cn',
            'proxy'         => false,
            'body'          => MC_INTERNAL ? false : [
                'cid'    => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'lizhi'             => [
            'method'        => 'GET',
            'url'           => 'http://m.lizhi.fm/api/audios_with_radio',
            'referer'       => 'http://m.lizhi.fm',
            'proxy'         => false,
            'body'          => false,
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qingting'          => [
            'method'        => 'GET',
            'url'           => 'http://i.qingting.fm/wapi/channels/' . split_songid($songid, 0) . '/programs/' . split_songid($songid, 1),
            'referer'       => 'http://www.qingting.fm',
            'proxy'         => false,
            'body'          => false
        ],
        'ximalaya'          => [
            'method'        => 'GET',
            'url'           => 'http://mobile.ximalaya.com/v1/track/ca/playpage/' . $songid,
            'referer'       => 'http://www.ximalaya.com',
            'proxy'         => false,
            'body'          => false
        ],
        'kg'                => [
            'method'        => 'GET',
            'url'           => 'http://kg.qq.com/cgi/kg_ugc_getdetail',
            'referer'       => 'http://kg.qq.com',
            'proxy'         => false,
            'body'          => [
                'v'          => 4,
                'format'     => 'json',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'shareid'    => $songid
            ]
        ]
    ];
    $radio_lrc_urls = [
        'netease'           => [
            'method'        => 'POST',
            'url'           => 'http://music.163.com/api/linux/forward',
            'referer'       => 'http://music.163.com/',
            'proxy'         => false,
            'body'          => encode_netease_data([
                'method'    => 'GET',
                'url'       => 'http://music.163.com/api/song/lyric',
                'params'    => [
                  'id' => $songid,
                  'lv' => 1
                ]
            ])
        ],
        '1ting'             => [
            'method'        => 'GET',
            'url'           => 'http://www.1ting.com/api/geci/lrc/' . $songid,
            'referer'       => 'http://www.1ting.com/geci' . $songid . '.html',
            'proxy'         => false,
            'body'          => false
        ],
        'baidu'             => [
            'method'        => 'GET',
            'url'           => 'http://musicapi.qianqian.com/v1/restserver/ting',
            'referer'       => 'http://music.baidu.com/song/' . $songid,
            'proxy'         => false,
            'body'          => [
                'method' => 'baidu.ting.song.lry',
                'songid' => $songid,
                'format' => 'json'
            ]
        ],
        'kugou'             => [
            'method'        => 'GET',
            'url'           => 'http://m.kugou.com/app/i/krc.php',
            'referer'       => 'http://m.kugou.com/play/info/' . $songid,
            'proxy'         => false,
            'body'          => [
                'cmd'        => 100,
                'timelength' => 999999,
                'hash'       => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X] AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'kuwo'              => [
            'method'        => 'GET',
            'url'           => 'http://m.kuwo.cn/newh5/singles/songinfoandlrc',
            'referer'       => 'http://m.kuwo.cn/yinyue/' . $songid,
            'proxy'         => false,
            'body'          => [
                'musicId' => $songid
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'qq'                => [
            'method'        => 'GET',
            'url'           => 'http://c.y.qq.com/lyric/fcgi-bin/fcg_query_lyric.fcg',
            'referer'       => 'http://m.y.qq.com',
            'proxy'         => false,
            'body'          => [
                'songmid'   => $songid,
                'format'    => 'json',
                'nobase64'  => 1,
                'songtype'  => 0,
                'callback'  => 'c'
            ],
            'user-agent'    => 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1'
        ],
        'xiami'             => [
            'method'        => 'GET',
            'url'           => $songid,
            'referer'       => 'http://www.xiami.com',
            'proxy'         => false