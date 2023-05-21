
'use strict';

/**
 *
 * 音乐搜索器 - JS 文件
 *
 * @author  MaiCong <i@maicong.me>
 * @link    https://github.com/maicong/music
 * @since   1.5.9
 *
 */

$(function() {
  // 获取参数
  function q(key) {
    var value = null;
    var tmp = [];
    location.search
      .substr(1)
      .split('&')
      .forEach(function(v) {
        tmp = v.split('=');
        if (tmp[0] === key) {
          value = decodeURIComponent(tmp[1]);
        }
      });
    return value;
  }

  // 加入历史记录
  function pushState(title, link) {
    if (window.history && window.history.pushState) {
      window.history.pushState(null, title, link);
    }
  }

  // 获取 url
  function getUrl(path) {
    var url = location.href.split('?')[0];
    return path ? url + path : url;
  }

  // 申明变量
  var player = null;
  var playerList = [];
  var nopic = 'static/img/nopic.jpg';
  var qName = q('name');
  var qId = q('id');
  var qUrl = q('url');
  var qType = q('type');
  var siteTitle = document.title;

  // 如果参数存在 name/id 和 type
  if ((qName || qId) && qType) {
    setTimeout(function() {
      $('#j-input').val(qName || qId);
      $('#j-type input[value="' + qType + '"]').prop('checked', true);
      if (qName) {
        $('#j-nav [data-filter="name"]').trigger('click');
      }
      if (qId) {
        $('#j-nav [data-filter="id"]').trigger('click');
      }
      $('#j-validator').trigger('submit');
    }, 0);
  }

  // 如果参数存在 url
  if (qUrl) {
    setTimeout(function() {
      $('#j-type').hide();
      $('#j-input').val(qUrl);
      $('#j-nav [data-filter="url"]').trigger('click');
      $('#j-validator').trigger('submit');
    }, 0);
  }