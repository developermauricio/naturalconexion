(function($) {
  $(document).ready(() => {
    $(".wsc-dismiss").on("click", function() {
      var $el = $(this).closest(".notice");
        $el.fadeTo(100, 0, function() {
          $el.slideUp(100, function() {
            $el.remove();
          });
        });
        $.post(ajaxurl, {
          action: "dismiss_wsc_notice",
        });
    });
  });
})(jQuery);
  
  
  