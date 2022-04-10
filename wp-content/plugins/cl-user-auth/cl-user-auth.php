<?php
/*
Plugin Name: CL_USER_AUTH
Plugin URI: /
Description: CL_USER_AUTH
Version: 0.1
Author: CL
Author URI: /
Text Domain: CL_USER_AUTH
License: GPLv2
*/

require_once('CurrentUser.php');

add_action('wp_head', 'cl_display_user');
add_action('rest_api_init', 'api_current_user');

function add_var($name, $value)
{
    if (is_numeric($value)) {
        return "const {$name}={$value};";
    }
    return "const {$name}=\"{$value}\";";
}

function cl_display_user()
{
    $current_user = wp_get_current_user();
    if (!$current_user || !($current_user instanceof WP_User)) {
        return;
    }

    $vars = add_var('user_email', $current_user->user_email)
        . add_var('user_first_name', $current_user->user_firstname)
        . add_var('user_last_name', $current_user->user_lastname)
        . add_var('user_display_name', $current_user->display_name)
        . add_var('user_id', $current_user->ID)
        . add_var('user_user_name', $current_user->user_login);
    echo ("<script>$vars</script>");
}

function api_current_user()
{
    CurrentUser::getInstance()->setUser(wp_get_current_user());
    /* register_rest_route('wp/v2', '/current-user', array(
        'methods' => 'GET',
        'callback' => 'get_user_curr',
    )); */
    register_rest_route('wp/v2', '/user-email/(?P<email>.+)', array(
        'methods' => 'GET',
        'callback' => 'exist_email',
    ));
}

function exist_email( $request ) {
    $exists = email_exists( $request['email'] );
    $user = new stdClass();

    if ( $exists ) {
        $user->res = true;
        return wp_send_json($user, 200);
    } else {
        $user->res = false;
        return wp_send_json($user, 200);
    }
}

function get_user_curr()
{
    $current_user = CurrentUser::getInstance()->getUserData();
    if ($current_user) {
        return wp_send_json($current_user, 200);
    }
    return wp_send_json($current_user, 401);
}
