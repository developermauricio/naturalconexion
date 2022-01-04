<?php

/*
 * WSS Data migration
 * 
 * This class is used to migrate data from old WSS's shipping zones system to the WooCommerce's native system 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Data migration class
 */

if ( !class_exists( 'WSS_Data_Migration' ) ) {
	
	class WSS_Data_Migration{

		/**
	 	* Data to migrate.
	 	*
	 	* @var array
	 	*/
		protected $migrate_data = array();

		/**
	 	* Stored shipping settings data.
	 	*
	 	* @var array
	 	*/
		protected $old_settings = array();

		public function __construct( ) {
			
			$shipping_zones_stored_data = get_option( 'be_woocommerce_shipping_zones', array() );
			$general_settings 			= get_option( 'woocommerce_super_shipping_settings', array() );
			$tables_rates 				= get_option( 'woocommerce_special_increase_rate', array() );
			$extra_fees 				= get_option( 'super_shipping_extra_fees', array() );
			$classes_priority 			= get_option( 'super_shipping_classes_priority', array() );
			$this->old_settings 		= array( 'shipping_zones' 	=> $shipping_zones_stored_data, 
											'general_settings' 		=> $general_settings,
											'tables_rates' 			=> $tables_rates, 
											'extra_fees'			=> $extra_fees, 
											'classes_priority' 		=> $classes_priority );

			if ( $shipping_zones_stored_data ) {

				$this->preformatted_shipping_zones_and_tables_rates( $this->old_settings );
				$this->migrate_shipping_zones_and_tables_rates();
				$this->clean_old_data();
			}			
		}

		public function clean_old_data(){

			// Delete old settings from the data base
			delete_option( 'be_woocommerce_shipping_zones' );
			delete_option( 'woocommerce_super_shipping_settings' );
			delete_option( 'woocommerce_special_increase_rate' ); 
			delete_option( 'super_shipping_extra_fees' );
			delete_option( 'super_shipping_classes_priority' );
		}

		public function create_and_save_data_backup(){

			$json_migrate_data = json_encode( $this->old_settings );
			$uploads_dir = wp_upload_dir();
			$migrate_date_file = fopen( $uploads_dir[ 'basedir' ] .'/wss-migration-backup.json', 'w' );
			fwrite( $migrate_date_file, $json_migrate_data );
			fclose( $migrate_date_file );
		}

		public function migrate_shipping_zones_and_tables_rates(){

			//Check if there is shipping zones to migrate
			if ( !empty( $this->migrate_data ) ) {

				if ( 'yes' === get_transient( 'wss_create_backup' ) ) {
					
					$this->create_and_save_data_backup();
				}

				foreach ( $this->migrate_data as $zone ) {
					
					$new_shipping_zone = new WC_Shipping_Zone(); 
					$new_shipping_zone->set_zone_name( $zone[ 'zone_name' ] );
					$new_shipping_zone->set_zone_order( $zone[ 'zone_order' ] );
					$new_shipping_zone->set_zone_locations( $zone[ 'zone_location' ] );

					// Adding shipping methods only to the zones which are not excluding
					foreach ( $zone[ 'tables_rates' ] as $table_rate ) {

						if ( 'shipping_rate' === $table_rate[ 'type_of_table' ] ) {

							$shipping_method_instance_id = $new_shipping_zone->add_shipping_method( 'super_shipping' );

							// Cleaning the structure of array
							unset( $table_rate[ 'prices_table_name' ] );
							unset( $table_rate[ 'shipping_zone' ] );
							unset( $table_rate[ 'type_of_table' ] );
							
							// Save the shipping method settings
							update_option( 'woocommerce_super_shipping_'. $shipping_method_instance_id .'_settings', $table_rate );								
						} else {

							$shipping_method_instance_id = $new_shipping_zone->add_shipping_method( 'local_pickup' );
						}
					}

					$new_shipping_zone->save();
				}
			}

			delete_transient( 'wss_activation_redirect' );
		}

		private function preformatted_shipping_zones_and_tables_rates( $data_stored ){
			$count = 0;

			foreach ( $data_stored[ 'shipping_zones' ] as $zone ) {

				// Check if zone has some locations excluded in order to create a new zone exception
				if ( !empty( $zone[ 'zone_except' ][ 'states' ] ) ) {

					$this->migrate_data[ $count ] = $this->create_shipping_zones_data( array( 
												'zone_title'	=> '',
												'zone_country'	=> $zone[ 'zone_except' ][ 'states' ]
												),
												'state', $count );
					$count++;
				}

				if ( !empty( $zone[ 'zone_except' ][ 'postals' ] ) ) {

					$this->migrate_data[ $count ] = $this->create_shipping_zones_data( array( 
												'zone_title'	=> '',
												'zone_country'	=> $zone[ 'zone_except' ][ 'postals' ]
												),
												'postal', $count );
					$count++;
				}

				$this->migrate_data[ $count ] = $this->create_shipping_zones_data( $zone, $zone[ 'zone_type' ], $count, $zone[ 'zone_id' ] );
				$count++; 
			}

			// Merge duplicate zones with the exactly same locations
			$this->merge_duplicate_zones();

			foreach ( $data_stored[ 'tables_rates' ] as $key => $table_rate ) {
				
				if ( array_search( $table_rate[ 'shipping_zone' ], array_column( $this->migrate_data, 'zone_id' ) ) >= 0 ) {

					// Set the correct keys
					$new_table_rate = array(
						'title' 			 => $table_rate[ 'table_name' ],
						'tax_status'			 => $data_stored[ 'general_settings' ][ 'tax_status' ],
						'hide_free_shipping'		 => $data_stored[ 'general_settings' ][ 'hide_free_shipping' ],
						'show_all_free_shipping_methods' => $data_stored[ 'general_settings' ][ 'show_all_free_shipping_methods' ],
						'volumetric_weight_measure'	 => $data_stored[ 'general_settings' ][ 'volumetric_weight_measure' ],
						'volumetric_weight_factor'	 => $data_stored[ 'general_settings' ][ 'volumetric_weight_measure' ]
					) + $table_rate;
					unset( $new_table_rate[ 'table_name' ] );
					$new_table_rate[ 'shipping_rules' ] = $new_table_rate[ 'taxes' ];
					unset( $new_table_rate[ 'taxes' ] );

					// Set the shipping classes priority list to the shipping method
					$new_table_rate[ 'shipping_classes_priority_table' ] = array();
					if ( ( 'class' === $new_table_rate[ 'calculation_type' ] ) && $new_table_rate[ 'shipping_class_priority' ] ) {
						
						$new_table_rate[ 'shipping_classes_priority_table' ] = $data_stored[ 'classes_priority' ];
					}

					// Set the extra fees to the shipping method
					$new_table_rate[ 'shipping_extra_fees_table' ] = array();
					foreach ( $data_stored[ 'extra_fees' ] as $value ) {
						
						if ( $key == $value[ 'shipping_table' ] ) {
							
							$new_table_rate[ 'shipping_extra_fees_table' ][] = array( 'label' => $value[ 'label' ], 'amount' => $value[ 'amount' ] );
						}
					}

					$this->migrate_data[ $table_rate[ 'shipping_zone' ] ][ 'tables_rates' ][] = $new_table_rate;
				}
			}
		}

		private function create_shipping_zones_data( $zone, $zone_type, $order, $zone_id = -1 ){
			$preformatted_zone = array( 'zone_name' => '', 'zone_order' => -1, 'zone_id' => $zone_id, 'zone_location' => array(),  'tables_rates' => array() );

			$preformatted_zone[ 'zone_name' ] = !empty( $zone[ 'zone_title' ] )? $zone[ 'zone_title' ] : 'Zona '. $order;
			$preformatted_zone[ 'zone_order' ] = $order;
			$states_codes = explode( ',', $zone[ 'zone_country' ] );

			foreach ( $states_codes as $state ) {
				
				$data = new stdClass();
				//Get the type of shipping zone
				if ( strpos( $state, ':') ) {

					// It's a state
					$data->code = $state;
					$data->type = 'state';
				} elseif ( preg_match( '/[0-9]/', $state ) ) {

					// It's a postcode
					$data->code = str_replace( '-', '..', $state );
					$data->type = 'postcode';
				} else {

					// It's a country
					$data->code = $state;
					$data->type = 'country';
				}

				array_push( $preformatted_zone[ 'zone_location' ], $data );
			}

			return $preformatted_zone;
		}

		public function delete_all_shipping_zones(){

			$shipping_zones = WC_Shipping_Zones::get_zones();
			foreach ( $shipping_zones as $ID => $zone_data ) {
				
				WC_Shipping_Zones::delete_zone( $ID );
			}
		}

		private function merge_duplicate_zones(){

			for ( $key = 0; $key < count( $this->migrate_data ); $key++ ) {
				
				$zone_location = $this->migrate_data[ $key ][ 'zone_location' ];

				for ( $zone_id = 0; $zone_id < count( $this->migrate_data ); $zone_id++ ) {
					
					if ( ( $key != $zone_id ) && ( count( $this->migrate_data[ $zone_id ][ 'zone_location' ] ) == count( $zone_location ) ) ) {
						
						$count = 0;
						$matching_level = 0;
						foreach ( $this->migrate_data[ $zone_id ][ 'zone_location' ] as $location_object ) {
							
							if ( ( $location_object->type === $zone_location[ $count ]->type ) && ( $location_object->code === $zone_location[ $count ]->code ) ) {
								
								$matching_level++;
							}

							$count++;
						}

						// Merging zones
						if ( count( $this->migrate_data[ $zone_id ][ 'zone_location' ] ) == $matching_level ) {
							
							$this->migrate_data[ $key ][ 'zone_id' ] = $this->migrate_data[ $key ][ 'zone_id' ] >= 0? $this->migrate_data[ $key ][ 'zone_id' ] : $this->migrate_data[ $zone_id ][ 'zone_id' ];
							unset( $this->migrate_data[ $zone_id ] );
						}
					}
				}
			}
		}
	}
}
