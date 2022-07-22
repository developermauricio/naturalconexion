<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } ?>
<div class="wrap">
<?php
$did = isset($_GET['did']) ? intval($_GET['did']) : '0';
if(!is_numeric($did)) { 
	die('<p>Are you sure you want to do this?</p>'); 
}

$result = mic_cls_dbquery::mic_count($did);
if ($result != '1') {
	?><div class="error fade"><p><strong><?php _e('Oops, selected details doesnt exist', 'marquee-image-crawler'); ?></strong></p></div><?php
}
else {
	$mic_errors = array();
	$mic_success = '';
	$mic_error_found = false;
	
	$data = array();
	$data = mic_cls_dbquery::mic_select_byid($did);
	
	$form = array(
		'mic_id' => $data['mic_id'],
		'mic_image' => $data['mic_image'],
		'mic_link' => $data['mic_link'],
		'mic_title' => $data['mic_title'],
		'mic_width' => $data['mic_width'],
		'mic_status' => $data['mic_status'],
		'mic_group' => $data['mic_group']
	);
}

if (isset($_POST['mic_form_submit']) && sanitize_text_field($_POST['mic_form_submit']) == 'yes') {
	check_admin_referer('mic_form_edit');
	
	$form['mic_image'] = isset($_POST['mic_image']) ? esc_url_raw($_POST['mic_image']) : '';
	if ($form['mic_image'] == '') {
		$mic_errors[] = __('Please enter the image path.', 'marquee-image-crawler');
		$mic_error_found = true;
	}
	$form['mic_title'] = isset($_POST['mic_title']) ? sanitize_text_field($_POST['mic_title']) : '';
	$form['mic_link'] = isset($_POST['mic_link']) ? esc_url_raw($_POST['mic_link']) : '';
	$form['mic_group'] = isset($_POST['mic_group']) ? sanitize_text_field($_POST['mic_group']) : '';
	if ($form['mic_group'] == '') {
		$form['mic_group'] = isset($_POST['mic_group_txt']) ? sanitize_text_field($_POST['mic_group_txt']) : '';
	}
	if ($form['mic_group'] == '') {
		$mic_errors[] = __('Please enter the image group.', 'marquee-image-crawler');
		$mic_error_found = true;
	}
	$form['mic_width'] = '0';
	$form['mic_status'] = isset($_POST['mic_status']) ? sanitize_text_field($_POST['mic_status']) : '';
	$form['mic_id'] = isset($_POST['mic_id']) ? sanitize_text_field($_POST['mic_id']) : '';

	if ($mic_error_found == FALSE)
	{	
		$status = mic_cls_dbquery::mic_action_ins($form, "update");
		if($status == 'update') {
			$mic_success = __('Image details was successfully updated.', 'marquee-image-crawler');
		}
		else {
			$mic_errors[] = __('Oops, something went wrong. try again.', 'marquee-image-crawler');
			$mic_error_found = true;
		}
	}
}

if ($mic_error_found == true && isset($mic_errors[0]) == true) {
	?><div class="error fade"><p><strong><?php echo $mic_errors[0]; ?></strong></p></div><?php
}

if ($mic_error_found == false && strlen($mic_success) > 0) {
	?><div class="updated fade"><p><strong><?php echo $mic_success; ?>
	<a href="<?php echo MICR_ADMIN_URL; ?>"><?php _e('Click here', 'marquee-image-crawler'); ?></a> <?php _e('to view the details', 'marquee-image-crawler'); ?>
	</strong></p></div><?php
}

?>
<script type="text/javascript">
jQuery(document).ready(function($){
    $('#upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
        .on('select', function(e){
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = image.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            // Output to the console uploaded_image
            console.log(uploaded_image);
            var img_imageurl = uploaded_image.toJSON().url;
			var img_imagetitle = uploaded_image.toJSON().title;
            // Let's assign the url value to the input field
            $('#mic_image').val(img_imageurl);
			//$('#mic_title').val(img_imagetitle);
        });
    });
});
</script>
<?php
wp_enqueue_script('jquery');
wp_enqueue_media();
?>
<div class="form-wrap">
	<h1 class="wp-heading-inline"><?php _e('Update image', 'marquee-image-crawler'); ?></h1>
	<form name="mic_form" method="post" action="#" onsubmit="return _mic_submit()"  >
      
	  <label for="tag-image"><strong><?php _e('Image (URL)', 'marquee-image-crawler'); ?></strong></label>
      <input name="mic_image" type="text" id="mic_image" value="<?php echo $data['mic_image']; ?>" size="60" />
	  <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">
      <p><?php _e('Where is the image located on the internet.', 'marquee-image-crawler'); ?> <br />
	  <a href="<?php echo $data['mic_image']; ?>" target="_blank"><img src="<?php echo $data['mic_image']; ?>" width="40"  /></a></p>
	  
	  <label for="tag-link"><strong><?php _e('Image title (Optional)', 'marquee-image-crawler'); ?></strong></label>
      <input name="mic_title" type="text" id="mic_title" value="<?php echo $form['mic_title']; ?>" size="60" />
      <p><?php _e('Enter title for your image.', 'marquee-image-crawler'); ?></p>
	  
	  <!--<label for="tag-width"><strong><?php //_e('Image width (Optional)', 'marquee-image-crawler'); ?></strong></label>
	  <input name="mic_width" type="text" id="mic_width" value="<?php //echo $form['mic_width']; ?>" maxlength="3" />
	  <p><?php //_e('Enter the image width (Optional).', 'marquee-image-crawler'); ?></p>-->
	  
	  <label for="tag-image"><strong><?php _e('Link (Optional)', 'marquee-image-crawler'); ?></strong></label>
      <input name="mic_link" type="text" id="mic_link" value="<?php echo $data['mic_link']; ?>" size="60" />
	  <p><?php _e('When someone clicks on the image, where do you want to send them.', 'marquee-image-crawler'); ?> <br />(ex: http://www.gopiplus.com/work/)</p>
	  
      <label for="tag-select-gallery-group"><strong><?php _e('Image group', 'marquee-image-crawler'); ?></strong></label>
		<select name="mic_group" id="mic_group">
			<option value=''><?php _e('Select', 'email-posts-to-subscribers'); ?></option>
			<?php
			$selected = "";
			$groups = array();
			$groups = mic_cls_dbquery::mic_group();
			
			if(count($groups) > 0) {
				foreach ($groups as $group) {
					if(strtoupper($form['mic_group']) == strtoupper($group["mic_group"])) { 
						$selected = "selected"; 
					}
					?>
					<option value="<?php echo stripslashes($group["mic_group"]); ?>" <?php echo $selected; ?>>
						<?php echo stripslashes($group["mic_group"]); ?>
					</option>
					<?php
					$selected = "";
				}
			}
			?>
		</select>
		(or) 
	   	<input name="mic_group_txt" type="text" id="mic_group_txt" value="" maxlength="10" onkeyup="return _mic_numericandtext(document.mic_form.mic_group_txt)" />
      <p><?php _e('This is to group the images. Select your group.', 'marquee-image-crawler'); ?></p>
	  
      <label for="tag-display-status"><strong><?php _e('Display', 'marquee-image-crawler'); ?></strong></label>
      <select name="mic_status" id="mic_status">
        <option value='Yes' <?php if($form['mic_status'] == 'Yes') { echo 'selected' ; } ?>>Yes</option>
        <option value='No' <?php if($form['mic_status'] == 'No') { echo 'selected' ; } ?>>No</option>
      </select>
      <p><?php _e('Do you want the image to show in the frontend?', 'marquee-image-crawler'); ?></p>
	  
      <input name="mic_id" id="mic_id" type="hidden" value="<?php echo $form['mic_id']; ?>">
      <input type="hidden" name="mic_form_submit" value="yes"/>
      <p class="submit">
        <input name="submit" class="button button-primary" value="<?php _e('Submit', 'marquee-image-crawler'); ?>" type="submit" />
        <input name="cancel" class="button button-primary" onclick="_mic_redirect()" value="<?php _e('Cancel', 'marquee-image-crawler'); ?>" type="button" />
        <input name="help" class="button button-primary" onclick="_mic_help()" value="<?php _e('Help', 'marquee-image-crawler'); ?>" type="button" />
      </p>
	  <?php wp_nonce_field('mic_form_edit'); ?>
    </form>
</div>
</div>