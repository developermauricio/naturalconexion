<?php
/*  Copyright 2010-2023 Renzo Johnson (email: renzo.johnson at gmail.com)

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



// if(!function_exists('chimp_24hours_cron_job')){
    function cron_remove_disable_log_daily(){

        $chimp_db_logdb = new chimp_db_log( 'mce_db_issues', 1,'api' );
        $res_debug = $chimp_db_logdb->chimp_log_delete_db();

        $chimp_php_logphp = new chimp_db_log('mce_php_issues', 1, 'api');
        $res_php = $chimp_php_logphp->chimp_log_delete_db();

        for ($i = 1; $i <= 300; $i++){
            $idform = 'cf7_mch_'.$i;
            $cf7_mch = get_option($idform);
            unset($cf7_mch['logfileEnabled']);
            update_option($idform, $cf7_mch);
        }
        return "ok";

    }
// }
add_action('chimp_24hours_cron_job','cron_remove_disable_log_daily');



// function that registers new custom schedule
function cml_daily_clean( $schedules ) {

    $schedules[ 'cml-daily' ] = array(
        'interval' => 86400, // segundos en un dia
        'display'  => 'Once Daily',
    );

    return $schedules;
}



// function that schedules custom event
function cml_sche_daily_cleaner() {
    // the actual hook to register new custom schedule

    add_filter( 'cron_schedules', 'cml_daily_clean' );

    // schedule custom event
    if( !wp_next_scheduled( 'cml-cleans-daily' ) ) {
        wp_schedule_event( time(), 'cml-daily', 'cml-cleans-daily' );
    }

}
add_action( 'init', 'cml_sche_daily_cleaner' );



// fire custom event
function cml_clean_deact_debuggr() {
    // your code...

  $chimp_db_logdb = new chimp_db_log( 'mce_db_issues', 1,'api' );
  $res_debug = $chimp_db_logdb->chimp_log_delete_db();

  $chimp_php_logphp = new chimp_db_log('mce_php_issues', 1, 'api');
  $res_php = $chimp_php_logphp->chimp_log_delete_db();

  for ($i = 1; $i <= 300; $i++){
      $idform = 'cf7_mch_'.$i;
      $cf7_mch = get_option($idform);
      unset($cf7_mch['logfileEnabled']);
      update_option($idform, $cf7_mch);
  }

  return "ok";

}
add_action( 'cml-cleans-daily', 'cml_clean_deact_debuggr' );



// fire custom event
if ( ! function_exists ( 'mce_plugin_deactivation'  ) ) {

 function mce_plugin_deactivation() {

     wp_clear_scheduled_hook( 'cml-cleans-daily' );
     // wp_clear_scheduled_hook( 'mce_5min_cron_job' );
     // wp_clear_scheduled_hook( 'mce_4days_cron_job' );
     // delete_site_option( 'mce_show_update_news' );
 }

}


// register_activation_hook(__FILE__,'mce_help');


// add_filter( 'cron_schedules', 'mce_cron_schedules');

// if ( ! function_exists ( 'mce_cron_schedules'  ) ) {

//  function mce_cron_schedules( $schedules ) {

//       $schedules['weekly'] = array(
//          'interval' => 604800, // segundos en una semana
//          'display' => __( 'Weekly', 'mce-textdomain' ) //nombre del intervalo
//       );

//       $schedules['monthly'] = array(
//          'interval' => 2592000, // segundos en 30 dias
//          'display' => __( 'Monthly', 'mce-textdomain' ) // nombre del intervalo
//       );

//      $schedules['12hours'] = array(
//          'interval' => 43200, // segundos en 12 horas --43200
//          'display' => __( '12hours', 'mce-textdomain' ) // nombre del intervalo
//       );

//       $schedules['5min'] = array(
//          'interval' => 300, // segundos en 5 minutos
//          'display' => __( '5min', 'mce-textdomain' ) // nombre del intervalo
//       );

//       $schedules['4days'] = array(
//          'interval' => 345600, // cada 4 dias.
//          'display' => __( '4days', 'mce-textdomain' ) // nombre del intervalo
//       );

//       return $schedules;
//  }

// }

// register_activation_hook( __FILE__, 'mce_plugin_scrool' );

// if ( ! function_exists ( 'mce_plugin_scrool'  ) ) {

//  function mce_plugin_scrool() {

//      if( ! wp_next_scheduled( 'cml-cleans-daily' ) ) {
//          wp_schedule_event( current_time( 'timestamp' ), '12hours', 'mce_12hours_cron_job' );
//      }

//      if( ! wp_next_scheduled( 'mce_4days_cron_job' ) ) {
//          wp_schedule_event( current_time( 'timestamp' ), '4days', 'mce_4days_cron_job' );
//      }

//     //include SPARTAN_MCE_PLUGIN_DIR . '/lib/install-wp-plugins.php' ;

//  }

// }

// add_action( 'mce_12hours_cron_job', 'mce_do_this_job_12hours' );
// //(add_action( 'mce_4days_cron_job', 'mce_do_this_job_4days' ) ;

// if ( ! function_exists ( 'mce_do_this_job_12hours'  ) ) {

//  function mce_do_this_job_12hours() {
//      if ( get_site_option('mce_show_update_news') == null  )
//          add_site_option( 'mce_show_update_news', 1 ) ;
//      else  {
//         $check = 0 ;
//         $tittle = '' ;
//         $message = mce_get_postnotice ($check,$tittle) ;
//         if ( $check == 1 ) {
//            update_site_option('mce_show_update_news', 1);
//            update_site_option('mce_conten_panel_master', $message) ;
//            update_site_option('mce_conten_tittle_master', $tittle) ;  ;
//         }

//         if ( get_site_option('mce_conten_panel_welcome') != null ) {

//            $check = 0 ;
//            $tittle = '' ;
//            $banner = mce_get_bannerladilla ($check,$tittle) ;
//            update_site_option('mce_conten_panel_welcome', $banner) ;

//            $check = 0 ;
//            $tittle = '' ;
//            $bannerlat = mce_get_bannerlateral ($check,$tittle) ;
//            update_site_option('mce_conten_panel_lateralbanner', $bannerlat) ;

//         }

//      }

//      $chimp_db_logdb = new chimp_db_log( 'mce_db_issues', 1,'api' );
//      $res = $chimp_db_logdb->chimp_log_delete_db() ;

//      //stats
//      $dat = getdate () ;
//       if ( $dat["wday"] == 4 or $dat["wday"] == 7 ) {
//         mce_do_this_job_4days() ;
//       }

//  }

// }

// if ( ! function_exists ( 'mce_do_this_job_4days'  ) ) {

//  function mce_do_this_job_4days() {

//    $mce_sents = ( is_null ( get_option( 'mce_sent') ) )? 0 : get_option( 'mce_sent')  ;
//    $diasdifer = mce_diferdays_dateact_date() ;
//    $diasdifer = ( is_null ( $diasdifer ) ) ? 0 : $diasdifer ;

//    $aa =   $mce_sents ;
//    //sents
//    switch ( $aa ) {
//      case 0  :
//        $labelping = '0 sends';
//        break;

//      case  ( $aa > 0 and $aa < 100 ) :
//        $labelping = '1 - 99';
//        break;

//      case ( $aa > 99 and $aa < 300 ):
//        $labelping = '100 - 299';
//        break;

//      case ( $aa > 299 and $aa < 600 ):
//        $labelping = '300-599';
//        break;

//      case ( $aa > 599 and $aa < 1000 ):
//        $labelping = '600-999';
//        break;

//      case ( $aa > 999  ):
//        $labelping = '> 999';
//        break;

//      default :
//        $labelping = 'unrecognosible';
//        break;

//    }

//    $respanalitc = vc_ga_send_event('Mailchimp Extension', 'SENDS', $labelping);

//     //days
//    $aa =   $diasdifer ;
//    switch ( $aa ) {
//      case 0  :
//        $labelping = '0 days';
//        break;

//      case  ( $aa > 0 and $aa < 100 ) :
//        $labelping = '1 - 99 days';
//        break;

//      case ( $aa > 99 and $aa < 300 ):
//        $labelping = '100 - 299 days';
//        break;

//      case ( $aa > 299 and $aa < 600 ):
//        $labelping = '300-599 days';
//        break;

//      case ( $aa > 599 and $aa < 1000 ):
//        $labelping = '600-999 days';
//        break;

//      case ( $aa > 999  ):
//        $labelping = '> 999 days';
//        break;

//      default :
//        $labelping = 'unrecognosite';
//        break;

//    }

//    $respanalitc = vc_ga_send_event('Mailchimp Extension', 'DAYS ACTIVATED', $labelping);


//  }

// }

// register_deactivation_hook( __FILE__, 'mce_plugin_deactivation' );


// if ( ! function_exists ( 'mce_plugin_deactivation'  ) ) {

//  function mce_plugin_deactivation() {

//      wp_clear_scheduled_hook( 'mce_12hours_cron_job' );
//      wp_clear_scheduled_hook( 'mce_5min_cron_job' );
//      wp_clear_scheduled_hook( 'mce_4days_cron_job' );
//      delete_site_option( 'mce_show_update_news' );
//  }

// }
