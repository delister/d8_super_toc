/**
 * @file
 * JS for the Super TOC UI.
 */

(function (Drupal, $) {
  "use strict";

  Drupal.behaviors.super_toc = {
    attach: function (context, settings) {
      if (typeof settings.super_toc === 'undefined') {
        return;
      }
        var $processed = $('div.toc').once('toc');

        if (!$processed.length) {
            return;
        }

        $.fn.shrinkTOCWidth = function () {
            $(this).css({
                width: "auto",
                display: "table"
            });
            if ($.browser && $.browser.msie && parseInt($.browser.version) === 7) {
                $(this).css("width", "");
            }
        };
        if (settings.super_toc.smooth_scroll) {
            $("body a").click(function (dataAndEvents) {
                var result = null;
                var hostname = null;
                var pathname = null;
                var qs = null;
                var hash = null;
                var offset = 0;

                if (typeof $().prop == 'function') {
                    hostname = $(this).prop("hostname");
                    pathname = $(this).prop("pathname");
                    qs = $(this).prop("search");
                    hash = $(this).prop("hash");
                }
                else {
                    hostname = $(this).attr("hostname");
                    pathname = $(this).attr("pathname");
                    qs = $(this).attr("search");
                    hash = $(this).attr("hash");
                }
                if (pathname.length > 0) {
                    if (pathname.charAt(0) !== "/") {
                        pathname = "/" + pathname;
                    }
                }
                if (window.location.hostname === hostname && (window.location.pathname === pathname && (window.location.search === qs && hash !== ""))) {
                    var sel = hash.replace(/([ !"$%&'()*+,.\/:;<=>?@[\]^`{|}~])/g, "\\$1");
                    var anchor;

                    if ($(sel).length > 0) {
                        result = hash;
                    }
                    else {
                        anchor = hash;
                        anchor = anchor.replace("#", "");
                        result = 'a[name="' + anchor + '"]';
                        if ($(result).length === 0) {
                            result = "";
                        }
                    }
                    if (settings.super_toc.smooth_scroll_offset > 0) {
                        offset = -1 * settings.super_toc.smooth_scroll_offset;
                    }
                    else {
                        if ($("#toolbar-bar").length > 0) {
                            if ($("#toolbar-bar").is(":visible") && $('#toolbar-bar').find('.toolbar-lining').is(':visible')) {
                                offset = -90;
                            }
                        }
                    }
                    if (result && $.isFunction($.smoothScroll)) {
                        $.smoothScroll({
                            scrollTarget: result,
                            offset: offset
                        });
                    }
                }
            });
        }
        if (typeof settings.super_toc.visibility_show != "undefined") {
            var invert = typeof settings.super_toc.visibility_hide_by_default != "undefined" ? true : false;
            var visibility_hide;

            if ($.cookie) {
                visibility_hide = $.cookie("supertoc_hidetoc") !== "null" ? settings.super_toc.visibility_show : settings.super_toc.visibility_hide;
            }
            else {
                visibility_hide = settings.super_toc.visibility_hide;
            }
            if (invert) {
                visibility_hide = visibility_hide === settings.super_toc.visibility_hide ? settings.super_toc.visibility_show : settings.super_toc.visibility_hide;
            }
            $("#toc_container p.toc_title").append(' <span class="toc_toggle">[<a href="#">' + visibility_hide + "</a>]</span>");
            if (visibility_hide === settings.super_toc.visibility_show) {
                $("ul.toc_list").hide();
                $("#toc_container").addClass("contracted").shrinkTOCWidth();
            }
            $("span.toc_toggle a").click(function (types) {
                types.preventDefault();
                switch ($(this).html()) {
                    case $("<div/>").html(Drupal.checkPlain(settings.super_toc.visibility_hide)).text():
                        $(this).html(settings.super_toc.visibility_show);
                        if ($.cookie) {
                            if (invert) {
                                $.cookie("supertoc_hidetoc", null, {
                                    path: "/"
                                });
                            }
                            else {
                                $.cookie("supertoc_hidetoc", "1", {
                                    expires: 30,
                                    path: "/"
                                });
                            }
                        }
                        $("ul.toc_list").hide("fast");
                        $("#toc_container").addClass("contracted").shrinkTOCWidth();
                        break;

                    case $("<div/>").html(Drupal.checkPlain(settings.super_toc.visibility_show)).text():
                    default:
                        $(this).html(settings.super_toc.visibility_hide);
                        if ($.cookie) {
                            if (invert) {
                                $.cookie("supertoc_hidetoc", "1", {
                                    expires: 30,
                                    path: "/"
                                });
                            }
                            else {
                                $.cookie("supertoc_hidetoc", null, {
                                    path: "/"
                                });
                            }
                        }
                        $("#toc_container").css("width", settings.super_toc.width).removeClass("contracted");
                        $("ul.toc_list").show("fast");
                }
            });
        }
    }
  };

}(Drupal, jQuery));
