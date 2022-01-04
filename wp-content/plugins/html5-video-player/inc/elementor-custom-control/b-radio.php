<?php
/**
 * FileSelect control.
 *
 * A control for selecting any type of files.
 *
 * @since 1.0.0
 */
class BPlugins_B_Radio extends \Elementor\Base_Data_Control {

	/**
	 * Get control type.
	 *
	 * Retrieve the control type, in this case `FILESELECT`.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Control type.
	 */
	public function get_type() {
		return 'b-radio';
	}

	/**
	 * Enqueue control scripts and styles.
	 *
	 * Used to register and enqueue custom scripts and styles
	 * for this control.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function enqueue() {
		wp_enqueue_media();
		wp_enqueue_style('thickbox');
	    wp_enqueue_script('media-upload');
	    wp_enqueue_script('thickbox');
		// Scripts
		// wp_register_script( 'bplugins-elementor-controls', plugins_url( '/controls.js', __FILE__ ), [ 'jquery' ], '1.0.0', true );
		wp_enqueue_script( 'bplugins-elementor-controls' );
	}

	/**
	 * Get default settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Control default settings.
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'options' => [
				'new' => 'New '
			]
		];
		
	}

	/**
	 * Render control output in the editor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid();
		
		?>

		<div class="elementor-control-field">
		{{{Object.keys(data.options).map(item => {
			<div>{{{item}}}</div>
		})}}};
		</div>
		
		
		<!-- <div class="elementor-control-field">
			<label for="<?php //echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			
			<div class="elementor-control-input-wrapper">
				
			</div>
		</div> -->
		<# if ( data.description ) { console.log('data', data) #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}
}