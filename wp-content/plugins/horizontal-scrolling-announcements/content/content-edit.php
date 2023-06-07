<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } ?>
<div class="wrap">
<?php
$guid = isset($_GET['guid']) ? $_GET['guid'] : '0';
hsas_cls_security::hsas_check_guid($guid);

// First check if ID exist with requested ID
$result = hsas_cls_dbquery::hsas_content_count($guid);
if ($result != '1') {
	?><div class="error fade">
		<p><strong>
			<?php echo __( 'Oops, selected details does not exists.', 'horizontal-scrolling-announcements' ); ?>
		</strong></p>
	</div><?php
} else {
	$hsas_errors = array();
	$hsas_success = '';
	$hsas_error_found = FALSE;

	$data = array();
	$data = hsas_cls_dbquery::hsas_content_view($guid, 0, 1);

	// Preset the form fields
	$form = array(
		'hsas_id' 	=> $data[0]['hsas_id'],
		'hsas_guid' => $data[0]['hsas_guid'],
		'hsas_text' => stripslashes($data[0]['hsas_text']),
		'hsas_link' => $data[0]['hsas_link'],
		'hsas_target' => $data[0]['hsas_target'],
		'hsas_order' => $data[0]['hsas_order'],
		'hsas_group' => $data[0]['hsas_group'],
		'hsas_datestart' => $data[0]['hsas_datestart'],
		'hsas_timestart' => $data[0]['hsas_timestart'],
		'hsas_dateend' => $data[0]['hsas_dateend'],
		'hsas_timeend' => $data[0]['hsas_timeend'],
		'hsas_css' => $data[0]['hsas_css']
	);
}

// Form submitted, check the data
if (isset($_POST['hsas_form_submit']) && $_POST['hsas_form_submit'] == 'yes')
{
	//	Just security thingy that wordpress offers us
	check_admin_referer('hsas_form_add');
	
	$form['hsas_text'] 		= isset($_POST['hsas_text']) ? wp_filter_post_kses($_POST['hsas_text']) : '';	
	$form['hsas_link'] 		= isset($_POST['hsas_link']) ? esc_url_raw($_POST['hsas_link']) : '';
	$form['hsas_target'] 	= isset($_POST['hsas_target']) ? sanitize_text_field($_POST['hsas_target']) : '';
	$form['hsas_order'] 	= isset($_POST['hsas_order']) ? intval($_POST['hsas_order']) : '';
	$form['hsas_group'] 	= isset($_POST['hsas_group']) ? sanitize_text_field($_POST['hsas_group']) : '';
	
	if( $form['hsas_group'] == "")
	{
		$form['hsas_group'] = isset($_POST['hsas_group']) ? sanitize_text_field($_POST['hsas_group_txt']) : '';
	}
	
	$form['hsas_datestart'] = isset($_POST['hsas_datestart']) ? sanitize_text_field($_POST['hsas_datestart']) : '';
	$form['hsas_timestart'] = isset($_POST['hsas_timestart']) ? sanitize_text_field($_POST['hsas_timestart']) : '';
	$form['hsas_dateend'] 	= isset($_POST['hsas_dateend']) ? sanitize_text_field($_POST['hsas_dateend']) : '';
	$form['hsas_timeend'] 	= isset($_POST['hsas_timeend']) ? sanitize_text_field($_POST['hsas_timeend']) : '';
	$form['hsas_css'] 		= isset($_POST['hsas_css']) ? sanitize_text_field($_POST['hsas_css']) : '';
	
	if ($form['hsas_text'] == '')
	{
		$hsas_errors[] = __('Please enter your announcement text.', 'horizontal-scrolling-announcements');
		$hsas_error_found = true;
	}
	
	if ($form['hsas_group'] == '')
	{
		$hsas_errors[] = __('Please select/enter group for this announcement text.', 'horizontal-scrolling-announcements');
		$hsas_error_found = true;
	}
	
	if ($form['hsas_datestart'] == '')
	{
		$hsas_errors[] = __('Please enter start date for this announcement text.', 'horizontal-scrolling-announcements');
		$hsas_error_found = true;
	}
	
	if ($form['hsas_dateend'] == '')
	{
		$hsas_errors[] = __('Please enter end date for this announcement text.', 'horizontal-scrolling-announcements');
		$hsas_error_found = true;
	}
	
	//	No errors found, we can add this Group to the table
	if ($hsas_error_found == false)
	{
		$action = false;
		$action = hsas_cls_dbquery::hsas_content_action($form, "update");
		if($action == "sus")
		{
			$hsas_success = __('Announcement successfully updated.', 'horizontal-scrolling-announcements');
		}
		elseif($action == "ext")
		{
			$hsas_errors[] = __('Announcement already exists.', 'horizontal-scrolling-announcements');
		}
		
		// Reset the form fields
		$form = array(
			'hsas_id' 	=> '',
			'hsas_guid' => '',
			'hsas_text' => '',
			'hsas_link' => '',
			'hsas_target' => '',
			'hsas_order' => '',
			'hsas_group' => '',
			'hsas_datestart' => '',
			'hsas_timestart' => '',
			'hsas_dateend' => '',
			'hsas_timeend' => '',
			'hsas_css' => ''
		);
	}
}

if ($hsas_error_found == true && isset($hsas_errors[0]) == true)
{
	?><div class="error fade"><p><strong><?php echo $hsas_errors[0]; ?></strong></p></div><?php
}

if ($hsas_error_found == false && strlen($hsas_success) > 0)
{
	?>
	<div class="updated fade">
		<p><strong><?php echo $hsas_success; ?> <a href="<?php echo HSAS_ADMINURL; ?>?page=hsas-content"><?php _e('Click here', 'horizontal-scrolling-announcements'); ?></a>
		<?php _e(' to view the details', 'horizontal-scrolling-announcements'); ?></strong></p>
	</div>
	<?php
}
?>
<div class="form-wrap">
	<h3><?php _e('Edit Announcement', 'horizontal-scrolling-announcements'); ?></h3>
	<form name="hsas_form" method="post" action="#" onsubmit="return _hsas_insert()"  >
		
		<label for="tag"><?php _e('Announcement Text', 'horizontal-scrolling-announcements'); ?></label>
		<textarea name="hsas_text" cols="80" rows="6" id="hsas_text"><?php echo $form['hsas_text']; ?></textarea>
		<p><?php _e('Please enter your announcement text.', 'horizontal-scrolling-announcements'); ?></p>
			
		<label for="tag"><?php _e('Link', 'horizontal-scrolling-announcements'); ?></label>
		<input name="hsas_link" type="text" id="hsas_link" value="<?php echo $form['hsas_link']; ?>" maxlength="1024" size="83"  />
		<p><?php _e('Please enter your announcement link.', 'horizontal-scrolling-announcements'); ?></p>
		
		<label for="tag"><?php _e('Link Target', 'horizontal-scrolling-announcements'); ?></label>
		<select name="hsas_target" id="hsas_target">
			<option value='_self' <?php if($form['hsas_target']=='_self') { echo 'selected="selected"' ; } ?>><?php _e('Open in same window', 'horizontal-scrolling-announcements'); ?></option>
			<option value='_blank' <?php if($form['hsas_target']=='_blank') { echo 'selected="selected"' ; } ?>><?php _e('Open in new window', 'horizontal-scrolling-announcements'); ?></option>
		</select>
		<p><?php _e('Please select your link target.', 'horizontal-scrolling-announcements'); ?></p>
		
		<label for="tag"><?php _e('Display Order', 'horizontal-scrolling-announcements'); ?></label>
		<input name="hsas_order" type="text" id="hsas_order" value="<?php echo $form['hsas_order']; ?>" maxlength="2"  />
		<p><?php _e('Please enter the display order, only number.', 'horizontal-scrolling-announcements'); ?></p>
		
		<label for="tag"><?php _e('Group', 'horizontal-scrolling-announcements'); ?></label>
		<input name="hsas_group_txt" type="text" id="hsas_group_txt" value="" maxlength="25" onkeyup="return _owlc_numericandtext(document.hsas_form.hsas_group_txt)" />
		(or)
		<select name="hsas_group" id="hsas_group">
			<option value=''><?php _e('Select', 'horizontal-scrolling-announcements'); ?></option>
			<?php
			$thisselected = "";
			$groups = array();
			$groups = hsas_cls_dbquery::hsas_content_group();
			if(count($groups) > 0)
			{
				$i = 1;
				foreach ($groups as $group)
				{
					if(stripslashes($group["hsas_group"]) == $form['hsas_group']) 
					{ 
						$thisselected = 'selected="selected"' ; 
					}
					?><option value="<?php echo $group["hsas_group"]; ?>" <?php echo $thisselected; ?>><?php echo $group["hsas_group"]; ?></option><?php
					$thisselected = "";
				}
			}
			?>
		</select>
		<p><?php _e('Please select/enter group for this announcement text.', 'horizontal-scrolling-announcements'); ?></p>
		
		<label for="tag"><?php _e('Start Date', 'horizontal-scrolling-announcements'); ?></label>
		<input name="hsas_datestart" type="text" id="hsas_datestart" value="<?php echo $form['hsas_datestart']; ?>" maxlength="10"  />
		<p><?php _e('Please enter start date for this announcement text.', 'horizontal-scrolling-announcements'); ?> YYYY-MM-DD</p>
		
		<label for="tag"><?php _e('End Date', 'horizontal-scrolling-announcements'); ?></label>
		<input name="hsas_dateend" type="text" id="hsas_dateend" value="<?php echo $form['hsas_dateend']; ?>" maxlength="10"  />
		<p><?php _e('Please enter end date for this announcement text.', 'horizontal-scrolling-announcements'); ?> YYYY-MM-DD</p>
		
		<input type="hidden" name="hsas_form_submit" value="yes"/>
		<input type="hidden" name="hsas_guid" id="hsas_guid" value="<?php echo $form['hsas_guid']; ?>"/>
		<p class="submit">
		<input name="publish" lang="publish" class="button button-primary" value="<?php _e('Submit', 'horizontal-scrolling-announcements'); ?>" type="submit" />
		<input name="publish" lang="publish" class="button button-primary" onclick="_hsas_redirect()" value="<?php _e('Cancel', 'horizontal-scrolling-announcements'); ?>" type="button" />
		<input name="Help" lang="publish" class="button button-primary" onclick="_hsas_help()" value="<?php _e('Help', 'horizontal-scrolling-announcements'); ?>" type="button" /><br />
		</p>
		<?php wp_nonce_field('hsas_form_add'); ?>
		
	</form>
</div>
</div>