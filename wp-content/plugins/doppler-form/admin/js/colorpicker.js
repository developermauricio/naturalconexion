(function($) {

  $.fn.colorpicker = function(options) {
    var _this = this;

    var settings = $.extend({
      color: "#FFF",
      hide: false
    }, options);

    // if(!settings.hide) {
    //   $(_this).();
    // }

    var colorPickerHtml = '<div id="color-picker"><canvas id="color-block" height="150" width="150"></canvas><canvas id="color-strip" height="150" width="30"></canvas></div>';
    var selectorHtml = '<div class="dplr_colorpicker_replace"><div class="dplr_colorpicker_preview">'
     + '<div class="dplr_colorpicker_preview_colorcontainer" >'
     + '</div></div><div class="dplr_colorpicker_arrow">▼</div></div>';
    var selectorElement = $(selectorHtml);
    var colorPickerElement = $(colorPickerHtml);
    var colorBlockElement = colorPickerElement.find('#color-block');
    var ctx1 = colorBlockElement[0].getContext('2d');
    var width1 = colorBlockElement[0].width;
    var height1 = colorBlockElement[0].height;
    var colorStripelement = colorPickerElement.find('#color-strip');
    var ctx2 = colorStripelement[0].getContext('2d');
    var width2 = colorStripelement[0].width;
    var height2 = colorStripelement[0].height;

    var x = 0;
    var y = 0;
    var drag = false;
    var rgbaColor = hexToRgbA(settings.color);

    $(selectorElement).find(".dplr_colorpicker_preview_colorcontainer").css("background-color", rgbaColor);
    $(_this).val(settings.color);

    ctx1.rect(0, 0, width1, height1);
    fillGradient();

    ctx2.rect(0, 0, width2, height2);
    var grd1 = ctx2.createLinearGradient(0, 0, 0, height1);

    grd1.addColorStop(0, 'rgba(255, 0, 0, 1)');
    grd1.addColorStop(0.17, 'rgba(255, 255, 0, 1)');
    grd1.addColorStop(0.34, 'rgba(0, 255, 0, 1)');
    grd1.addColorStop(0.51, 'rgba(0, 255, 255, 1)');
    grd1.addColorStop(0.68, 'rgba(0, 0, 255, 1)');
    grd1.addColorStop(0.85, 'rgba(255, 0, 255, 1)');
    grd1.addColorStop(1, 'rgba(255, 0, 0, 1)');
    ctx2.fillStyle = grd1;
    ctx2.fill();

    colorStripelement.click(function(e) {
      x = e.offsetX;
        y = e.offsetY;
        var imageData = ctx2.getImageData(x, y, 1, 1).data;
        rgbaColor = 'rgba(' + imageData[0] + ',' + imageData[1] + ',' + imageData[2] + ',1)';
        fillGradient();
    });

    colorBlockElement.on("mousedown", function(e) {
      drag = true;
      changeColor(e);
    });

    colorBlockElement.on("mouseup", function(e) {
      drag = false;
      colorPickerElement.removeClass("active");
    });

    colorBlockElement.on("mousemove", function(e) {
      if (drag) {
        changeColor(e);
      }
    });

    selectorElement.on("click", function(e) {
      colorPickerElement.toggleClass("active");
    });

    colorPickerElement.on("mouseenter", function(e) {
      colorPickerElement.addClass("active");
    });

    function fillGradient() {
      ctx1.fillStyle = rgbaColor;
      ctx1.fillRect(0, 0, width1, height1);

      var grdWhite = ctx2.createLinearGradient(0, 0, width1, 0);
      grdWhite.addColorStop(0, 'rgba(255,255,255,1)');
      grdWhite.addColorStop(1, 'rgba(255,255,255,0)');
      ctx1.fillStyle = grdWhite;
      ctx1.fillRect(0, 0, width1, height1);

      var grdBlack = ctx2.createLinearGradient(0, 0, 0, height1);
      grdBlack.addColorStop(0, 'rgba(0,0,0,0)');
      grdBlack.addColorStop(1, 'rgba(0,0,0,1)');
      ctx1.fillStyle = grdBlack;
      ctx1.fillRect(0, 0, width1, height1);
    }

    function changeColor(e) {
      x = e.offsetX;
      y = e.offsetY;
      var imageData = ctx1.getImageData(x, y, 1, 1).data;
      rgbaColor = 'rgba(' + imageData[0] + ',' + imageData[1] + ',' + imageData[2] + ',1)';
      $(selectorElement).find(".dplr_colorpicker_preview_colorcontainer").css("background-color", rgbaColor);
      $(_this).val(rgb2hex(imageData));
    }

    function rgb2hex(rgb) {

       return (rgb && rgb.length >= 3) ? "#" +
        ("0" + parseInt(rgb[0],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
        ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) : '#FFFFFF';
    }

    function hexToRgbA(hex){
        var c;
        if(/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)){
            c= hex.substring(1).split('');
            if(c.length== 3){
                c= [c[0], c[0], c[1], c[1], c[2], c[2]];
            }
            c= '0x'+c.join('');
            return 'rgba('+[(c>>16)&255, (c>>8)&255, c&255].join(',')+',1)';
        }
        throw new Error('Bad Hex');
    }

    return _this.after(selectorElement).after(colorPickerElement);
  }
})(jQuery);
