<?php
/**
 * Handle Products list feature.
 *
 * @link       https://orionorigin.com
 * @since      1.0.0
 * @author     Orion
 * @package    Orion
 */

if ( ! class_exists( 'WAD_Products_List' ) ) {
	/**
	 * Description of class-o-list
	 *
	 * @author ORION
	 */
	class WAD_Products_List {

		/**
		 * Class version.
		 */
		public const VERSION = '1.0';

		/**
		 * Product List ID.
		 *
		 * @var string List ID.
		 */
		private $id;

		/**
		 * Get Product List ID.
		 *
		 * @return mixed
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Set Product List ID.
		 *
		 * @param mixed $id Product List ID.
		 *
		 * @return void
		 */
		public function set_id( $id ): void {
			$this->id = $id;
		}

		/**
		 * Args.
		 *
		 * @var mixed Args.
		 */
		private $args;


		/**
		 * Set Product List Args.
		 *
		 * @param mixed $args Args.
		 *
		 * @return void
		 */
		public function set_args( $args ): void {
			$this->args = $args;
		}

		/**
		 * Products.
		 *
		 * @var mixed Products.
		 */
		private $products;

		/**
		 * Set Products into Product List.
		 *
		 * @param mixed $products List of Products.
		 *
		 * @return void
		 */
		public function set_products( $products ) {
			$this->products = $products;
		}

		/**
		 * Last fetch products.
		 *
		 * @var mixed Last fetch products.
		 */
		private $last_fetch;

		/**
		 * Get latest fetch.
		 *
		 * @return mixed
		 */
		public function get_last_fetch() {
			return $this->last_fetch;
		}

		/**
		 * Set last fetch products into Product List.
		 *
		 * @param mixed $last_fetch last fetch products into Product List.
		 *
		 * @return void
		 */
		public function set_last_fetch( $last_fetch ) {
			$this->last_fetch = $last_fetch;
		}

		/**
		 * Constructor.
		 *
		 * @param string $list_id Product List ID.
		 */
		public function __construct( $list_id ) {
			if ( $list_id ) {
				$this->set_id( $list_id );
				$this->set_args( get_post_meta( $list_id, 'o-list', true ) );
				$this->set_products( false );
				$this->set_last_fetch( false );
			}
		}

		/**
		 * Enqueue scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			global $post_type;
			$list_cpts = array(
				'o-list',
			);

			if ( in_array( $post_type, $list_cpts, true ) ) {
				wp_enqueue_script( 'o-products-list', plugin_dir_url( __FILE__ ) . '../admin/js/o-products-list.js', array( 'jquery', 'o-admin' ), self::VERSION, false );
			}
		}

		/**
		 * Run class details and options.
		 *
		 * @return void
		 */
		public function run() {
			add_action( 'init', array( $this, 'register_cpt_list' ) );
			add_action( 'add_meta_boxes', array( $this, 'get_list_metabox' ) );
			add_action( 'save_post_o-list', array( $this, 'save_list' ) );
			add_action( 'wp_ajax_o-list-evaluate-query', array( $this, 'evaluate_query' ) );
			add_action( 'admin_notices', array( $this, 'get_max_input_vars_php_ini' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Register the list custom post type
		 */
		public function register_cpt_list() {

			$labels = array(
				'name'               => __( 'List', 'o-list' ),
				'singular_name'      => __( 'List', 'o-list' ),
				'add_new'            => __( 'New list', 'o-list' ),
				'add_new_item'       => __( 'New list', 'o-list' ),
				'edit_item'          => __( 'Edit list', 'o-list' ),
				'new_item'           => __( 'New list', 'o-list' ),
				'view_item'          => __( 'View list', 'o-list' ),
				'not_found'          => __( 'No list found', 'o-list' ),
				'not_found_in_trash' => __( 'No list in the trash', 'o-list' ),
				'menu_name'          => __( 'Lists', 'o-list' ),
			);

			$args = array(
				'labels'              => $labels,
				'hierarchical'        => false,
				'description'         => 'Lists',
				'supports'            => array( 'title' ),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'query_var'           => false,
				'can_export'          => true,
			);

			register_post_type( 'o-list', $args );
		}

		/**
		 * Adds the metabox for the list CPT
		 */
		public function get_list_metabox() {

			$screens = array( 'o-list' );

			foreach ( $screens as $screen ) {

				add_meta_box(
					'o-list-settings-box',
					__( 'List settings', 'o-list' ),
					array( $this, 'get_list_settings_page' ),
					$screen
				);
			}
		}

		/**
		 * List CPT metabox callback
		 */
		public function get_list_settings_page() {
			?>
		<div class='block-form'>
			<?php

			$begin = array(
				'type' => 'sectionbegin',
				'id'   => 'o-datasource-container',
			);

			$extraction_type = array(
				'title'   => __( 'Extraction type', 'o-list' ),
				'name'    => 'o-list[type]',
				'type'    => 'radio',
				'class'   => 'o-list-extraction-type',
				'default' => 'by-id',
				'desc'    => __( 'How would you like to specify which products you want to include in the list?', 'o-list' ),
				'options' => array(
					'by-id'          => __( 'By ID', 'o-list' ),
					'custom-request' => __( 'Dynamic request', 'o-list' ),
				),
			);

			$list_id     = get_the_ID();
			$metas       = get_post_meta( $list_id, 'o-list', true );
			$action_meta = O_Utils::get_proper_value( $metas, 'type', 'by-id' );
			if ( 'by-id' === $action_meta ) {
				$custom_request_css = 'display:none;';
				$by_id_css          = '';
			} else {
				$by_id_css          = 'display:none;';
				$custom_request_css = '';
			}

			$ids_list = array(
				'title'     => __( 'Products IDs', 'o-list' ),
				'desc'      => __( 'Values separated by commas', 'o-list' ),
				'name'      => 'o-list[ids]',
				'row_class' => 'extract-by-id-row',
				'row_css'   => $by_id_css,
				'type'      => 'text',
				'default'   => '',
			);

			$author = array(
				'title'     => __( 'Author', 'o-list' ),
				'desc'      => __( 'Retrieves only the elements created by the specified authors', 'o-list' ),
				'name'      => 'o-list[author__in]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'multiselect',
				'default'   => '',
				'options'   => $this->get_authors(),
			);

			$exclude = array(
				'title'     => __( 'Exclude', 'o-list' ),
				'desc'      => __( 'Excludes the following elements IDs from the results (values separated by commas)', 'o-list' ),
				'name'      => 'o-list[post__not_in]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'text',
				'default'   => '',
			);

			$metas_relationship = array(
				'title'     => __( 'Metas relationship', 'o-list' ),
				'name'      => 'o-list[meta_query][relation]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'select',
				'default'   => '',
				'options'   => array(
					'AND' => __( 'AND', 'o-list' ),
					'OR'  => __( 'OR', 'o-list' ),
				),
			);

			$meta_filter_key = array(
				'title'   => __( 'Key', 'o-list' ),
				'name'    => 'key',
				'type'    => 'text',
				'default' => '',
			);

			$meta_filter_compare = array(
				'title'   => __( 'Operator', 'o-list' ),
				'tip'     => __( "If the operator  is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN', make sure the different values are separated by a comma", 'o-list' ),
				'name'    => 'compare',
				'type'    => 'select',
				'options' => array(
					'='           => 'EQUALS',
					'!='          => 'NOT EQUALS',
					'>'           => 'MORE THAN',
					'>='          => 'MORE OR EQUALS',
					'<'           => 'LESS THAN',
					'<='          => 'LESS OR EQUALS',
					'LIKE'        => 'LIKE',
					'NOT LIKE'    => 'NOT LIKE',
					'IN'          => 'IN',
					'NOT IN'      => 'NOT IN',
					'BETWEEN'     => 'BETWEEN',
					'NOT BETWEEN' => 'NOT BETWEEN',
					'NOT EXISTS'  => 'NOT EXISTS',
					'REGEXP'      => 'REGEXP',
					'NOT REGEXP'  => 'NOT REGEXP',
					'RLIKE'       => 'RLIKE',
				),
			);

			$meta_filter_value = array(
				'title'   => __( 'Value', 'o-list' ),
				'name'    => 'value',
				'type'    => 'text',
				'default' => '',
			);

			$meta_filter_type = array(
				'title'   => __( 'Type', 'o-list' ),
				'name'    => 'type',
				'type'    => 'select',
				'options' => array(
					''         => 'Undefined',
					'NUMERIC'  => 'NUMERIC',
					'BINARY'   => 'BINARY',
					'DATE'     => 'DATE',
					'CHAR'     => 'CHAR',
					'DATETIME' => 'DATETIME',
					'DECIMAL'  => 'DECIMAL',
					'SIGNED'   => 'SIGNED',
					'TIME'     => 'TIME',
					'UNSIGNED' => 'UNSIGNED',
				),
			);

			$tax_query_data = $this->get_tax_query_data();
			?>
			<script>
				var o_tax_query_recap=<?php echo wp_json_encode( $tax_query_data['values'] ); ?>;
			</script>
			<?php

			$metas_filters = array(
				'title'     => __( 'Metas', 'o-list' ),
				'desc'      => __( 'Filter by metas', 'o-list' ),
				'name'      => 'o-list[meta_query][queries]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'repeatable-fields',
				'fields'    => array( $meta_filter_key, $meta_filter_compare, $meta_filter_value, $meta_filter_type ),
			);

			$taxonomies_relationship = array(
				'title'     => __( 'Taxonomies relationship', 'o-list' ),
				'name'      => 'o-list[tax_query][relation]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'select',
				'default'   => '',
				'options'   => array(
					'AND' => 'AND',
					'OR'  => 'OR',
				),
			);

			$taxonomy_filter_key = array(
				'title'   => __( 'Taxonomy', 'o-list' ),
				'name'    => 'taxonomy',
				'type'    => 'select',
				'class'   => 'o-list-taxonomies-selector',
				'options' => $tax_query_data['params'],
			);

			$taxonomy_filter_operator = array(
				'title'   => __( 'Operator', 'o-list' ),
				'name'    => 'operator',
				'type'    => 'select',
				'options' => array(
					'IN'     => 'IN',
					'NOT IN' => 'NOT IN',
					'AND'    => 'AND',
				),
			);

			$taxonomy_filter_value = array(
				'title'   => __( 'Value', 'o-list' ),
				'name'    => 'terms',
				'type'    => 'multiselect',
				'class'   => 'o-list-terms-selector',
				'options' => $tax_query_data['values_arr'],
			);

			$taxonomies_filters = array(
				'title'     => __( 'Taxonomies', 'o-list' ),
				'desc'      => __( 'Filter by taxonomies (Categories, Tags, Attributes)', 'o-list' ),
				'name'      => 'o-list[tax_query][queries]',
				'row_class' => 'extract-by-custom-request-row',
				'row_css'   => $custom_request_css,
				'type'      => 'repeatable-fields',
				'id'        => 'o-list-taxonomy-table',
				'fields'    => array( $taxonomy_filter_key, $taxonomy_filter_operator, $taxonomy_filter_value ),
			);

			$end      = array( 'type' => 'sectionend' );
			$settings = array(
				$begin,
				$extraction_type,
				$ids_list,
				$author,
				$exclude,
				$taxonomies_relationship,
				$taxonomies_filters,
				$metas_relationship,
				$metas_filters,
				$end,
			);
			echo wp_kses( O_Utils::admin_fields( $settings ), O_Utils::get_allowed_tags() );
			?>
		</div>
		<input type="hidden" name="o_list_securite_nonce" value="<?php echo esc_html( wp_create_nonce( 'o-list-security-nonce' ) ); ?>"/>
		<a id="o-list-check-query" class="button mg-top"><?php esc_attr_e( 'Evaluate', 'o-list' ); ?></a>
		<span id="o-list-loading" class="o-list-loading mg-top mg-left" style="display: none;"></span>
		<div id="debug" class="mg-top"></div>
			<?php
			global $o_row_templates;
			?>
		<script>
			var o_rows_tpl=<?php echo wp_json_encode( $o_row_templates ); ?>;
		</script>
			<?php
		}

		/**
		 * Get Tax query data.
		 *
		 * @return array[]
		 */
		private function get_tax_query_data() {
			$tax_terms = get_taxonomies( array(), 'objects' );

			$params            = array();
			$values            = array();
			$values_arr        = array();
			$values_arr_by_key = array();

			foreach ( $tax_terms as $tax_key => $tax_obj ) {
				if ( ! in_array( 'product', $tax_obj->object_type ) ) {
					continue;
				}

				$params[ $tax_key ] = $tax_obj->labels->singular_name;
				$terms              = get_terms( $tax_key );
				$terms_select       = '';
				foreach ( $terms as $term ) {
					$terms_select                .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
					$values_arr[ $term->term_id ] = $term->name;
					if ( ! isset( $values_arr_by_key[ $tax_key ] ) ) {
						$values_arr_by_key[ $tax_key ] = array();
					}
					$values_arr_by_key[ $tax_key ][ $term->term_id ] = $term->name;
				}
				if ( $terms_select ) {
					$values[ $tax_key ] = $terms_select;
				} else {
					unset( $params[ $tax_key ] );
				}
			}

			return array(
				'params'            => $params,
				'values'            => $values,
				'values_arr'        => $values_arr,
				'values_arr_by_key' => $values_arr_by_key,
			);
		}

		/**
		 * Provide list of authors.
		 *
		 * @return array
		 */
		private function get_authors() {
			$all_users   = get_users(
				array(
					'has_published_posts' => array( 'product' ),
				)
			);
			$authors_arr = array( '' => 'Any' );
			foreach ( $all_users as $user ) {
				$authors_arr[ $user->ID ] = $user->user_nicename;
			}

			return $authors_arr;
		}

		/**
		 * Saves the display data.
		 *
		 * @param mixed $post_id Post ID.
		 *
		 * @return void
		 */
		public function save_list( $post_id ) {
			$meta_key = 'o-list';

			if ( isset( $_POST[ $meta_key ], $_POST['o_list_securite_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['o_list_securite_nonce'] ), 'o-list-security-nonce' ) ) {
				$posted_data = filter_input( INPUT_POST, $meta_key, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
				update_post_meta( $post_id, $meta_key, $posted_data );
			}
		}

		/**
		 * Provide all product lists.
		 *
		 * @return array
		 */
		public static function get_all() {
			global $wpdb;
			$lists_arr = array();
			$results   = $wpdb->get_results(
				$wpdb->prepare(
					"select ID, post_title from $wpdb->posts where post_type='%1s' and post_status='%2s'",
					'o-list',
					'publish'
				)
			);
			foreach ( $results as $result_row ) {
				$product_title = $result_row->post_title;
				if ( empty( $product_title ) ) {
					$product_title = __( 'No title', 'o-list' );
				}
				$lists_arr[ $result_row->ID ] = $product_title;
			}
			return $lists_arr;
		}

		/**
		 * Evaluate Product List Query.
		 *
		 * @return void
		 */
		public function evaluate_query() {
			$posted_data = filter_input( INPUT_POST, 'data', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$msg         = '0' . __( ' result(s) found', 'o-list' );
			if ( isset( $posted_data['o-list'] ) && is_admin() ) {
				$parameters = $posted_data['o-list'];
				$args       = $this->get_args( $parameters );
				$posts      = get_posts( $args );
				$msg        = count( $posts ) . __( ' result(s) found', 'o-list' );
				if ( count( $posts ) ) {
					$msg .= ': (';
					foreach ( $posts as $post ) {
						$msg .= $post->post_title . ', ';
					}
					$length = strlen( $msg );
					$msg    = substr( $msg, 0, $length - 2 );
					$msg   .= ')';
				} else {
					$msg .= '.';
				}
			}
			mb_internal_encoding('UTF-8');
			echo wp_json_encode( array( 'msg' => $msg ), JSON_UNESCAPED_UNICODE );
			die();
		}

		/**
		 * Get Args.
		 *
		 * @param mixed $raw_args Args.
		 *
		 * @return array|string[][]
		 */
		public function get_args( $raw_args = false ) {
			if ( ! $raw_args ) {
				$raw_args = $this->args;
			}

			$args = array(
				'post_type' => array( 'product', 'product_variation' ),
			);
			if ( isset( $raw_args['type'] ) && 'by-id' == $raw_args['type'] ) {
				$args['post__in'] = explode( ',', $raw_args['ids'] );
			} else {
				// Tax queries.
				if ( isset( $raw_args['tax_query']['queries'] ) ) {
					$args['tax_query']             = array();
					$args['tax_query']['relation'] = $raw_args['tax_query']['relation'];
					foreach ( $raw_args['tax_query']['queries'] as $query ) {
						$args['tax_query'][] = $query;
					}
				}

				// Metas.
				if ( isset( $raw_args['meta_query']['queries'] ) ) {
					$args['meta_query']             = array();
					$args['meta_query']['relation'] = $raw_args['meta_query']['relation'];
					foreach ( $raw_args['meta_query']['queries'] as $query ) {
						$array_operators = array( 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' );
						if ( in_array( $query['compare'], $array_operators ) ) {
							$query['value'] = explode( ',', $query['value'] );
						}
						$args['meta_query'][] = $query;
					}
				}

				// Other parameters.
				$other_parameters = array( 'author__in', 'post__not_in' );
				foreach ( $other_parameters as $parameter ) {
					if ( ! isset( $raw_args[ $parameter ] ) ) {
						continue;
					}
					if ( 'post__not_in' == $parameter ) {
						$args[ $parameter ] = explode( ',', $raw_args[ $parameter ] );
					} elseif ( 'author__in' == $parameter && array( '' ) == $raw_args[ $parameter ] ) {
						continue;
					} else {
						$args[ $parameter ] = $raw_args[ $parameter ];
					}
				}
			}
			$args['nopaging'] = true;
			return $args;
		}

		/**
		 * Get Products.
		 *
		 * @param mixed $force_old Retrieve product using old algorithm? .
		 *
		 * @return array
		 */
		public function get_products( $force_old = false ) {
			global $wad_products_lists;
			global $wad_last_products_fetch;

			if ( $force_old ) {
				$use_new_extraction_aglgorithm = false;
			} else {
				$use_new_extraction_aglgorithm = true;
			}

			if (
				$use_new_extraction_aglgorithm // New algorithm mode
				&& isset( $wad_products_lists[ $this->id ] ) // We already retrieved products from this list before
				&& $wad_products_lists[ $this->id ]['last_fetch'] == $wad_last_products_fetch // Our last extraction is the same as what we're need to extract now
			) {
				return $wad_products_lists[ $this->id ]['products']; // We simply return what we already stored without any calculation
			}

			// If there is no product extracted no need to bother applying any discount here because woocommerce is not looping any product
			if ( empty( $wad_last_products_fetch ) && $use_new_extraction_aglgorithm ) {
				return array();
			}

			$products = array();

			// Force old: useful otherwise the free gifts prices are not removed from the total for example
			// or to avoid any issue right after the customer clicks on the place order button

			$args = $this->get_args();
			if ( $use_new_extraction_aglgorithm ) {

				if ( $wad_last_products_fetch && $wad_last_products_fetch != $this->last_fetch ) {
					// We make sure that the values excluded using the list exclude field are not included again in the last fetch
					if ( isset( $args['post__not_in'] ) && ! empty( $args['post__not_in'] ) ) {
						$wad_last_products_fetch = array_diff( $wad_last_products_fetch, array_map( 'intval', $args['post__not_in']  ) );
					}
					if ( isset( $args['post__in'] ) ) {
						array_merge( $args['post__in'], $wad_last_products_fetch );
					} else {
						$args['post__in'] = $wad_last_products_fetch;
					}

					$this->last_fetch = $wad_last_products_fetch;
					$this->products   = false;
				} else {
					$products = $this->products;
				}
			}

			if ( $this->products && ! $force_old ) {
				$products = $this->products;
			} else {
				$products = get_posts( $args );
				if ( ! empty( $products ) ) {
					$to_return = array_map(
						function ( $o ) {
							return $o->ID;
						},
						$products
					);
					// This will make sure the variations are included for the variable products.
					$variations_ids = $this->get_request_variations( $products );
					$this->products = array_merge( $to_return, $variations_ids );
				}
			}

			$wad_products_lists[ $this->id ] = array(
				'last_fetch' => $this->last_fetch,
				'products'   => $this->products,
			);
			return $this->products;
		}

		/**
		 * Check if the request contains any variation.
		 * If it does not, it adds returns all variations linked to the request.
		 *
		 * @param mixed $posts List of posts.
		 *
		 * @return array
		 */
		private function get_request_variations( $posts ) {
			$results    = array();
			$variations = array_filter(
				$posts,
				function ( $e ) {
					return 'product_variation' == $e->post_type;
				}
			);
			if ( empty( $variations ) ) {
				global $wpdb;
				$parents_ids       = array_map(
					function( $o ) {
						$p = wc_get_product( $o->ID );
						if ( $p->get_type() == 'variable' ) {
							return $o->ID;
						}},
					$posts
				);
				$clean_parents_ids = array_map( 'intval', array_filter( $parents_ids ) );
                $str               = rtrim(str_repeat('%d,', count($clean_parents_ids)), ',');
                $parents_ids_str   = implode( ',', $clean_parents_ids );
                if ( ! empty( $parents_ids_str ) ) {
                    $results = $wpdb->get_col(
                        $wpdb->prepare(
                            "select distinct id from $wpdb->posts where post_parent in($str) and post_type=%s", array_merge($clean_parents_ids, ['product_variation'])
                        )
                    );
                }
			}

			return $results;
		}

		/**
		 * Get the max input vars number of php ini file.
		 */
		public function get_max_input_vars_php_ini() {
			$total_max_normal = ini_get( 'max_input_vars' );
			$msg              = 'Your max input var is <strong>' . $total_max_normal . '</strong> but this page contains <strong>{nb}</strong> fields. You may experience a lost of data after saving. In order to fix this issue, please increase <strong>the max_input_vars</strong> value in your php.ini file.';
			?>
		<script type="text/javascript">
			var o_max_input_vars = <?php echo esc_html( $total_max_normal ); ?>;
			var o_max_input_msg = "<?php echo esc_html( $msg ); ?>";
		</script>
			<?php
		}
	}

	$obj = new WAD_Products_List( false );
	$obj->run();
}
