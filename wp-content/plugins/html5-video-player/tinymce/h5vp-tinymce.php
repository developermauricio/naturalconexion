<?php
/*-------------------------------------------------------------------------------*/
/*   AJAX Get Slider List
/*-------------------------------------------------------------------------------*/
function h5vp_grab_slider_list_ajax() {
	
	if ( !isset( $_POST['grabslider'] ) ) {
		wp_die();
		} 
		else {
			
			$list = array();
			
			global $post;
			
			$args = array(
  				'post_type' => 'videoplayer',
  				'order' => 'ASC',
				'posts_per_page' => -1,
  				'post_status' => 'publish'
		
				);

				$myposts = get_posts( $args );
				foreach( $myposts as $post ) :	setup_postdata($post);

				$list[$post->ID] = array('val' => $post->ID, 'title' => esc_html(esc_js(the_title(NULL, NULL, FALSE))) );

				endforeach;
				
				}
		
			echo json_encode($list); //Send to Option List ( Array )
			wp_die();


	}

add_action('wp_ajax_h5vp_grab_slider_list_ajax', 'h5vp_grab_slider_list_ajax');

/*-------------------------------------------------------------------------------*/
/*   Frontend Register JS & CSS
/*-------------------------------------------------------------------------------*/
function h5vp_reg_script() {
	wp_register_style( 'h5vp-tinymcecss', plugins_url( 'tinymce/tinymce.css' , dirname(__FILE__) ), false, 'all');
	wp_register_script( 'h5vp-tinymcejs', plugins_url( 'tinymce/tinymce.js' , dirname(__FILE__) ), false );	
}
add_action( 'admin_init', 'h5vp_reg_script' );


//-------------------------------------------------------------------------------------------------	
if ( strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post-new.php' ) || strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post.php' ) ) {
	
// ADD STYLE & SCRIPT
	add_action( 'admin_head', 'h5vp_editor_add_init' );
		function h5vp_editor_add_init() {
			
			if ( get_post_type( get_the_ID() ) != 'videoplayer' ) {
				
				wp_enqueue_style( 'h5vp-tinymcecss' );
				wp_enqueue_script( 'h5vp-tinymcejs' );

		?>
        <?php
			}
			
		}
	
// ADD MEDIA BUTOON	
	add_action( 'media_buttons', 'h5vp_shortcode_button', 1 );
		function h5vp_shortcode_button() {
			$img = H5VP_PLUGIN_DIR .'img/icn.png';
			$container_id = 'h5vpmodal';
			$title = 'Insert Html5 Video Player';
			$context = '
			<a class="thickbox button" id="h5vp_shortcode_button" title="'.$title.'" style="outline: medium none !important; cursor: pointer;" >
			<img src="'.$img.'" alt="" width="20" height="20" style="position:relative; top:-1px"/>Html5 video player</a>';
			echo $context;
		}	
}


// GENERATE POPUP CONTENT
add_action('admin_footer', 'h5vp_popup_content');	
function h5vp_popup_content() {

if ( strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post-new.php' ) || strstr( $_SERVER['REQUEST_URI'], 'wp-admin/post.php' ) ) {

if ( get_post_type( get_the_ID() ) != 'videoplayer' ) {
// START GENERATE POPUP CONTENT

?>
<div id="h5vpmodal" style="display:none;">
<div id="tinyform" style="width: 550px;">
<form method="post">

<div class="h5vp_input" id="h5vptinymce_select_slider_div">
<label class="label_option" for="h5vptinymce_select_slider">Html5 Video Player</label>
	<select class="h5vp_select" name="h5vptinymce_select_slider" id="h5vptinymce_select_slider">
    <option id="selectslider" type="text" value="select">- Select Player -</option>
</select>
<div class="clearfix"></div>
</div>

<div class="h5vp_button">
<input type="button" value="Insert Shortcode" name="h5vp_insert_scrt" id="h5vp_insert_scrt" class="button-secondary" />	
<div class="clearfix"></div>
</div>

</form>
</div>
</div>
<?php 
	}
  } //END
}

?>