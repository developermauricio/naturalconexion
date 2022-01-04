<?php 
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // restict for direct access
}
?>

<h2><?php echo esc_html__('Enable Default Registration Fields', 'addify_reg'); ?></h2>
<p><?php echo esc_html__('Enable default woocommerce registration fields on registration page. When user enter these fields data will be populated on billing fields automatically.', 'addify_reg'); ?></p>

<div class="updated notice notice-success is-dismissible" id="afref_def_message">
	<p><?php echo esc_html__('Settings saved successfully.', 'addify_reg'); ?></p>
</div>

<div class="addify_df_fields">

	<form action="" method="post" id="df_form">

		<?php
		

		$def_posts = get_posts(array(
		  'post_type' => 'def_reg_fields',
		  'numberposts' => -1,
		  'order'    => 'ASC',
		  'post_status' => 'any',
		  'orderby' => 'menu_order'
		));

		foreach ($def_posts as $def_post) :
			$required    = get_post_meta($def_post->ID, 'is_required', true);
			$width       = get_post_meta($def_post->ID, 'width', true);
			$message     = get_post_meta($def_post->ID, 'message', true);
			$placeholder = get_post_meta($def_post->ID, 'placeholder', true);
			?>
		<div class="accordion">
			<div class="field_title"><b><?php echo esc_html__($def_post->post_title, 'addify_reg'); ?></b></div>
			<div class="field_status"><b><?php echo esc_html__($def_post->post_status, 'addify_reg'); ?></b></div>
		</div>
		<div class="panel">
			<input type="hidden" value="<?php echo intval($def_post->ID); ?>" name="post_ids[]">
			<p>
				<label for="label"><?php echo esc_html__('Label:', 'addify_reg'); ?></label>
				  <input type="text" value="<?php echo esc_attr($def_post->post_title); ?>" name="field_label[]" class="deffields">
			  </p>

			  <p>
				<label for="placeholder"><?php echo esc_html__('Placeholder:', 'addify_reg'); ?></label>
				  <input type="text" value="<?php echo esc_attr($placeholder); ?>" name="field_placeholder[]" class="deffields">
			  </p>

			  <p>
				<label for="message"><?php echo esc_html__('Message:', 'addify_reg'); ?></label>
				  <input type="text" value="<?php echo esc_attr($message); ?>" name="field_message[]" class="deffields">
			  </p>

			  <p>
				<label for="required"><?php echo esc_html__('Required:', 'addify_reg'); ?></label>
				  <input <?php checked($required, 1); ?> type="checkbox" value="1" name="field_required[]" class="">
			  </p>

			  <p>
				<label for="sort_order"><?php echo esc_html__('Sort Order:', 'addify_reg'); ?></label>
				  <input type="text" value="<?php echo intval($def_post->menu_order); ?>" name="field_sort_order[]" class="deffields">
			  </p>

			  <p><label for="width"><?php echo esc_html__('Field Width:', 'addify_reg'); ?></label> 
				  <select name="field_width[]" class="deffields">
					  <option <?php selected($width, 'afreg_full'); ?> value="afreg_full"><?php echo esc_html__('Full Width', 'addify_reg'); ?></option>
					  <?php 
						if ( 'State / County' != $def_post->post_title && 'Country' != $def_post->post_title ) {
							?>
						  <option <?php selected($width, 'afreg_half'); ?> value="afreg_half"><?php echo esc_html__('Half Width', 'addify_reg'); ?></option>
							<?php
						}
						?>
					  
				  </select>
			  </p>	


			  <p><label for="status"><?php echo esc_html__('Status:', 'addify_reg'); ?></label> 
				  <select name="field_status[]" class="deffields">
					  <option <?php selected($def_post->post_status, 'publish'); ?> value="publish"><?php echo esc_html__('Publish', 'addify_reg'); ?></option>
					  <option <?php selected($def_post->post_status, 'unpublish'); ?> value="unpublish"><?php echo esc_html__('Unpublish', 'addify_reg'); ?></option>
				  </select>
			  </p>	


		</div>
		<?php endforeach; ?>

		<div class="save_button"><input onClick="afregsaveFields()" type="button" name="afreg_def_fields_save" value="Save Fields" class="button button-primary button-large"></div>
	</form>

</div>

<script>
var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
  acc[i].addEventListener("click", function() {
	this.classList.toggle("active");
	var panel = this.nextElementSibling;
	if (panel.style.maxHeight){
	  panel.style.maxHeight = null;
	} else {
	  panel.style.maxHeight = panel.scrollHeight + "px";
	} 
  });
}
</script>
