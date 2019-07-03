/* This site is converting visitors into subscribers and customers with OptinMonster - http://optinmonster.com */

var om58792c2520464, om58792c2520464_poll = function () {
  var r = 0;
  return function (n, l) {
    clearInterval(r), r = setInterval(n, l)
  }
}();
!function (e, t, n) {
  if (e.getElementById(n)) {
    om58792c2520464_poll(function () {
      if (window['om_loaded']) {
        if (!om58792c2520464) {
          om58792c2520464 = new OptinMonsterApp();
          return om58792c2520464.init({
            "s": "14660.58792c2520464",
            "staging": 0,
            "dev": 0,
            "beta": 0
          });
        }
      }
    }, 25);
    return;
  }
  var d = false, o = e.createElement(t);
  o.id = n, o.src = "//a.optnmnstr.com/app/js/api.min.js", o.async = true, o.onload = o.onreadystatechange = function () {
    if (!d) {
      if (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") {
        try {
          d = om_loaded = true;
          om58792c2520464 = new OptinMonsterApp();
          om58792c2520464.init({
            "s": "14660.58792c2520464",
            "staging": 0,
            "dev": 0,
            "beta": 0
          });
          o.onload = o.onreadystatechange = null;
        } catch (t) {
        }
      }
    }
  };
  (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(o)
}(document, "script", "omapi-script");
/* / OptinMonster */
/* Bug */
(function ($) {
  /* START: type ahead search with solr-suggester */
  Drupal.behaviors.login_page = {
    attach: function (context, settings) {
      $('#user-form-modal').on('touchmove', function (e) {
        var aclElement = $('.user-popup-enabled').find('.ui-autocomplete');
        var length = aclElement.children().length;
        if (length > 0) {
          aclElement.hide();
        }
      });
    }
  };
  /* END: type ahead search with solr-suggester */
})(jQuery);
