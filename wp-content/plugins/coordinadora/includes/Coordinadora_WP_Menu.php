<?php
class Coordinadora_WP_Menu
{
    public static function init()
    {
        add_action('admin_menu', __CLASS__ . '::coordinadora_add_menu');
    }

    public static function coordinadora_add_menu()
    {
        add_menu_page('Coordinadora', 'Coordinadora', 'manage_options', 'coordinadora-mercantil', __CLASS__ . '::coordinadora_page', 'dashicons-edit-page');
    }

    public static function coordinadora_page()
    {
        echo '<div style="text-align: center; margin-top: 30px;">';
        echo '<a class="button button-primary" target="_blank" href="https://wc.coordinadora.com/admin/orders">' . __( 'Ir a Coordinadora', 'coordinadora' ) . '</a>';
        echo '</div>';
    }
}
