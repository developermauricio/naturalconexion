/*
 * Thickbox 3.1 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2007 cody lindley
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
*/

if (typeof wcct_modal_pathToImage != 'string') {
    var wcct_modal_pathToImage = (typeof wcctmodal10n !== "undefined") ? wcctmodal10n.loadingAnimation : "";
}

/*!!!!!!!!!!!!!!!!! edit below this line at your own risk !!!!!!!!!!!!!!!!!!!!!!!*/

//on page load call wcct_modal_init
jQuery(document).ready(function () {
    wcct_modal_init('a.wcctmodal, area.wcctmodal, input.wcctmodal');//pass where to apply wcctmodal
    imgLoader = new Image();// preload image
    imgLoader.src = wcct_modal_pathToImage;
});

/*
 * Add wcctmodal to href & area elements that have a class of .wcctmodal.
 * Remove the loading indicator when content in an iframe has loaded.
 */
function wcct_modal_init(domChunk) {
    jQuery('body')
        .on('click', domChunk, wcct_modal_click)
        .on('wcctmodal:iframe:loaded', function () {
            jQuery('#WCCT_MB_window').removeClass('wcctmodal-loading');
        });
}

function wcct_modal_click() {
    var t = this.title || this.name || null;
    var a = this.href || this.alt;
    var g = this.rel || false;
    wcct_modal_show(t, a, g);
    this.blur();
    return false;
}

function wcct_modal_show(caption, url, imageGroup) {//function called when the user clicks on a wcctmodal link

    var $closeBtn;

    try {
        if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
            jQuery("body", "html").css({height: "100%", width: "100%"});
            jQuery("html").css("overflow", "hidden");
            if (document.getElementById("WCCT_MB_HideSelect") === null) {//iframe to hide select elements in ie6
                jQuery("body").append("<iframe id='WCCT_MB_HideSelect'>" + wcctmodal10n.noiframes + "</iframe><div id='WCCT_MB_overlay'></div><div id='WCCT_MB_window' class='wcctmodal-loading'></div>");
                jQuery("#WCCT_MB_overlay").click(wcct_modal_remove);
            }
        } else {//all others
            if (document.getElementById("WCCT_MB_overlay") === null) {
                jQuery("body").append("<div id='WCCT_MB_overlay'></div><div id='WCCT_MB_window' class='wcctmodal-loading'></div>");
                jQuery("#WCCT_MB_overlay").click(wcct_modal_remove);
                jQuery('body').addClass('modal-open');
            }
        }

        if (wcct_modal_detectMacXFF()) {
            jQuery("#WCCT_MB_overlay").addClass("WCCT_MB_overlayMacFFBGHack");//use png overlay so hide flash
        } else {
            jQuery("#WCCT_MB_overlay").addClass("WCCT_MB_overlayBG");//use background and opacity
        }

        if (caption === null) {
            caption = "";
        }
        jQuery("body").append("<div id='WCCT_MB_load'><img src='" + imgLoader.src + "' width='208' /></div>");//add loader to the page
        jQuery('#WCCT_MB_load').show();//show loader

        var baseURL;
        if (url.indexOf("?") !== -1) { //ff there is a query string involved
            baseURL = url.substr(0, url.indexOf("?"));
        } else {
            baseURL = url;
        }

        var urlString = /\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/;
        var urlType = baseURL.toLowerCase().match(urlString);

        if (urlType == '.jpg' || urlType == '.jpeg' || urlType == '.png' || urlType == '.gif' || urlType == '.bmp') {//code to show images

            WCCT_MB_PrevCaption = "";
            WCCT_MB_PrevURL = "";
            WCCT_MB_PrevHTML = "";
            WCCT_MB_NextCaption = "";
            WCCT_MB_NextURL = "";
            WCCT_MB_NextHTML = "";
            WCCT_MB_imageCount = "";
            WCCT_MB_FoundURL = false;
            if (imageGroup) {
                WCCT_MB_TempArray = jQuery("a[rel=" + imageGroup + "]").get();
                for (WCCT_MB_Counter = 0; ((WCCT_MB_Counter < WCCT_MB_TempArray.length) && (WCCT_MB_NextHTML === "")); WCCT_MB_Counter++) {
                    var urlTypeTemp = WCCT_MB_TempArray[WCCT_MB_Counter].href.toLowerCase().match(urlString);
                    if (!(WCCT_MB_TempArray[WCCT_MB_Counter].href == url)) {
                        if (WCCT_MB_FoundURL) {
                            WCCT_MB_NextCaption = WCCT_MB_TempArray[WCCT_MB_Counter].title;
                            WCCT_MB_NextURL = WCCT_MB_TempArray[WCCT_MB_Counter].href;
                            WCCT_MB_NextHTML = "<span id='WCCT_MB_next'>&nbsp;&nbsp;<a href='#'>" + wcctmodal10n.next + "</a></span>";
                        } else {
                            WCCT_MB_PrevCaption = WCCT_MB_TempArray[WCCT_MB_Counter].title;
                            WCCT_MB_PrevURL = WCCT_MB_TempArray[WCCT_MB_Counter].href;
                            WCCT_MB_PrevHTML = "<span id='WCCT_MB_prev'>&nbsp;&nbsp;<a href='#'>" + wcctmodal10n.prev + "</a></span>";
                        }
                    } else {
                        WCCT_MB_FoundURL = true;
                        WCCT_MB_imageCount = wcctmodal10n.image + ' ' + (WCCT_MB_Counter + 1) + ' ' + wcctmodal10n.of + ' ' + (WCCT_MB_TempArray.length);
                    }
                }
            }

            imgPreloader = new Image();
            imgPreloader.onload = function () {
                imgPreloader.onload = null;

                // Resizing large images - original by Christian Montoya edited by me.
                var pagesize = wcct_modal_getPageSize();
                var x = pagesize[0] - 150;
                var y = pagesize[1] - 150;
                var imageWidth = imgPreloader.width;
                var imageHeight = imgPreloader.height;
                if (imageWidth > x) {
                    imageHeight = imageHeight * (x / imageWidth);
                    imageWidth = x;
                    if (imageHeight > y) {
                        imageWidth = imageWidth * (y / imageHeight);
                        imageHeight = y;
                    }
                } else if (imageHeight > y) {
                    imageWidth = imageWidth * (y / imageHeight);
                    imageHeight = y;
                    if (imageWidth > x) {
                        imageHeight = imageHeight * (x / imageWidth);
                        imageWidth = x;
                    }
                }
                // End Resizing

                WCCT_MB_WIDTH = imageWidth + 30;
                WCCT_MB_HEIGHT = imageHeight + 60;
                jQuery("#WCCT_MB_window").append("<a href='' id='WCCT_MB_ImageOff'><span class='screen-reader-text'>" + wcctmodal10n.close + "</span><img id='WCCT_MB_Image' src='" + url + "' width='" + imageWidth + "' height='" + imageHeight + "' alt='" + caption + "'/></a>" + "<div id='WCCT_MB_caption'>" + caption + "<div id='WCCT_MB_secondLine'>" + WCCT_MB_imageCount + WCCT_MB_PrevHTML + WCCT_MB_NextHTML + "</div></div><div id='WCCT_MB_closeWindow'><button type='button' id='WCCT_MB_closeWindowButton'><span class='screen-reader-text'>" + wcctmodal10n.close + "</span><span class='wcct_modal_close_btn'></span></button></div>");

                jQuery("#WCCT_MB_closeWindowButton").click(wcct_modal_remove);

                if (!(WCCT_MB_PrevHTML === "")) {
                    function goPrev() {
                        if (jQuery(document).unbind("click", goPrev)) {
                            jQuery(document).unbind("click", goPrev);
                        }
                        jQuery("#WCCT_MB_window").remove();
                        jQuery("body").append("<div id='WCCT_MB_window'></div>");
                        wcct_modal_show(WCCT_MB_PrevCaption, WCCT_MB_PrevURL, imageGroup);
                        return false;
                    }

                    jQuery("#WCCT_MB_prev").click(goPrev);
                }

                if (!(WCCT_MB_NextHTML === "")) {
                    function goNext() {
                        jQuery("#WCCT_MB_window").remove();
                        jQuery("body").append("<div id='WCCT_MB_window'></div>");
                        wcct_modal_show(WCCT_MB_NextCaption, WCCT_MB_NextURL, imageGroup);
                        return false;
                    }

                    jQuery("#WCCT_MB_next").click(goNext);

                }

                jQuery(document).bind('keydown.wcctmodal', function (e) {
                    if (e.which == 27) { // close
                        wcct_modal_remove();

                    } else if (e.which == 190) { // display previous image
                        if (!(WCCT_MB_NextHTML == "")) {
                            jQuery(document).unbind('wcctmodal');
                            goNext();
                        }
                    } else if (e.which == 188) { // display next image
                        if (!(WCCT_MB_PrevHTML == "")) {
                            jQuery(document).unbind('wcctmodal');
                            goPrev();
                        }
                    }
                    return false;
                });

                wcct_modal_position();
                jQuery("#WCCT_MB_load").remove();
                jQuery("#WCCT_MB_ImageOff").click(wcct_modal_remove);
                jQuery("#WCCT_MB_window").css({'visibility': 'visible'}); //for safari using css instead of show
            };

            imgPreloader.src = url;
        } else {//code to show html

            var queryString = url.replace(/^[^\?]+\??/, '');
            var params = wcct_modal_parseQuery(queryString);

            WCCT_MB_WIDTH = (params['width'] * 1) + 30 || 630; //defaults to 630 if no parameters were added to URL
            WCCT_MB_HEIGHT = (params['height'] * 1) + 40 || 440; //defaults to 440 if no parameters were added to URL
            ajaxContentW = WCCT_MB_WIDTH - 30;
            ajaxContentH = WCCT_MB_HEIGHT - 45;

            if (url.indexOf('WCCT_MB_iframe') != -1) {// either iframe or ajax window
                urlNoQuery = url.split('WCCT_MB_');
                jQuery("#WCCT_MB_iframeContent").remove();
                if (params['modal'] != "true") {//iframe no modal
                    jQuery("#WCCT_MB_window").append("<div id='WCCT_MB_title'><div id='WCCT_MB_ajaxWindowTitle'>" + caption + "</div><div id='WCCT_MB_closeAjaxWindow'><button type='button' id='WCCT_MB_closeWindowButton'><span class='screen-reader-text'>" + wcctmodal10n.close + "</span><span class='wcct_modal_close_btn'></span></button></div></div><iframe frameborder='0' hspace='0' allowtransparency='true' src='" + urlNoQuery[0] + "' id='WCCT_MB_iframeContent' name='WCCT_MB_iframeContent" + Math.round(Math.random() * 1000) + "' onload='wcct_modal_showIframe()' style='width:" + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17) + "px;' >" + wcctmodal10n.noiframes + "</iframe>");
                } else {//iframe modal
                    jQuery("#WCCT_MB_overlay").unbind();
                    jQuery("#WCCT_MB_window").append("<iframe frameborder='0' hspace='0' allowtransparency='true' src='" + urlNoQuery[0] + "' id='WCCT_MB_iframeContent' name='WCCT_MB_iframeContent" + Math.round(Math.random() * 1000) + "' onload='wcct_modal_showIframe()' style='width:" + (ajaxContentW + 29) + "px;height:" + (ajaxContentH + 17) + "px;'>" + wcctmodal10n.noiframes + "</iframe>");
                }
            } else {// not an iframe, ajax
                if (jQuery("#WCCT_MB_window").css("visibility") != "visible") {
                    if (params['modal'] != "true") {//ajax no modal
                        jQuery("#WCCT_MB_window").append("<div id='WCCT_MB_title'><div id='WCCT_MB_ajaxWindowTitle'>" + caption + "</div><div id='WCCT_MB_closeAjaxWindow'><a href='#' id='WCCT_MB_closeWindowButton'><div class='wcct_modal_close_btn'></div></a></div></div><div id='WCCT_MB_ajaxContent' style='width:" + ajaxContentW + "px;height:" + ajaxContentH + "px'></div>");
                    } else {//ajax modal
                        jQuery("#WCCT_MB_overlay").unbind();
                        jQuery("#WCCT_MB_window").append("<div id='WCCT_MB_ajaxContent' class='WCCT_MB_modal' style='width:" + ajaxContentW + "px;height:" + ajaxContentH + "px;'></div>");
                    }
                } else {//this means the window is already up, we are just loading new content via ajax
                    jQuery("#WCCT_MB_ajaxContent")[0].style.width = ajaxContentW + "px";
                    jQuery("#WCCT_MB_ajaxContent")[0].style.height = ajaxContentH + "px";
                    jQuery("#WCCT_MB_ajaxContent")[0].scrollTop = 0;
                    jQuery("#WCCT_MB_ajaxWindowTitle").html(caption);
                }
            }

            jQuery("#WCCT_MB_closeWindowButton").click(wcct_modal_remove);

            if (url.indexOf('WCCT_MB_inline') != -1) {
                jQuery("#WCCT_MB_ajaxContent").append(jQuery('#' + params['inlineId']).children());
                jQuery("#WCCT_MB_window").bind('wcct_modal_unload', function () {
                    jQuery('#' + params['inlineId']).append(jQuery("#WCCT_MB_ajaxContent").children()); // move elements back when you're finished
                });
                wcct_modal_position();
                jQuery("#WCCT_MB_load").remove();
                jQuery("#WCCT_MB_window").css({'visibility': 'visible'});
            } else if (url.indexOf('WCCT_MB_iframe') != -1) {
                wcct_modal_position();
                jQuery("#WCCT_MB_load").remove();
                jQuery("#WCCT_MB_window").css({'visibility': 'visible'});
            } else {
                var load_url = url;
                load_url += -1 === url.indexOf('?') ? '?' : '&';
                jQuery("#WCCT_MB_ajaxContent").load(load_url += "random=" + (new Date().getTime()), function () {//to do a post change this load method
                    wcct_modal_position();
                    jQuery("#WCCT_MB_load").remove();
                    wcct_modal_init("#WCCT_MB_ajaxContent a.wcctmodal");
                    jQuery("#WCCT_MB_window").css({'visibility': 'visible'});
                });
            }

        }

        if (!params['modal']) {
            jQuery(document).bind('keydown.wcctmodal', function (e) {
                if (e.which == 27) { // close
                    wcct_modal_remove();
                    return false;
                }
            });
        }

        $closeBtn = jQuery('#WCCT_MB_closeWindowButton');
        /*
         * If the native Close button icon is visible, move focus on the button
         * (e.g. in the Network Admin Themes screen).
         * In other admin screens is hidden and replaced by a different icon.
         */
        if ($closeBtn.find('.wcct_modal_close_btn').is(':visible')) {
            $closeBtn.focus();
        }


        if (jQuery("#WCCT_MB_ajaxContent").innerHeight() > window.innerHeight) {
            jQuery("#WCCT_MB_ajaxContent").height((window.innerHeight * 90) / 100);
        }

    } catch (e) {
        //nothing here
    }
}

//helper functions below
function wcct_modal_showIframe() {
    jQuery("#WCCT_MB_load").remove();
    jQuery("#WCCT_MB_window").css({'visibility': 'visible'}).trigger('wcctmodal:iframe:loaded');
}

function wcct_modal_remove() {
    jQuery("#WCCT_MB_imageOff").unbind("click");
    jQuery("#WCCT_MB_closeWindowButton").unbind("click");
    jQuery('#WCCT_MB_window').fadeOut('fast', function () {
        jQuery('#WCCT_MB_window, #WCCT_MB_overlay, #WCCT_MB_HideSelect').trigger('wcct_modal_unload').unbind().remove();
        jQuery('body').trigger('wcctmodal:removed');
    });
    jQuery('body').removeClass('modal-open');
    jQuery("#WCCT_MB_load").remove();
    if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
        jQuery("body", "html").css({height: "auto", width: "auto"});
        jQuery("html").css("overflow", "");
    }
    jQuery(document).unbind('.wcctmodal');
    return false;
}

function wcct_modal_position() {
    var isIE6 = typeof document.body.style.maxHeight === "undefined";
    jQuery("#WCCT_MB_window").css({marginLeft: '-' + parseInt((WCCT_MB_WIDTH / 2), 10) + 'px', width: WCCT_MB_WIDTH + 'px'});
    if (!isIE6) { // take away IE6
        jQuery("#WCCT_MB_window").css({marginTop: '-' + parseInt((WCCT_MB_HEIGHT / 2), 10) + 'px'});
    }
}

function wcct_modal_parseQuery(query) {
    var Params = {};
    if (!query) {
        return Params;
    }// return empty object
    var Pairs = query.split(/[;&]/);
    for (var i = 0; i < Pairs.length; i++) {
        var KeyVal = Pairs[i].split('=');
        if (!KeyVal || KeyVal.length != 2) {
            continue;
        }
        var key = unescape(KeyVal[0]);
        var val = unescape(KeyVal[1]);
        val = val.replace(/\+/g, ' ');
        Params[key] = val;
    }
    return Params;
}

function wcct_modal_getPageSize() {
    var de = document.documentElement;
    var w = window.innerWidth || self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
    var h = window.innerHeight || self.innerHeight || (de && de.clientHeight) || document.body.clientHeight;
    arrayPageSize = [w, h];
    return arrayPageSize;
}

function wcct_modal_detectMacXFF() {
    var userAgent = navigator.userAgent.toLowerCase();
    if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox') != -1) {
        return true;
    }
}
