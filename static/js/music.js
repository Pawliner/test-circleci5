
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
      $alert.html(msg).show();
    },
    submit: function submit(v) {
      v.preventDefault();
      if (this.isFormValid()) {
        var input = $.trim($('#j-input').val());
        var filter = $('#j-input').data('filter');
        var type =
          filter === 'url' ? '_' : $('input[name="music_type"]:checked').val();
        var page = 1;
        var $more = $('<div class="aplayer-more">载入更多</div>');
        var isload = false;
        var ajax = function ajax(input, filter, type, page) {
          $.ajax({
            type: 'POST',
            url: getUrl(),
            timeout: 30000,
            data: {
              input: input,
              filter: filter,
              type: type,
              page: page
            },
            dataType: 'json',
            beforeSend: function beforeSend() {
              isload = true;
              var title = document.title;
              switch (filter) {
                case 'name':
                  pushState(title, getUrl('?name=' + input + '&type=' + type));
                  break;
                case 'id':
                  pushState(title, getUrl('?id=' + input + '&type=' + type));
                  break;
                case 'url':
                  pushState(title, getUrl('?url=' + encodeURIComponent(input)));
                  break;
              }
              if (page === 1) {
                $('#j-input').attr('disabled', true);
                $('#j-submit').button('loading');
              } else {
                $more.text('请稍后...');
              }
            },
            success: function success(result) {
              if (result.code === 200 && result.data) {
                result.data.map(function(v) {
                  if (!v.title) v.title = '暂无';
                  if (!v.author) v.author = '暂无';
                  if (!v.pic) v.pic = nopic;
                  if (!v.lrc) v.lrc = '[00:00.00] 暂无歌词';
                  if (!/\[00:(\d{2})\./.test(v.lrc)) {
                    v.lrc = '[00:00.00] 无效歌词';
                  }
                });
                var setValue = function setValue(data) {
                  $('#j-link').val(data.link);
                  $('#j-link-btn').attr('href', data.link);
                  $('#j-src').val(data.url);
                  $('#j-src-btn').attr('href', data.url);
                  $('#j-lrc').val(data.lrc);
                  $('#j-lrc-btn').attr(
                    'href',
                    'data:application/octet-stream;base64,' +
                      btoa(unescape(encodeURIComponent(data.lrc)))