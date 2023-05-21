
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

  // Tab 切换
  $('#j-nav').on('click', 'li', function() {
    var holder = {
      name: '例如: 不要说话 陈奕迅',
      id: '例如: 25906124',
      url: '例如: http://music.163.com/#/song?id=25906124',
      pattern_name: '^.+$',
      pattern_id: '^[\\w\\/\\|]+$',
      pattern_url: '^https?:\\/\\/\\S+$'
    };
    var filter = $(this).data('filter');

    $(this)
      .addClass('am-active')
      .siblings('li')
      .removeClass('am-active');

    $('#j-input')
      .data('filter', filter)
      .attr({
        placeholder: holder[filter],
        pattern: holder['pattern_' + filter]
      })
      .removeClass('am-field-valid am-field-error am-active')
      .closest('.am-form-group')
      .removeClass('am-form-success am-form-error')
      .find('.am-alert')
      .hide();

    if (filter === 'url') {
      $('#j-type').hide();
    } else {
      $('#j-type').show();
    }
  });

  // 输入验证
  $('#j-validator').validator({
    onValid: function onValid(v) {
      $(v.field)
        .closest('.am-form-group')
        .find('.am-alert')
        .hide();
    },
    onInValid: function onInValid(v) {
      var $field = $(v.field);
      var $group = $field.closest('.am-form-group');
      var msgs = {
        name: '将 名称 和 作者 一起输入可提高匹配度',
        id: '输入错误，请查看下面的帮助',
        url: '输入错误，请查看下面的帮助'
      };
      var $alert = $group.find('.am-alert');
      var msg = msgs[$field.data('filter')] || this.getValidationMessage(v);

      if (!$alert.length) {
        $alert = $(
          '<div class="am-alert am-alert-danger am-animation-shake"></div>'
        )
          .hide()
          .appendTo($group);
      }