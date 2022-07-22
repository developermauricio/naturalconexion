<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); } ?>
<?php
if (isset($_POST['frm_mic_display']) && $_POST['frm_mic_display'] == 'yes') {
	$did = isset($_GET['did']) ? intval($_GET['did']) : '0';
	if(!is_numeric($did)) { 
		die('<p>Are you sure you want to do this?</p>'); 
	}
	
	$mic_success = '';
	$mic_success_msg = false;
	$result = mic_cls_dbquery::mic_count($did);
	
	if ($result != '1') {
		?><div class="error fade"><p><strong><?php _e('Oops, selected details doesnt exist', 'marquee-image-crawler'); ?></strong></p></div><?php
	}
	else {
		if (isset($_GET['ac']) && sanitize_text_field($_GET['ac']) == 'del' && isset($_GET['did']) && intval($_GET['did']) != '') {
			check_admin_referer('mic_form_show');
			mic_cls_dbquery::mic_delete($did);
			$mic_success_msg = true;
			$mic_success = __('Selected record was successfully deleted.', 'marquee-image-crawler');
		}
	}
	
	if ($mic_success_msg == true) {
		?><div class="updated fade"><p><strong><?php echo $mic_success; ?></strong></p></div><?php
	}
}
?>
<div class="wrap">
    <h2><?php _e('Marquee image crawler', 'marquee-image-crawler'); ?>
	<a class="add-new-h2" href="<?php echo MICR_ADMIN_URL; ?>&amp;ac=add"><?php _e('Add New', 'marquee-image-crawler'); ?></a></h2><br />
    <div class="tool-box">
	<?php
	$myData = array();
	$myData = mic_cls_dbquery::mic_select_bygroup("");
	?>
	<form name="frm_mic_display" method="post">
      <table width="100%" class="widefat" id="straymanage">
        <thead>
          <tr>
			<th scope="col"><?php _e('Image', 'marquee-image-crawler'); ?></th>
            <th scope="col"><?php _e('Group', 'marquee-image-crawler'); ?></th>
            <th scope="col"><?php _e('Status', 'marquee-image-crawler'); ?></th>
			<th scope="col"><?php _e('Title', 'marquee-image-crawler'); ?></th>
          </tr>
        </thead>
		<tfoot>
          <tr>
			<th scope="col"><?php _e('Image', 'marquee-image-crawler'); ?></th>
            <th scope="col"><?php _e('Group', 'marquee-image-crawler'); ?></th>
            <th scope="col"><?php _e('Status', 'marquee-image-crawler'); ?></th>
			<th scope="col"><?php _e('Title', 'marquee-image-crawler'); ?></th>
          </tr>
        </tfoot>
		<tbody>
		<?php 
		$i = 0;
		if(count($myData) > 0 ) {
			foreach ($myData as $data) {
				?>
				<tr class="<?php if ($i&1) { echo'alternate'; } else { echo ''; }?>">
					<td>
						<a href="<?php echo $data['mic_image']; ?>" target="_blank">
							<img src="<?php echo $data['mic_image']; ?>" width="40"  />
						</a>
						<?php if($data['mic_link'] <> '') { ?>
						<a href="<?php echo $data['mic_link']; ?>" target="_blank"><img src="<?php echo plugin_dir_url( __DIR__ ); ?>/inc/link-icon.gif"  /></a>
						<?php } ?>
					</td>
					<td><?php echo $data['mic_group']; ?></td>
					<td><?php echo mic_cls_dbquery::mic_common_text($data['mic_status']); ?></td>
					<td><?php echo $data['mic_title']; ?>
						<div class="row-actions">
							<span class="edit"><a title="Edit" href="<?php echo MICR_ADMIN_URL; ?>&ac=edit&amp;did=<?php echo $data['mic_id']; ?>"><?php _e('Edit', 'marquee-image-crawler'); ?></a> | </span>
							<span class="trash"><a onClick="javascript:_mic_delete('<?php echo $data['mic_id']; ?>')" href="javascript:void(0);"><?php _e('Delete', 'marquee-image-crawler'); ?></a></span> 
						</div>
					</td>
				</tr>
				<?php 
				$i = $i+1; 
			} 
		}
		else {
			?><tr><td colspan="5" align="center"><?php _e('No records available', 'marquee-image-crawler'); ?></td></tr><?php 
		}
		?>
		</tbody>
        </table>
		<?php wp_nonce_field('mic_form_show'); ?>
		<input type="hidden" name="frm_mic_display" value="yes"/>
      </form>	
	  <div class="tablenav bottom">
	  <a href="<?php echo MICR_ADMIN_URL; ?>&amp;ac=add">
	  <input class="button button-primary" type="button" value="<?php _e('Add New', 'marquee-image-crawler'); ?>" /></a>
	  <a target="_blank" href="http://www.gopiplus.com/work/2020/12/18/marquee-image-crawler-wordpress-plugin/">
	  <input class="button button-primary" type="button" value="<?php _e('Short Code', 'marquee-image-crawler'); ?>" /></a>
	  <a target="_blank" href="http://www.gopiplus.com/work/2020/12/18/marquee-image-crawler-wordpress-plugin/">
	  <input class="button button-primary" type="button" value="<?php _e('Help', 'marquee-image-crawler'); ?>" /></a>
	  </div>
	</div>
</div>