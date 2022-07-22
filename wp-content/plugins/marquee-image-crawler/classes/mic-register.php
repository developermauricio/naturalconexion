<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class mic_cls_registerhook {
	public static function mic_activation() {
	
		global $wpdb;

		add_option('marquee-image-crawler', "1.0");

		$charset_collate = '';
		$charset_collate = $wpdb->get_charset_collate();
	
		$mic_default_tables = "CREATE TABLE {$wpdb->prefix}marquee_img_crawler (
										mic_id INT unsigned NOT NULL AUTO_INCREMENT,
										mic_image VARCHAR(1024) NOT NULL default '',
										mic_link VARCHAR(1024) NOT NULL default '',
										mic_title VARCHAR(1024) NOT NULL default '',
										mic_width int(11) NOT NULL default '0',
										mic_group VARCHAR(10) NOT NULL default 'Group1',
										mic_status VARCHAR(3) NOT NULL default 'Yes',
										mic_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
										PRIMARY KEY (mic_id)
										) $charset_collate;";
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $mic_default_tables );
		
		$mic_default_tablesname = array( 'marquee_img_crawler' );
	
		$mic_errors = false;
		$mic_missing_tables = array();
		foreach($mic_default_tablesname as $table_name) {
			if(strtoupper($wpdb->get_var("SHOW TABLES like  '". $wpdb->prefix.$table_name . "'")) != strtoupper($wpdb->prefix.$table_name)) {
				$mic_missing_tables[] = $wpdb->prefix.$table_name;
			}
		}
		
		if($mic_missing_tables) {
			$errors[] = __( 'These tables could not be created on installation ' . implode(', ',$mic_missing_tables), 'marquee-image-crawler' );
			$mic_errors = true;
		}
		
		if($mic_errors) {
			wp_die( __( $errors[0] , 'marquee-image-crawler' ) );
			return false;
		} 
		else {
			mic_cls_dbquery::mic_default();
		}
		
		if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) {
			set_transient( '_mic_activation_redirect', 1, 30 );
		}
			
		return true;
	}

	public static function mic_deactivation() {
		// do not generate any output here
	}

	public static function mic_adminoptions() {
	
		global $wpdb;
		$current_page = isset($_GET['ac']) ? sanitize_text_field($_GET['ac']) : '';
		
		switch($current_page) {
			case 'edit':
				require_once(MICR_DIR . 'pages' . DIRECTORY_SEPARATOR . 'image-management-edit.php');
				break;
			case 'add':
				require_once(MICR_DIR . 'pages' . DIRECTORY_SEPARATOR . 'image-management-add.php');
				break;
			default:
				require_once(MICR_DIR . 'pages' . DIRECTORY_SEPARATOR . 'image-management-show.php');
				break;
		}
	}
	
	public static function mic_frontscripts() {
		if (!is_admin()) {
			wp_enqueue_script( 'marquee-image-crawler', plugin_dir_url( __DIR__ ) . '/js/marquee-image-crawler.js');
		}	
	}

	public static function mic_addtomenu() {
	
		if (is_admin()) {
			add_options_page( __('Marquee image', 'marquee-image-crawler'), 
								__('Marquee image', 'marquee-image-crawler'), 'manage_options', 
									'marquee-image-crawler', array( 'mic_cls_registerhook', 'mic_adminoptions' ) );
		}
	}
	
	public static function mic_adminscripts() {
	
		if(!empty($_GET['page'])) {
			switch (sanitize_text_field($_GET['page'])) {
				case 'marquee-image-crawler':
					wp_register_script( 'marquee-image-adminscripts', plugin_dir_url( __DIR__ ) . '/pages/setting.js', '', '', true );
					wp_enqueue_script( 'marquee-image-adminscripts' );
					$mic_select_params = array(
						'mic_image'  		=> __( 'Please enter the image path.', 'marqueeimage-select', 'marquee-image-crawler' ),
						'mic_group'  		=> __( 'Please enter the image group.', 'marqueeimage-select', 'marquee-image-crawler' ),
						'mic_width'  		=> __( 'Please enter image width.', 'marqueeimage-select', 'marquee-image-crawler' ),
						'mic_width_num'  	=> __( 'Please enter image width. only numbers.', 'marqueeimage-select', 'marquee-image-crawler' ),
						'mic_numletters'  	=> __( 'Please input numeric and letters only.', 'marqueeimage-select', 'marquee-image-crawler' ),
						'mic_delete'  		=> __( 'Do you want to delete this record?', 'marqueeimage-select', 'marquee-image-crawler' ),
					);
					wp_localize_script( 'marquee-image-adminscripts', 'mic_adminscripts', $mic_select_params );
					break;
			}
		}
	}
	
	public static function mic_widgetloading() {
		register_widget( 'mic_widget_register' );
	}
}

class mic_widget_register extends WP_Widget 
{
	function __construct() {
		$widget_ops = array('classname' => 'widget_text marquee-image-widget', 'description' => __('Marquee image crawler', 'marquee-image-crawler'), 'marquee-image-crawler');
		parent::__construct('marquee-image-crawler', __('Marquee image crawler', 'marquee-image-crawler'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );
		
		$mic_title 		= apply_filters( 'widget_title', empty( $instance['mic_title'] ) ? '' : $instance['mic_title'], $instance, $this->id_base );
		$mic_group		= $instance['mic_group'];
		$mic_folder		= $instance['mic_folder'];
		$mic_speed		= $instance['mic_speed'];
		$mic_width		= $instance['mic_width'];
		$mic_height		= $instance['mic_height'];
	
		echo $args['before_widget'];
		if (!empty($mic_title)) {
			echo $args['before_title'] . $mic_title . $args['after_title'];
		}
		
		$data = array(
			'group' 	=> $mic_group,
			'folder' 	=> $mic_folder,
			'speed' 	=> $mic_speed,
			'width' 	=> $mic_width,
			'height' 	=> $mic_height
		);
		
		mic_cls_shortcode::mic_render($data);
		
		echo $args['after_widget'];
	}
	
	function update( $new_instance, $old_instance ) {		
		$instance 					= $old_instance;
		$instance['mic_title'] 		= ( ! empty( $new_instance['mic_title'] ) ) ? strip_tags( $new_instance['mic_title'] ) : '';
		$instance['mic_group'] 		= ( ! empty( $new_instance['mic_group'] ) ) ? strip_tags( $new_instance['mic_group'] ) : '';
		$instance['mic_folder'] 	= ( ! empty( $new_instance['mic_folder'] ) ) ? strip_tags( $new_instance['mic_folder'] ) : '';
		$instance['mic_speed'] 		= ( ! empty( $new_instance['mic_speed'] ) ) ? strip_tags( $new_instance['mic_speed'] ) : '';
		$instance['mic_width'] 		= ( ! empty( $new_instance['mic_width'] ) ) ? strip_tags( $new_instance['mic_width'] ) : '';
		$instance['mic_height'] 	= ( ! empty( $new_instance['mic_height'] ) ) ? strip_tags( $new_instance['mic_height'] ) : '';
		return $instance;
	}
	
	function form( $instance ) {
		$defaults = array(
			'mic_title' 	=> '',
		    'mic_group' 	=> '',
			'mic_folder' 	=> '',
			'mic_speed' 	=> '',
			'mic_width' 	=> '',
			'mic_height' 	=> ''
        );
		
		$instance 		= wp_parse_args( (array) $instance, $defaults);
		$mic_title 		= $instance['mic_title'];
        $mic_group 		= $instance['mic_group'];
		$mic_folder 	= $instance['mic_folder'];
		$mic_speed 		= $instance['mic_speed'];
		$mic_width 		= $instance['mic_width'];
		$mic_height 	= $instance['mic_height'];
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('mic_title'); ?>"><?php _e('Title', 'marquee-image-crawler'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('mic_title'); ?>" name="<?php echo $this->get_field_name('mic_title'); ?>" type="text" value="<?php echo $mic_title; ?>" />
        </p>
		<p>
			<label for="<?php echo $this->get_field_id('mic_group'); ?>"><?php _e('Image group', 'marquee-image-crawler'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('mic_group'); ?>" name="<?php echo $this->get_field_name('mic_group'); ?>">
			<option value="">Select (Use folder)</option>
			<?php
			$groups = array();
			$groups = mic_cls_dbquery::mic_group();
			if(count($groups) > 0) {
				foreach ($groups as $group) {
					?>
					<option value="<?php echo $group['mic_group']; ?>" <?php $this->mic_selected($group['mic_group'] == $mic_group); ?>>
					<?php echo $group['mic_group']; ?>
					</option>
					<?php
				}
			}
			?>
			</select>
        </p>
		
		<p>
			<label for="<?php echo $this->get_field_id('mic_folder'); ?>"><?php _e('Folder', 'marquee-image-crawler'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('mic_folder'); ?>" name="<?php echo $this->get_field_name('mic_folder'); ?>" type="text" value="<?php echo $mic_folder; ?>" />
        </p>
		
		<p>
			<label for="<?php echo $this->get_field_id('mic_speed'); ?>"><?php _e('Speed', 'marquee-image-crawler'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('mic_speed'); ?>" name="<?php echo $this->get_field_name('mic_speed'); ?>" type="text" value="<?php echo $mic_speed; ?>" />
        </p>
		
		<p>
			<label for="<?php echo $this->get_field_id('mic_width'); ?>"><?php _e('Gap', 'marquee-image-crawler'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('mic_width'); ?>" name="<?php echo $this->get_field_name('mic_width'); ?>" type="text" value="<?php echo $mic_width; ?>" />
        </p>
		
		<p>
			<label for="<?php echo $this->get_field_id('mic_height'); ?>"><?php _e('Height', 'marquee-image-crawler'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('mic_height'); ?>" name="<?php echo $this->get_field_name('mic_height'); ?>" type="text" value="<?php echo $mic_height; ?>" />
        </p>
		<?php
	}
	
	function mic_selected($var) {
		if ($var==1 || $var==true) {
			echo 'selected="selected"';
		}
	}
}

class mic_cls_shortcode {
	public function __construct() {
	}
	
	public static function mic_shortcode( $atts ) {
		ob_start();
		if (!is_array($atts)) {
			return '';
		}
		
		//[marquee-image-crawler group="Group1" speed="5" gap="10" height="150"]
		//[marquee-image-crawler folder="wp-content/plugins/marquee-image-crawler/sample" speed="5" gap="10" height="150"]
		$atts = shortcode_atts( array(
				'group'			=> '',
				'folder'		=> '',
				'speed'			=> '',
				'gap'			=> '',
				'height'		=> ''
			), $atts, 'marquee-image-crawler' );

		$group 		= isset($atts['group']) ? $atts['group'] : '';
		$folder 	= isset($atts['folder']) ? $atts['folder'] : '';
		$speed 		= isset($atts['speed']) ? $atts['speed'] : '';
		$width 		= isset($atts['gap']) ? $atts['gap'] : '';
		$height 	= isset($atts['height']) ? $atts['height'] : '';

		$data = array(
			'group' 	=> $group,
			'folder' 	=> $folder,
			'speed' 	=> $speed,
			'width' 	=> $width,
			'height' 	=> $height
		);
		
		self::mic_render( $data );

		return ob_get_clean();
	}
	
	public static function mic_render( $input = array() ) {	
		
		$mic = "";
		$datas = array();
		$files	= array();
		
		if(count($input) == 0) {
			return $mic;
		}

		$group 		= $input['group'];
		$folder		= $input['folder'];
		$speed		= $input['speed'];
		$width		= $input['width']; // its used for padding width
		$height		= $input['height'];
		
		if(!is_numeric($speed)) {
			$speed = 5;
		}
		
		if(!is_numeric($width)) {
			$width = 0;
		}
		
		if(!is_numeric($height)) {
			$height = 150;
		}
		
		$mic_id = $group . '-' . rand(1, 1000);
		
		if($group <> "") {
			$datas = mic_cls_dbquery::mic_select_bygroup_rand($group);
			if(count($datas) > 0 ) {
				$mic = '<div class="marquee" id="'.$mic_id.'">';
				foreach ($datas as $data) {
					$mic .= '<img src="' . $data['mic_image'] . '" style="';
					
					if($width > 0) {
						$mic .= 'padding-right:'.$width.'px';
					}

					$mic .= '" /> ';
				}			
				$mic .= '</div>';
			}
		}
		else if($folder <> "") {
			$siteurl_link = get_option('siteurl');
			if (mic_cls_dbquery::endswith($siteurl_link, '/') == false) {
				$siteurl_link = $siteurl_link . "/";
			}
			
			if(is_dir($folder)) {		
				$dirhandle = opendir($folder);
				
				while ($file = readdir($dirhandle)) {
					if(!is_dir($file) && (strpos(strtoupper($file), '.JPG') > 0 or 
						strpos(strtoupper($file), '.GIF') > 0 or 
							strpos(strtoupper($file), '.JPEG') > 0 or 
								strpos(strtoupper($file), '.PNG') > 0) )
					{
						$files[] = $file;
					}
				}
				
				if (mic_cls_dbquery::endswith($folder, '/') == false) {
					$folder = $folder . "/";
				}
				
				if(count($files) > 0 ) {
					$mic = '<div class="marquee" id="'.$mic_id.'">';
					foreach ($files as $image) {
						$mic .= '<img src="' . $siteurl_link . $folder . $image . '" style="';
					
						if($width > 0) {
							$mic .= 'padding-right:'.$width.'px';
						}
						
						$mic .= '" /> ';
					}
					$mic .= '</div>';
				}
			}
		}
	
		if(count($datas) > 0 || count($files) > 0) {
			$mic .= '<script type="text/javascript">';
			$mic .= 'MarqueeImageCrawler({ ';
				$mic .= "uniqueid: '" . $mic_id . "', ";
				$mic .= "style: { 'width': '150%', 'height': '" . $height . "px' }, ";
				$mic .= 'speed: ' . $speed . ', ';
				$mic .= "mouse: 'cursor driven', ";
				$mic .= 'moveatleast: 2, ';
				$mic .= 'neutral: 150, ';
				$mic .= 'savedirection: true, ';
				$mic .= 'noAddedAlt: false, ';
				$mic .= 'stopped: false, ';
				$mic .= 'noAddedSpace: false, ';
				$mic .= "direction: 'left', ";
				$mic .= 'random: true ';
			$mic .= '});';
			$mic .= '</script>';
		}
		
		echo $mic;
	}
}
?>