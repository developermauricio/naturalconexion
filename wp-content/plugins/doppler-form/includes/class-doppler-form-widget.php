<?php
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers/Form_Helper.php';
require_once(dirname( __FILE__ ) . '/DopplerAPIClient/DopplerService.php');

if(!class_exists('Dplr_Subscription_Widget')):

class Dplr_Subscription_Widget extends WP_Widget {

	// constructor
	public function __construct() {
		$widget_ops = array(
			'classname' => 'dplr_form_widget', 'description' => __('Your Doppler Subscription Forms', 'doppler-form') );
		parent::__construct('dplr_form_widget', __('Doppler Form', 'doppler-form'), $widget_ops);
	}

	// widget output
	function widget($args, $instance) {

		extract($args);

		$options = get_option('dplr_settings');
		
		if(empty($options['dplr_option_useraccount']) && empty($options['dplr_option_apikey'])){
			return false;
		}

		$form = array('form' => DPLR_Form_Model::get($instance['form_id'], true));
		
		if($form['form'] != NULL) {
			echo $before_widget;
			$title = apply_filters('widget_title', $form["form"]->title);
			echo $args['before_title'] . $title . $args['after_title'];

			$form['fields'] = DPLR_Field_Model::getBy(['form_id' => $instance['form_id']],['sort_order'], true);
			DPLR_Form_Helper::generate($form);

			echo $after_widget;
		}

	}

	// save options from widget administration screen
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['form_id'] =  $new_instance[ 'form_id' ];

		return $instance;
	}

	// display form fields on widget administration screen
	function form( $instance ) {

		$forms = DPLR_Form_Model::getAll();
	
	 ?>
		<?php if (count($forms) > 0) { ?>
		<p>
			<label for="form_id"><?php _e('Select the Form that you want to publish', 'doppler-form')?></label>
			<select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>">
				<option> </option>
				<?php for ($i=0; $i < count($forms); $i++) { ?>
				<option <?php echo isset($instance['form_id']) &&  $instance['form_id'] == $forms[$i]->id ? "selected='selected'" : ""; ?> value="<?php echo $forms[$i]->id; ?>"><?php echo $forms[$i]->name; ?></option>
				<?php } ?>
			</select>
		</p>
	 	<?php
	} else {?>
		<p><?php _e('You don\'t have Forms yet!', 'doppler-form')?></p>
	<?php }
	}
}

endif;

?>
