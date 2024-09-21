(function ($) {
  "use strict";

  $.fn.pi_notification = function (options) {
    var settings = $.extend(
      {
        animation: "fadeIn",
        closing_animation: "fadeOut",
        content: {
          title: "",
          desc: "",
          short_desc: "",
          image: "",
          link: "javascript:void(0)"
        },
        close: true,
        close_image: "",
        dismiss: false,
        mobile: true,
        mobile_size: 968,
        border_radius: 0,
        link_in_tab: false,
        audio_alert_enabled: false,
        audio_url: "",
        max_notification_count:0
      },
      options
    );

    this.maxNotificationReached = function () {
      var count = parseInt(sessionStorage.getItem('pisol_sn_counter'));
      if (count == null) {
        return false;
      }

      if(settings.max_notification_count == 0 || settings.max_notification_count == undefined) return false;

      if (count >= parseInt(settings.max_notification_count)) {
        return true;
      }
      return false;
  }

    this.close = function (obj) {
      $(obj.popup).removeClass(settings.animation);
      $(obj.popup).addClass(settings.closing_animation);
      setTimeout(function () { $(obj.popup).remove(); }, 500, obj);
    }

    this.close_button = function (obj) {
      var obj = obj;
      $(document).on("click", ".pi-popup-close", { obj: obj }, function (event) {
        event.data.obj.close(event.data.obj);
      });
    }

    this.layout = function (obj) {

      if (this.maxNotificationReached()){
        console.log('Max Sales Notification Count Reached');
        return;
      }

      var html =
        '<div class="animated pi-popup ' +
        settings.animation +
        ' ">' +
        (settings.content.image != "" ? '<div class="pi-popup-image">' +
          (settings.link_image != "" ? '<a' +
            (settings.link_in_tab ? ' target="_blank" ' : ' ') +
            'href="' + (settings.content.link != undefined ? settings.content.link : "javascript:void(0)") + '">' : '') +
          '<img src="' +
          settings.content.image +
          '">' +
          (settings.link_image != "" ? '</a>' : '') +
          "</div>" : "") +
        '<div class="pi-popup-content">' +
        (settings.content.desc != undefined ? settings.content.desc : "") +
        ((settings.close && settings.close_image != "") ? '<a class="pi-popup-close" href="javascript:void(0)"><img src="' + settings.close_image + '"></a>' : '') +
        "</div>" +
        "</div>";
      obj.close_button(obj);
      obj.alert();
      if (obj.browser_mobile()) {
        if (settings.mobile) {
          obj.incrementCounter();
          return $(html).appendTo("body");
        }
        return;
      } else {
        obj.incrementCounter();
        return $(html).appendTo("body");
      }

    }

    this.incrementCounter = function () {
      var count = sessionStorage.getItem('pisol_sn_counter');

      if (count == null) {
        count = 0;
      }

      count++;
      sessionStorage.setItem('pisol_sn_counter', count);
    }

    this.browser_mobile = function () {
      var width = parseInt($(window).width());
      if (width > settings.mobile_size) {
        return false
      } else {
        return true;
      }
    }

    this.alert = function () {
      if (settings.audio_alert_enabled) {

        if (settings.audio_url != "") {
          $.playSound(settings.audio_url);
        }

      }
    }

    this.popup = this.layout(this);

    return this;
  };

  jQuery(function ($) {

    /* setTimeout(function () { popup.close(popup); }, 2000);*/


  });

})(jQuery);


(function ($) {
  $.extend({
    playSound: function () {
      return $(
        '<audio class="sound-player" autoplay="autoplay" style="display:none;">'
        + '<source src="' + arguments[0] + '" />'
        + '<embed src="' + arguments[0] + '" hidden="true" autostart="true" loop="false"/>'
        + '</audio>'
      ).appendTo('body');
    },
    stopSound: function () {
      $(".sound-player").remove();
    }
  });
})(jQuery);