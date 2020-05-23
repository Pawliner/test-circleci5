
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
        ],
        'kg'                => [
            'method'        => 'GET',
            'url'           => 'http://kg.qq.com/cgi/fcg_lyric',
            'referer'       => 'http://kg.qq.com',
            'proxy'         => false,
            'body'          => [
                'format'     => 'json',
                'inCharset'  => 'utf8',
                'outCharset' => 'utf-8',
                'ksongmid'   => $songid
            ]
        ]
    ];
    if ('query' === $type) {
        return $radio_search_urls[$site];
    }
    if ('songid' === $type) {
        return $radio_song_urls[$site];
    }
    if ('lrc' === $type) {
        return $radio_lrc_urls[$site];
    }
    return;
}

// 获取音频信息 - 关键词搜索
function mc_get_song_by_name($query, $site = 'netease', $page = 1)
{
    if (!$query) {
        return;
    }
    $radio_search_url = mc_song_urls($query, 'query', $site, $page);
    if (empty($query) || empty($radio_search_url)) {
        return;
    }
    $radio_result = mc_curl($radio_search_url);
    if (empty($radio_result)) {
        return;
    }
    $radio_songid = [];
    switch ($site) {
        case '1ting':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['results'])) {
                return;
            }
            foreach ($radio_data['results'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case 'baidu':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['song_list'])) {
                return;
            }
            foreach ($radio_data['song_list'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case 'kugou':
            $radio_data = json_decode($radio_result, true);
            $key = MC_INTERNAL ? 'lists' : 'info';
            if (empty($radio_data['data']) || empty($radio_data['data'][$key])) {
                return;
            }
            foreach ($radio_data['data'][$key] as $val) {
                if (MC_INTERNAL) {
                    $hash = $val['SQFileHash'];
                    if (!str_replace('0', '', $hash)) {
                        $hash = $val['FileHash'];
                    }
                } else {
                    $hash = $val['320hash'] ?: $val['hash'];
                }
                $radio_songid[] = $hash;
            }
            break;
        case 'kuwo':
            $radio_result = str_replace('\'', '"', $radio_result);
            $radio_data   = json_decode($radio_result, true);
            if (empty($radio_data['abslist'])) {
                return;
            }
            foreach ($radio_data['abslist'] as $val) {
                $radio_songid[] = str_replace('MUSIC_', '', $val['MUSICRID']);
            }
            break;
        case 'qq':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['song']) || empty($radio_data['data']['song']['list'])) {
                return;
            }
            foreach ($radio_data['data']['song']['list'] as $val) {
                $radio_songid[] = $val['songmid'];
            }
            break;
        case 'xiami':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['songs'])) {
                return;
            }
            foreach ($radio_data['data']['songs'] as $val) {
                $radio_songid[] = $val['song_id'];
            }
            break;
        case '5singyc':
        case '5singfc':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']['songArray'])) {
                return;
            }
            foreach ($radio_data['data']['songArray'] as $val) {
                $radio_songid[] = $val['songId'];
            }
            break;
        case 'migu':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['musics'])) {
                return;
            }
            foreach ($radio_data['musics'] as $val) {
                $radio_songid[] = $val['id'];
            }
            break;
        case 'lizhi':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['audio']) || empty($radio_data['audio']['data'])) {
                return;
            }
            foreach ($radio_data['audio']['data'] as $val) {
                $radio_songid[] = $val['audio']['id'];
            }
            break;
        case 'qingting':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']) || empty($radio_data['data']['data'])) {
                return;
            }
            foreach ($radio_data['data']['data'][0]['doclist']['docs'] as $val) {
                $radio_songid[] = $val['parent_id'].'|'.$val['id'];
            }
            break;
        case 'ximalaya':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['track']) || empty($radio_data['track']['docs'])) {
                return;
            }
            foreach ($radio_data['track']['docs'] as $val) {
                if (!$val['is_paid']) { // 过滤付费的
                    $radio_songid[] = $val['id'];
                }
            }
            break;
        case 'kg':
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['data']['ugclist'])) {
                return;
            }
            foreach ($radio_data['data']['ugclist'] as $val) {
                $radio_songid[] = $val['shareid'];
            }
            break;
        case 'netease':
        default:
            $radio_data = json_decode($radio_result, true);
            if (empty($radio_data['result']) || empty($radio_data['result']['songs'])) {
                return;
            }
            foreach ($radio_data['result']['songs'] as $val) {
                $radio_songid[] = $val['id'];
            }
            break;
    }
    return mc_get_song_by_id($radio_songid, $site, true);
}

// 获取音频信息 - 歌曲ID
function mc_get_song_by_id($songid, $site = 'netease', $multi = false)
{
    if (empty($songid) || empty($site)) {
        return;
    }
    $radio_song_urls = [];
    $site_allow_multiple = [
        'netease',
        '1ting',
        'baidu',
        'qq',
        'xiami',
        'lizhi'
    ];
    if ($multi) {
        if (!is_array($songid)) {
            return;
        }
        if (in_array($site, $site_allow_multiple, true)) {
            $radio_song_urls[] = mc_song_urls(implode(',', $songid), 'songid', $site);
        } else {
            foreach ($songid as $key => $val) {
                $radio_song_urls[] = mc_song_urls($val, 'songid', $site);
            }
        }
    } else {
        $radio_song_urls[] = mc_song_urls($songid, 'songid', $site);
    }
    if (empty($radio_song_urls) || !array_key_exists(0, $radio_song_urls)) {
        return;
    }
    $radio_result = [];
    foreach ($radio_song_urls as $key => $val) {
        $radio_result[] = mc_curl($val);
    }
    if (empty($radio_result) || !array_key_exists(0, $radio_result)) {
        return;
    }
    $radio_songs = [];
    switch ($site) {
        case '1ting':
            foreach ($radio_result as $val) {
                $radio_data             = json_decode($val, true);
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id  = $value['song_id'];
                        $radio_lrc_urls = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc  = mc_curl($radio_lrc_urls);
                        }
                        $radio_songs[]  = [
                            'type'   => '1ting',
                            'link'   => 'http://www.1ting.com/player/6c/player_' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'title'  => $value['song_name'],
                            'author' => $value['singer_name'],
                            'lrc'    => $radio_lrc,
                            'url'    => 'http://h5.1ting.com/file?url=' . str_replace('.wma', '.mp3', $value['song_filepath']),
                            'pic'    => 'http://img.store.sogou.com/net/a/link?&appid=100520102&w=500&h=500&url=' . $value['album_cover']
                        ];
                    }
                }
            }
            break;
        case 'baidu':
            foreach ($radio_result as $val) {
                $radio_json             = json_decode($val, true);
                $radio_data             = $radio_json['data']['songList'];
                if (!empty($radio_data)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id  = $value['songId'];
                        $radio_lrc_urls = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc  = json_decode(mc_curl($radio_lrc_urls), true);
                        }
                        $radio_songs[]  = [
                            'type'   => 'baidu',
                            'link'   => 'http://music.baidu.com/song/' . $radio_song_id,
                            'songid' => $radio_song_id,
                            'title'  => $value['songName'],
                            'author' => $value['artistName'],
                            'lrc'    => $radio_lrc['lrcContent'],
                            'url'    => str_replace(
                                [
                                    'yinyueshiting.baidu.com',
                                    'zhangmenshiting.baidu.com',
                                    'zhangmenshiting.qianqian.com'
                                ],
                                'gss0.bdstatic.com/y0s1hSulBw92lNKgpU_Z2jR7b2w6buu',
                                $value['songLink']
                            ),
                            'pic'    => $value['songPicBig']
                        ];
                    }
                }
            }
            break;
        case 'kugou':
            foreach ($radio_result as $val) {
                $radio_data           = json_decode($val, true);
                if (!empty($radio_data)) {
                    if (!$radio_data['url']) {
                        if (count($radio_result) === 1) {
                            $radio_songs      = [
                                'error' => $radio_data['privilege'] ? '源站反馈此音频需要付费' : '找不到可用的播放地址',
                                'code' => 403
                            ];
                            break;
                        }
                        // 过滤无效的
                        continue;
                    }
                    $radio_song_id    = $radio_data['hash'];
                    $radio_song_album = str_replace('{size}', '150', $radio_data['album_img']);
                    $radio_song_img   = str_replace('{size}', '150', $radio_data['imgUrl']);
                    $radio_lrc_urls   = mc_song_urls($radio_song_id, 'lrc', $site);
                    if ($radio_lrc_urls) {
                        $radio_lrc    = mc_curl($radio_lrc_urls);
                    }
                    $radio_songs[]    = [
                        'type'   => 'kugou',
                        'link'   => 'http://www.kugou.com/song/#hash=' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['songName'],
                        'author' => $radio_data['singerName'],
                        'lrc'    => $radio_lrc,
                        'url'    => $radio_data['url'],
                        'pic'    => $radio_song_album ?: $radio_song_img
                    ];
                }
            }
            break;
        case 'kuwo':
            foreach ($radio_result as $val) {
                preg_match_all('/<([\w]+)>(.*?)<\/\\1>/i', $val, $radio_json);
                if (!empty($radio_json[1]) && !empty($radio_json[2])) {
                    $radio_data             = [];
                    foreach ($radio_json[1] as $key => $value) {
                        $radio_data[$value] = $radio_json[2][$key];
                    }
                    $radio_song_id          = $radio_data['music_id'];
                    $radio_lrc_urls         = mc_song_urls($radio_song_id, 'lrc', $site);
                    if ($radio_lrc_urls) {
                        $radio_lrc_info     = json_decode(mc_curl($radio_lrc_urls), true);
                    }
                    $radio_lrclist          = $radio_lrc_info['data']['lrclist'];
                    $radio_songs[]          = [
                        'type'   => 'kuwo',
                        'link'   => 'http://www.kuwo.cn/yinyue/' . $radio_song_id,
                        'songid' => $radio_song_id,
                        'title'  => $radio_data['name'],
                        'author' => $radio_data['singer'],
                        'lrc'    => generate_kuwo_lrc($radio_lrclist),
                        'url'    => 'http://' . $radio_data['mp3dl'] . '/resource/' . $radio_data['mp3path'],
                        'pic'    => $radio_data['artist_pic']
                    ];
                }
            }
            break;
        case 'qq':
            $radio_vkey = json_decode(mc_curl([
                'method'     => 'GET',
                'url'        => 'http://base.music.qq.com/fcgi-bin/fcg_musicexpress.fcg',
                'referer'    => 'http://y.qq.com',
                'proxy'      => false,
                'body'       => [
                    'json'   => 3,
                    'guid'   => 5150825362,
                    'format' => 'json'
                ]
            ]), true);
            foreach ($radio_result as $val) {
                $radio_json                  = json_decode($val, true);
                $radio_data                  = $radio_json['data'];
                $radio_url                   = $radio_json['url'];
                if (!empty($radio_data) && !empty($radio_url)) {
                    foreach ($radio_data as $value) {
                        $radio_song_id       = $value['mid'];
                        $radio_authors       = [];
                        foreach ($value['singer'] as $singer) {
                            $radio_authors[] = $singer['title'];
                        }
                        $radio_author        = implode(',', $radio_authors);
                        $radio_lrc_urls      = mc_song_urls($radio_song_id, 'lrc', $site);
                        if ($radio_lrc_urls) {
                            $radio_lrc       = jsonp2json(mc_curl($radio_lrc_urls));
                        }
                        $radio_music         = 'http://' . str_replace('ws', 'dl', $radio_url[$value['id']]);
                        if (!empty($radio_vkey['key'])) {
                            $radio_music     = generate_qqmusic_url(
                                $radio_song_id,
                                $radio_vkey['key']
                            ) ?: $radio_music;
                        }
                        $radio_album_id      = $value['album']['mid'];
                        $radio_songs[]       = [
                            'type'   => 'qq',
                            'link'   => 'http://y.qq.com/n/yqq/song/' . $radio_song_id . '.html',
                            'songid' => $radio_song_id,
                            'title'  => $value['title'],