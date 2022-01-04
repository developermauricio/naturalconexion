<?php

$license 	= get_option( 'pcbpys_license_key' );
$status 	= get_option( 'pcbpys_license_status' );

if( $status !== false && $status == 'valid' && !isset($_GET['pcbpys_license_deactivate']) ) { 
    include('admin-settings.php');
} else {
  ?>
    <div class="wrap">
        <h2><?php _e('Activate Plugin License'); ?></h2>

    <div class="wpwoof-wrap">
    <?php
        include('info-settings.php'); ?>
    </div>




    </div>
<?php }