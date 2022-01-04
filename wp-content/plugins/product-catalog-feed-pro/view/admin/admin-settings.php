<?php 
    global $wpwoof_values;
    global $wpwoof_add_button;
    global $wpwoof_add_tab;
    global $wpwoof_message;
    global $wpwoofeed_oldname;

    if( is_array($wpwoof_values) && empty($wpwoof_values) )
        $wpwoof_values = array( 'field_mapping'=>array() );
        $is_edit = ( isset($_REQUEST['edit']) && !empty($_REQUEST['edit']) );
 ?>
<?php 
if ( class_exists( 'WooCommerce' ) ) { ?>
    <div class="wrap">
        <div class="wpwoof-wrap">

            <?php  if (isset($_GET['show_msg']) && $_GET['show_msg'] == TRUE) {
                $wpwoof_message = $_GET['wpwoof_message'];
                if (isset($wpwoof_message) && $wpwoof_message === 'success') {
                    echo "<div class='updated'><p>" . __(get_option('wpwoof_message'), 'wpwoof') . "</p></div>";
                } elseif (isset($wpwoof_message) && $wpwoof_message === 'error') {
                    echo "<div class='error'><p>" . __(get_option('wpwoof_message'), 'wpwoof') . "</p></div>";
                }
            }

            ?>
            <div class="wpwoof-container">
                <?php  include('settings-top.php'); ?>
                <?php if( ! $is_edit ) { ?>
                <div class="wpwoof-content wpwoof-settings-panel first" style="display:block;">
                    <?php include('manage-feed.php'); ?>
                </div>
                <?php } ?>
                <div class="wpwoof-content wpwoof-settings-panel second"<?php if( $is_edit ) echo ' style="display: block;"'; ?>>
                    <form method="post" name="wpwoof-addfeed" id="wpwoof-addfeed" action="<?php menu_page_url('wpwoof-settings', true); ?>">
                        <?php wp_nonce_field('wpwoof_feed_nonceaction', 'wpwoof_feed_nonce'); 
                        include('add-feed.php'); ?>
                    </form>    
                </div>
                <?php include('settings-bottom.php'); ?>
            </div>
        </div>
    </div>
    <iframe id="id-wpwoof-iframe" style="display:none;"></iframe> 
    <!-- The Modal -->  
    <div id="IDwpwoof-myModal" class="wpwoof-modal">
        <!-- Modal content -->
        <div class="wpwoof-modal-content">
        <span class="wpwoof-close">&times;</span>
        <p>
        You have started a feed generation. 
        Please do not close this page until the feed is completed.
        </p>
        </div>
    </div>
    <!-- Modal content -->
    
<?php } else { ?>
    <div class="wrap">
        <h2>Activate WooCommerce</h2>
        <div>
            <br>
            <p>You must first activate <strong>WooCommerce</strong> in order for the Product Catalog to work</p>
        </div>
    </div>
<?php } ?>