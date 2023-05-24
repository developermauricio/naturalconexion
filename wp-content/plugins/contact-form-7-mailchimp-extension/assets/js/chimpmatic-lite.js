/*
  Author Name: Renzo Johnson <renzo.johnson@gmail.com>
  Plugin Name: Contact Form 7 MailChimp Extension
  Plugin URI: http://renzojohnson.com/contributions/contact-form-7-mailchimp-extension

  Copyright 2010-2023 Renzo Johnson (email: renzo.johnson at gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


jQuery(document).on('click', '#mce_activalist', function(event){ // use jQuery no conflict methods replace $ with "jQuery"

  event.preventDefault(); // stop post action

  jQuery.ajax({
      type: "POST",
      url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'

      data: {

        action : 'wpcf7_mce_loadlistas',
        mce_idformxx : jQuery("#mce_txtcomodin").val(),
        mceapi: jQuery("#wpcf7-mailchimp-api").val(),

      },


      beforeSend: function() {

        jQuery(".mce-custom-fields .spinner").css('visibility', 'visible');
        // alert('before');

      },


      success: function(response){ // response //data, textStatus, jqXHR

        jQuery(".mce-custom-fields .spinner").css('visibility', 'hidden');
        jQuery('#mce_panel_listamail').html( response );

        var valor = jQuery("#mce_txcomodin2").val();
        // var chm_valid ='';
        var attrclass ='';

        if (valor === '1') {

          attrclass = 'spt-response-out spt-valid';
          jQuery("#mce_apivalid .chmm").removeClass("invalid").addClass("valid");
          jQuery("#mce_apivalid .dashicons").removeClass("dashicons-no").addClass( "dashicons-yes" );

          jQuery(".chmp-inactive").removeClass("chmp-inactive").addClass("chmp-active");

          jQuery("#chmp-new-user").removeClass("chmp-active").addClass("chmp-inactive");



        } else {

          attrclass = 'spt-response-out';
          jQuery("#mce_apivalid .chmm").removeClass("valid").addClass("invalid");
          jQuery("#mce_apivalid .dashicons").removeClass("dashicons-yes").addClass( "dashicons-no" );

          jQuery(".chmp-active").removeClass("chmp-active").addClass("chmp-inactive");

          jQuery("#chmp-new-user").removeClass("chmp-inactive").addClass("chmp-active");

        }

        jQuery('#mce_panel_listamail').attr("class",attrclass);
        // jQuery('#mce_apivalid').html( chm_valid );

      },

      error: function(data, textStatus, jqXHR){

          jQuery(".mce-custom-fields .spinner").css('visibility', 'hidden');
          alert(textStatus);

      },

  });

});



jQuery(document).on('click', '#log_reset', function(event){

  event.preventDefault(); // stop post action

  jQuery.ajax({
      type: "POST",
      url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'

      data: {

        action : 'chimpmatic_logreset',
        mce_idformxx : jQuery("#mce_txtcomodin").val(),
        mceapi: jQuery("#wpcf7-mailchimp-api").val(),

      },
      // error: function(e) {
      //   console.log(e);
      // },

      beforeSend: function() {

        jQuery("#log_reset").addClass("CHIMPLogger");

      },

      success: function( response ){ // response //data, textStatus, jqXHR

        jQuery('#log_panel').html( response );

      },

      error: function(data, textStatus, jqXHR){

          alert( jqXHR );

      },

  });

});



jQuery(document).on('click', '#log_reset_php', function(event){

  event.preventDefault(); // stop post action

  jQuery.ajax({
      type: "POST",
      url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'

      data: {

        action : 'chimpmatic_phplogreset',
        mce_idformxx : jQuery("#mce_txtcomodin").val(),
        mceapi: jQuery("#wpcf7-mailchimp-api").val(),

      },
      // error: function(e) {
      //   console.log(e);
      // },

      beforeSend: function() {

        jQuery("#log_reset_php").addClass("CHIMPLogger");

      },

      success: function( response ){ // response //data, textStatus, jqXHR

        jQuery('#log_panel_php').html( response );

      },

      error: function(data, textStatus, jqXHR){

          alert( jqXHR );

      },

  });

});



jQuery(document).on('change', '#chimpmatic-update', function(event){ // Aug 7, 2020 now

      event.preventDefault(); // stop post action

      var xchk = 0 ;

      if ( jQuery(this).prop('checked') )
         xchk = 1 ;

      jQuery.ajax({
          type: "POST",
          url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'
          data: {
              action : 'wpcf7_mch_set_autoupdate',
              valcheck : xchk  ,
                },

          success: function( response ){ // response,data, textStatus, jqXHR,

            //jQuery( '#gg-select'+ itag ).html( response );

          },
          error: function(data, textStatus, jqXHR){

              alert(textStatus);

          },
      });
});



jQuery(document).ready(function() {

  try {

    if (! jQuery('#wpcf7-mailchimp-cf-active').is(':checked'))

      jQuery('.mailchimp-custom-fields').hide();

    jQuery('#wpcf7-mailchimp-cf-active').click(function() {

      if (jQuery('.mailchimp-custom-fields').is(':hidden')
      && jQuery('#wpcf7-mailchimp-cf-active').is(':checked')) {

        jQuery('.mailchimp-custom-fields').slideDown('fast');
      }

      else if (jQuery('.mailchimp-custom-fields').is(':visible')
      && jQuery('#wpcf7-mailchimp-cf-active').not(':checked')) {

        jQuery('.mailchimp-custom-fields').slideUp('fast');
        jQuery(this).closest('form').find(".mailchimp-custom-fields input[type=text]").val("");

      }

    });

    //Here test check dbl optin


/*
   if (! jQuery('#wpcf7-mailchimp-conf-subs').is(':checked'))

        jQuery('#wpcf7-mailchimp-dbloptin').hide();

    jQuery('#wpcf7-mailchimp-conf-subs').click(function() {

        if (jQuery('#wpcf7-mailchimp-dbloptin').is(':hidden')
        && jQuery('#wpcf7-mailchimp-conf-subs').is(':checked')) {

          jQuery('#wpcf7-mailchimp-dbloptin').slideDown('fast');
        }

        else if (jQuery('#wpcf7-mailchimp-dbloptin').is(':visible')
        && jQuery('#wpcf7-mailchimp-conf-subs').not(':checked')) {

          jQuery('#wpcf7-mailchimp-dbloptin').slideUp('fast');
          //jQuery(this).closest('form').find("#wpcf7-mailchimp-dbloptin input[type=text]").val(""); //al quitarle el check lo deberia volver en blanco
        }

    }); */

    //end


    jQuery(".mce-trigger").click(function() {

      jQuery(this).text(function(i, text){
        if (text ===  "Hide advanced settings") {
          jQuery('.only-one-toogles').slideUp();
          return "Show advanced settings"
        } else {
          jQuery('.only-one-toogles').slideUp();
          jQuery(".mce-support").slideToggle("fast");
          return "Hide advanced settings"
        }
      })

      return false; //Prevent the browser jump to the link anchor

    });


    jQuery(".mce-trigger2").click(function() {
      jQuery(".mce-support2").slideToggle("fast");
      return false; //Prevent the browser jump to the link anchor
    });


    jQuery(".mce-trigger3").click(function() {
      jQuery(".mce-support3").slideToggle("fast");
      return false; //Prevent the browser jump to the link anchor
    });


  }

  catch (e) {

  }

  // Start Move Highlight class into only-one-toogles | Daffa Changes | 10 Jan 2023
jQuery(".cme-trigger-sys").click(function() {


  if (jQuery("#toggle-sys").is(":hidden")==true) {

    jQuery(".mce-trigger").text(function(i, text){
      return text === "Hide advanced settings" ? "Show advanced settings" : "Show advanced settings" ;
    })

    jQuery('.only-one-toogles').slideUp();
    jQuery( "#toggle-sys" ).slideToggle(250);
  } else {
    jQuery('.only-one-toogles').slideUp();
  }

});


jQuery(".cme-trigger-exp").click(function() {


  if (jQuery("#mce-export").is(":hidden")==true) {

    jQuery(".mce-trigger").text(function(i, text){
      return text === "Hide advanced settings" ? "Show advanced settings" : "Show advanced settings" ;
    })

    jQuery('.only-one-toogles').slideUp();
    jQuery( "#mce-export" ).slideToggle(250);
  } else {
    jQuery('.only-one-toogles').slideUp();
  }
});


jQuery(".cme-trigger-log").click(function() {



  if (jQuery("#eventlog-sys").is(":hidden")==true) {

    jQuery(".mce-trigger").text(function(i, text){
      return text === "Hide advanced settings" ? "Show advanced settings" : "Show advanced settings" ;
    })

    jQuery('.only-one-toogles').slideUp();
    jQuery( "#eventlog-sys" ).slideToggle(250);
  } else {
    jQuery('.only-one-toogles').slideUp();
  }

});


jQuery(".cme-trigger-php").click(function() {


  if (jQuery("#eventlog-sys-php").is(":hidden")==true) {

    jQuery(".mce-trigger").text(function(i, text){
      return text === "Hide advanced settings" ? "Show advanced settings" : "Show advanced settings" ;
    })

    jQuery('.only-one-toogles').slideUp();
    jQuery( "#eventlog-sys-php" ).slideToggle(250);
  } else {
    jQuery('.only-one-toogles').slideUp();
  }

});

  // Start Move Highlight class into only-one-toogles | Daffa Changes | 10 Jan 2023


jQuery(document).on('click', '.cme-trigger-log', function(event){

  event.preventDefault(); // stop post action

  jQuery.ajax({
      type: "POST",
      url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'

      data: {

        action : 'chimpmatic_logload',
        mce_idformxx : jQuery("#mce_txtcomodin").val(),
        mceapi: jQuery("#wpcf7-mailchimp-api").val(),

      },
      // error: function(e) {
      //   console.log(e);
      // },

      beforeSend: function() {

        // jQuery("#log_reset").addClass("CHIMPLogger");

      },

      success: function( response ){ // response //data, textStatus, jqXHR

        jQuery('#log_panel').html( response );

      },

      error: function(data, textStatus, jqXHR){

          alert( jqXHR );

      },

  });

});


jQuery(document).on('click', '.cme-trigger-php', function(event){

  event.preventDefault(); // stop post action

  jQuery.ajax({
      type: "POST",
      url: ajaxurl, // or '<?php echo admin_url('admin-ajax.php'); ?>'

      data: {

        action : 'chimpmatic_phplogload',
        mce_idformxx : jQuery("#mce_txtcomodin").val(),
        mceapi: jQuery("#wpcf7-mailchimp-api").val(),

      },
      // error: function(e) {
      //   console.log(e);
      // },

      beforeSend: function() {

        // jQuery("#log_reset").addClass("CHIMPLogger");

      },

      success: function( response ){ // response //data, textStatus, jqXHR

        jQuery('#log_panel_php').html( response );

      },

      error: function(data, textStatus, jqXHR){

          alert( jqXHR );

      },

  });

});



function toggleDiv() {

  setTimeout(function () {
      jQuery(".mce-cta").slideToggle(450);
  }, 10000);

}

// jQuery("#mce_activalist").click(function() {
//     toggleDiv();
// });

jQuery('#wpcf7-mailchimp-list').on('click', function() {

  toggleDiv();
  // alert('List Chosen!');
})



function toggleLateral() {

  setTimeout(function () {
      jQuery(".mce-move").insertBefore("#informationdiv");
      jQuery(".mce-move").slideToggle(450);
  }, 10000);

}

toggleLateral();



});