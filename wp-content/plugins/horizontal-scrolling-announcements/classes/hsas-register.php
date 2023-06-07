<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class hsas_cls_registerhook {
	public static function hsas_activation() {
		global $wpdb;

		add_option('horizontal-scrolling-announcements', "1.0");

		// Creating default tables
		global $wpdb;

		$charset_collate = '';
		$charset_collate = $wpdb->get_charset_collate();

		$hsas_default_tables = "CREATE TABLE {$wpdb->prefix}horizontal_scrolling_hsas (
									hsas_id INT unsigned NOT NULL AUTO_INCREMENT,
									hsas_guid VARCHAR(255) NOT NULL,
									hsas_text text NOT NULL,
									hsas_link VARCHAR(1024) NOT NULL default '#',
									hsas_target VARCHAR(1024) NOT NULL default '_self',
									hsas_order int(11) NOT NULL default '1',
									hsas_group VARCHAR(255) NOT NULL default 'IMG',
			 						hsas_datestart date NOT NULL DEFAULT '0000-00-00',
									hsas_timestart int(11) NOT NULL default '0',
									hsas_dateend date NOT NULL DEFAULT '0000-00-00',
									hsas_timeend int(11) NOT NULL default '0',
									hsas_css VARCHAR(1024) NOT NULL default '',
									PRIMARY KEY (hsas_id)
									) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $hsas_default_tables );

		$hsas_default_table_names = array( 'horizontal_scrolling_hsas' );

		$hsas_has_errors = false;
		$hsas_missing_tables = array();
		foreach($hsas_default_table_names as $table_name) {
			if(strtoupper($wpdb->get_var("SHOW TABLES like  '". $wpdb->prefix.$table_name . "'")) != strtoupper($wpdb->prefix.$table_name)) {
				$hsas_missing_tables[] = $wpdb->prefix.$table_name;
			}
		}


		if($hsas_missing_tables) {
			$errors[] = __( 'These tables could not be created on installation ' . implode(', ',$hsas_missing_tables), 'horizontal-scrolling-announcements' );
			$hsas_has_errors = true;
		}

		// if error call wp_die()
		if($hsas_has_errors) {
			wp_die( __( $errors[0] , 'horizontal-scrolling-announcements' ) );
			return false;
		} else {
			// Inserting dummy data on first activation
			hsas_cls_dbquery::hsas_content_default();
		}

		if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
			set_transient( '_hsas_activation_redirect', 1, 30 );
		}

		return true;
	}

	/**
	 * Sends user to the help & info page on activation.
	 */
	public static function hsas_welcome() {

		if ( ! get_transient( '_hsas_activation_redirect' ) ) {
			return;
		}

		// Delete the redirect transient
		delete_transient( '_hsas_activation_redirect' );

		wp_redirect( admin_url( 'admin.php?page=hsas-content' ) );
		exit;
	}

	public static function hsas_deactivation() {
		// do not generate any output here
	}

	public static function hsas_admin_option() {
		// do not generate any output here
	}

	public static function hsas_adminmenu() {

		add_menu_page( __( 'Horizontal scroll', 'horizontal-scrolling-announcements' ),
			__( 'Horizontal scroll', 'horizontal-scrolling-announcements' ), 'manage_options', 'hsas-content', array( 'hsas_cls_intermediate', 'hsas_announcements' ), 'dashicons-megaphone', 51 );

		add_submenu_page('hsas-content', __( 'Announcements', 'horizontal-scrolling-announcements' ),
			__( 'Announcements', 'horizontal-scrolling-announcements' ), 'manage_options', 'hsas-content', array( 'hsas_cls_intermediate', 'hsas_announcements' ));
	
	}

	public static function hsas_load_scripts() {

		if( !empty( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'hsas-content':
					wp_register_script( 'hsas-content', HSAS_URL . 'content/content.js', '', '', true );
					wp_enqueue_script( 'hsas-content' );
					$hsas_select_params = array(
						'hsas_delete_record'   	=> _x( 'Do you want to delete this record?', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_text'   		=> _x( 'Please enter your announcement text.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_link'   		=> _x( 'Please enter your announcement link, optional.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_target' 		=> _x( 'Please select your link target.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_order' 		=> _x( 'Please enter the display order, only number.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_group' 		=> _x( 'Please select/enter group for this announcement text.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_datestart' 	=> _x( 'Please enter start date for this announcement text.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_dateend' 		=> _x( 'Please enter end date for this announcement text.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_add_css' 			=> _x( 'Please enter style for this announcement, optional.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
						'hsas_numericandtext' 	=> _x( 'Please enter text and numbers only.', 'hsas-content-select', 'horizontal-scrolling-announcements' ),
					);
					wp_localize_script( 'hsas-content', 'hsas_content', $hsas_select_params );
					break;
			}
		}
	}
	
	public static function hsas_widget_loading() {
		register_widget( 'hsas_widget_register' );
	}
}


function hsas_shortcode( $atts ) {
	if ( ! is_array( $atts ) ) {
		return '';
	}
	//[hsas-shortcode group="" speed="20" direction="left" gap="50"]
	$group = isset($atts['group']) ? $atts['group'] : '';
	$speed = isset($atts['speed']) ? $atts['speed'] : '20';
	$direction = isset($atts['direction']) ? $atts['direction'] : 'left';
	$gap = isset($atts['gap']) ? $atts['gap'] : '50';
	
	$arr = array();
	$arr["group"] 		= $group;
	$arr["speed"] 		= $speed;
	$arr["direction"] 	= $direction;
	$arr["gap"] 		= $gap;
	$arr["type"] 		= "shortcode";	
	return hsas_cls_widget::hsas_horizontal_scrolling($arr);
}

class hsas_widget_register extends WP_Widget 
{
	function __construct() 
	{
		$widget_ops = array('classname' => 'widget_text hsas-widget', 'description' => __('Horizontal scrolling announcements', 'horizontal-scrolling-announcements'), 'horizontal-scrolling-announcements');
		parent::__construct('horizontal-scrolling-announcements', __('Horizontal scrolling announcements', 'horizontal-scrolling-announcements'), $widget_ops);
	}
	
	function widget( $args, $instance ) 
	{
		extract( $args, EXTR_SKIP );
		
		$hsas_title 	= apply_filters( 'widget_title', empty( $instance['hsas_title'] ) ? '' : $instance['hsas_title'], $instance, $this->id_base );
		$hsas_group		= $instance['hsas_group'];
		$hsas_speed		= $instance['hsas_speed'];
		$hsas_direction	= $instance['hsas_direction'];
		$hsas_gap		= $instance['hsas_gap'];
		
		echo $args['before_widget'];
		
		if ( ! empty( $hsas_title ) )
		{
			echo $args['before_title'] . $hsas_title . $args['after_title'];
		}
		// Call widget method
		$arr = array();
		$arr["group"] 		= $hsas_group;
		$arr["speed"] 		= $hsas_speed;
		$arr["direction"] 	= $hsas_direction;
		$arr["gap"] 		= $hsas_gap;	
		$arr["type"] 		= "widget";	
		echo hsas_cls_widget::hsas_horizontal_scrolling($arr);
		// Call widget method
		
		echo $args['after_widget'];
	}
	
	function update( $new_instance, $old_instance ) 
	{
		$instance 					= $old_instance;
		$instance['hsas_title'] 	= ( ! empty( $new_instance['hsas_title'] ) ) ? strip_tags( $new_instance['hsas_title'] ) : '';
		$instance['hsas_group'] 	= ( ! empty( $new_instance['hsas_group'] ) ) ? strip_tags( $new_instance['hsas_group'] ) : '';
		$instance['hsas_speed'] 	= ( ! empty( $new_instance['hsas_speed'] ) ) ? strip_tags( $new_instance['hsas_speed'] ) : '';
		$instance['hsas_direction'] = ( ! empty( $new_instance['hsas_direction'] ) ) ? strip_tags( $new_instance['hsas_direction'] ) : '';
		$instance['hsas_gap'] 		= ( ! empty( $new_instance['hsas_gap'] ) ) ? strip_tags( $new_instance['hsas_gap'] ) : '';
		return $instance;
	}
	
	function form( $instance ) 
	{
		$defaults = array(
			'hsas_title' 	=> '',
            'hsas_group' 	=> '',
            'hsas_speed' 	=> '',
			'hsas_direction'=> '',
			'hsas_gap'  	=> ''
        );
		$instance 			= wp_parse_args( (array) $instance, $defaults);
		$hsas_title 		= $instance['hsas_title'];
        $hsas_group 		= $instance['hsas_group'];
        $hsas_speed 		= $instance['hsas_speed'];
		$hsas_direction 	= $instance['hsas_direction'];
		$hsas_gap 			= $instance['hsas_gap'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('hsas_title'); ?>"><?php _e('Widget Title', 'horizontal-scrolling-announcements'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('hsas_title'); ?>" name="<?php echo $this->get_field_name('hsas_title'); ?>" type="text" value="<?php echo $hsas_title; ?>" />
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('hsas_group'); ?>"><?php _e('Announcement Group', 'horizontal-scrolling-announcements'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('hsas_group'); ?>" name="<?php echo $this->get_field_name('hsas_group'); ?>" type="text" maxlength="20" value="<?php echo $hsas_group; ?>" />
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('hsas_speed'); ?>"><?php _e('Speed', 'horizontal-scrolling-announcements'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('hsas_direction'); ?>" name="<?php echo $this->get_field_name('hsas_speed'); ?>">
				<option value="2" <?php $this->hsas_selected($hsas_speed == '2'); ?>>2</option>
				<option value="4" <?php $this->hsas_selected($hsas_speed == '4'); ?>>4</option>
				<option value="6" <?php $this->hsas_selected($hsas_speed == '6'); ?>>6</option>
				<option value="8" <?php $this->hsas_selected($hsas_speed == '8'); ?>>8</option>
				<option value="10" <?php $this->hsas_selected($hsas_speed == '10'); ?>>10</option>
				<option value="12" <?php $this->hsas_selected($hsas_speed == '12'); ?>>12</option>
				<option value="14" <?php $this->hsas_selected($hsas_speed == '14'); ?>>14</option>
				<option value="16" <?php $this->hsas_selected($hsas_speed == '16'); ?>>16</option>
				<option value="18" <?php $this->hsas_selected($hsas_speed == '18'); ?>>18</option>
				<option value="20" <?php $this->hsas_selected($hsas_speed == '20'); ?>>20</option>
				<option value="22" <?php $this->hsas_selected($hsas_speed == '22'); ?>>22</option>
				<option value="24" <?php $this->hsas_selected($hsas_speed == '24'); ?>>24</option>
				<option value="26" <?php $this->hsas_selected($hsas_speed == '26'); ?>>26</option>
				<option value="28" <?php $this->hsas_selected($hsas_speed == '28'); ?>>28</option>
				<option value="30" <?php $this->hsas_selected($hsas_speed == '30'); ?>>30</option>
				<option value="35" <?php $this->hsas_selected($hsas_speed == '35'); ?>>35</option>
				<option value="40" <?php $this->hsas_selected($hsas_speed == '40'); ?>>40</option>
			</select>
        </p>
		<p>
            <label for="<?php echo $this->get_field_id('hsas_direction'); ?>"><?php _e('Direction', 'horizontal-scrolling-announcements'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('hsas_direction'); ?>" name="<?php echo $this->get_field_name('hsas_direction'); ?>">
				<option value="left" <?php $this->hsas_selected($hsas_direction == 'left'); ?>>Left</option>
				<option value="right" <?php $this->hsas_selected($hsas_direction == 'right'); ?>>Right</option>
				<option value="up" <?php $this->hsas_selected($hsas_direction == 'up'); ?>>Up</option>
				<option value="down" <?php $this->hsas_selected($hsas_direction == 'down'); ?>>Down</option>
			</select>
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('hsas_gap'); ?>"><?php _e('Gap in pixels between the tickers', 'horizontal-scrolling-announcements'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('hsas_gap'); ?>" name="<?php echo $this->get_field_name('hsas_gap'); ?>" type="text" value="<?php echo $hsas_gap; ?>" />
        </p>
		<?php
	}
	
	function hsas_selected($var) 
	{
		if ($var==1 || $var==true) 
		{
			echo 'selected="selected"';
		}
	}
}

class hsas_cls_widget {
	public static function hsas_horizontal_scrolling($atts) {
		$hsas = "";
		
		if ( ! is_array( $atts ) )
		{
			return '';
		}
	
		$group 		= isset($atts['group']) ? $atts['group'] : '';
		$speed 		= isset($atts['speed']) ? $atts['speed'] : '';
		$direction 	= isset($atts['direction']) ? $atts['direction'] : '';
		$gap 		= isset($atts['gap']) ? $atts['gap'] : '';
		$type 		= isset($atts['type']) ? $atts['type'] : '';
			
		$contents = hsas_cls_dbquery::hsas_content_display($group);
		if(count($contents) > 0) {
			
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'jquery.marquee.min', HSAS_URL.'script/jquery.marquee.min.js', '', '', true);
			
			$style = "";
			$marqueecontent = "";
			
			if(!is_numeric($speed)) {
				$speed = 20000;
			} else {
				$speed = $speed * 1000;
			}
			
			if( $direction != "left" && $direction != "right" && $direction != "up" && $direction != "down") {
				$direction = "left";
			}
			
			if(!is_numeric($gap)) {
				$gap = 50;
			}
			
			foreach ($contents as $content) {
				
				$scrolltxt = "";
				if($content['hsas_link'] == ""){
					$scrolltxt = stripslashes($content['hsas_text']);
				} 
				else {
					$scrolltxt = '<a style="'.$content['hsas_css'].'" href="'.$content['hsas_link'].'" target="'.$content['hsas_target'].'">'.stripslashes($content['hsas_text']).'</a>';
				}
				
				$marqueecontent = $marqueecontent . "&nbsp;&nbsp;&nbsp;" . $scrolltxt;
			}
			
			$randnumber = mt_rand(10,100);
			
			$height = "";
			if($direction == "up" || $direction == "down") {
				$height = "height: 50px";
			}
			
			$hsas = $hsas . "<div class='marquee-hsas-".$type."-".$randnumber."' style='width: 100%;overflow: hidden;".$height."'>";
			$hsas = $hsas . $marqueecontent;
			$hsas = $hsas . "</div>";
			
			$hsas = $hsas . "<script>";
			$hsas = $hsas . "jQuery(function(){";
			  $hsas = $hsas . "jQuery('.marquee-hsas-".$type."-".$randnumber."').marquee({";
			  $hsas = $hsas . "allowCss3Support: true,";
			  $hsas = $hsas . "css3easing: 'linear',";
			  $hsas = $hsas . "easing: 'linear',";
			  $hsas = $hsas . "delayBeforeStart: 2000,";
			  $hsas = $hsas . "direction: '".$direction."',";
			  $hsas = $hsas . "duplicated: true,";
			  $hsas = $hsas . "duration: ".$speed.",";
			  $hsas = $hsas . "gap: ".$gap.",";
			  $hsas = $hsas . "pauseOnCycle: true,";
			  $hsas = $hsas . "pauseOnHover: true,";
			  $hsas = $hsas . "startVisible: true";
			  $hsas = $hsas . "});";
			$hsas = $hsas . "});";
			$hsas = $hsas . "</script>";
		}
		
		return $hsas;	
	}
}

?>