<?php
use HTML5Player\Helper\DefaultArgs;
use HTML5Player\Services\VideoTemplate;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class VideoPlayer extends Widget_Base {

	/**
	 * Retrieve the widget name.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'H5VPPlayer';
	}

	/**
	 * Retrieve the widget title.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'HTML5 Video Player', 'h5vp' );
	}

	/**
	 * Retrieve the widget icon.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'fas fa-video';
	}

	/**
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * Note that currently Elementor supports only one category.
	 * When multiple categories passed, Elementor uses the first one.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'basic' ];
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies required to run the widget.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 *
	 * @return array Widget scripts dependencies.
	 */
	public function get_script_depends() {
		return ['h5vp-public'];
	}

	/**
	 * Style
	 */
	public function get_style_depends() {
		return ['h5vp-public'];
	}


	/**
	 * Register the widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Settings', 'h5vp' ),
				'tab' 	=> Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'source',
			[
				'label' 		=> esc_html__( 'Select Video', 'h5vp' ),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste Video URL", "h5vp"),
			]
		);

		$this->add_control(
			'poster',
			[
				'label' 		=> esc_html__( 'Select Poster', 'h5vp' ),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste Poster URL", "h5vp"),
			]
		);

		$this->add_control(
			'width',
			[
				'label' 		=> __( 'Width', 'h5vp' ),
				'type'			=> Controls_Manager::SLIDER,
				'size_units' 	=> [ 'px', '%'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'separator' => 'before'
			]
		);

		$this->add_control(
			'repeat',
			[
				'label' => __( 'Repeat', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'h5vp' ),
				'label_off' => __( 'No', 'h5vp' ),
				'return_value' => '1',
				'default' => '',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'muted',
			[
				'label' => __( 'Muted', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'h5vp' ),
				'label_off' => __( 'No', 'h5vp' ),
				'return_value' => '1',
				'default' => '',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'autoplay',
			[
				'label' => __( 'Autoplay', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'h5vp' ),
				'label_off' => __( 'No', 'h5vp' ),
				'return_value' => '1',
				'default' => '',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'reset_on_end',
			[
				'label' => __( 'Reset On End', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'h5vp' ),
				'label_off' => __( 'No', 'h5vp' ),
				'return_value' => '1',
				'default' => '1',
				'separator' 	=> 'before',
				'condition' => array(
					'video_source' => 'library'
				)
			]
		);

		$this->add_control(
			'auto_hide_control',
			[
				'label' => __( 'Auto Hide Control', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'Yes', 'h5vp' ),
				'label_off' => __( 'No', 'h5vp' ),
				'return_value' => '1',
				'default' => '1',
				'separator' 	=> 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'controls',
			[
				'label' => esc_html__( 'Controls', 'h5vp' ),
				'tab' 	=> Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'play-large',
			[
				'label' => __( 'Large Play', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'play-large',
				'separator' 	=> 'before',
				'default' => 'play-large'
			]
		);

		$this->add_control(
			'restart',
			[
				'label' => __( 'Restart', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'restart',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'rewind',
			[
				'label' => __( 'Rewind', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'rewind',
				// 'default' => 'rewind',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'play',
			[
				'label' => __( 'Play', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'play',
				'default' => 'play',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'fast-forward',
			[
				'label' => __( 'Fast Forward', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'fast-forward',
				// 'default' => 'fast-forward',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'progress',
			[
				'label' => __( 'Progressbar', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'progress',
				'default' => 'progress',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'current-time',
			[
				'label' => __( 'Current Time', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'current-time',
				'default' => 'current-time', 
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'duration',
			[
				'label' => __( 'Duration', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'duration',
				// 'default' => 'duration',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'mute',
			[
				'label' => __( 'Mute', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'mute',
				'default' => 'mute',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'volume',
			[
				'label' => __( 'Volume', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'volume',
				'default' => 'volume',
				'separator' 	=> 'before',
			]
		);
		$this->add_control(
			'settings',
			[
				'label' => __( 'Settings', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'settings',
				'default' => 'settings',
				'separator' 	=> 'before',
			]
		);


		$this->add_control(
			'pip',
			[
				'label' => __( 'PIP', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'pip',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'airplay',
			[
				'label' => __( 'Air Play', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'ariplay',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'fullscreen',
			[
				'label' => __( 'Full Screen', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'fullscreen',
				'separator' 	=> 'before',
			]
		);

		$this->add_control(
			'download',
			[
				'label' => __( 'Downlaod', 'h5vp' ),
				'type' => Controls_Manager::SWITCHER,
				'return_value' => 'download',
				'separator' 	=> 'before',
			]
		);

		$this->end_controls_section();

		// Player Mode and Player Size Options


	}

	/**
	 * Render the widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 */
	protected function render() {
		$s = $this->get_settings_for_display();

		$options = [];
		
	  
		$controls = [
			$s['play-large'],
			$s['restart'],
			$s['rewind'],
			$s['play'],
			$s['fast-forward'],
			$s['progress'],
			$s['current-time'],
			$s['duration'],
			$s['mute'],
			$s['volume'],
			'captions',
			$s['settings'],
			$s['pip'],
			$s['airplay'],
			$s['download'],
			$s['fullscreen'],
		];
	  
		$options = [
			'controls' => $controls,
			'tooltips' => [
				'controls' => true,
				'seek' => true,
			],
			'loop' => [
				'active' => (boolean)$s['repeat'],
			],
			'autoplay' => (boolean)$s['autoplay'],
			'muted' => (boolean)$s['muted'],
			'hideControls' => (boolean)$s['auto_hide_control'],
			'resetOnEnd' => (boolean)$s['reset_on_end'],
		];
	  

		$infos = [
			'id' => 1467,
			'resetOnEnd' => (boolean) self::i($s, 'reset_on_end'),
			'autoplay' => (boolean) self::i($s, 'autoplay'),
			'source' => $s['source'],
			'poster' => $s['poster'],
			'setSource' => true
		];

		$template = array(
			'class' => 'h5vp_elementor_initializer',
            'poster' => self::i($s, 'poster'),
			'width' => self::i($s, 'width', 'size').self::i($s, 'width', 'unit'),
			'preload' => self::i($s, 'preload'),
        );
		
		$options = [
			'options' => $options,
			'infos' => $infos,
			'template' => $template
		];

		$data = DefaultArgs::parseArgs($options);
		
		echo VideoTemplate::html($data);
		return false;
	}

	public static function i($array, $key1, $key2 = '', $default = false){
        if(isset($array[$key1][$key2])){
            return $array[$key1][$key2];
        }else if (isset($array[$key1])){
            return $array[$key1];
        }
        return $default;
    }

}
