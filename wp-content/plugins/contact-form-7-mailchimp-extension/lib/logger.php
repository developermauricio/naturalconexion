<?php
/*  Copyright 2010-2023 Renzo Johnson (email: renzo.johnson at gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/



class chimp_db_log {

  // Atributos
  public $form;
  public $debug_enabled;
  public $category ;
  public $idform ;

	public function __construct( $form, $debug_enabled, $category, $idform = ''   ) {

		$this->form = $form;
		$this->debug_enabled = $debug_enabled ;
		$this->category = $category ;
		$this->idform = $idform ;

	}



	public function chimp_log_insert_db ( $mstype, $content, $object ) {

		if ( !$this->debug_enabled ) return ;

		$form = $this->form  ;

		$default = array() ;
		$master_error = get_option ( $form.'_log', $default ) ;
		$master_error = is_null ( $master_error ) ? array() :  $master_error ;
		$master_error = is_array( $master_error ) ? $master_error : array() ;

		//error_log ( print_r ( $master_error,true ) ) ;

		$maxarray = count ( $master_error  ) + 1 ;
		//error_log ( print_r ( 'cuenta :' . $maxarray  ) ) ;

		$serror_log  = array(
				'id'  => uniqid(),
				'idform' => $this->idform,
				'category' => $this->category,
				'mstype' => $mstype,
				'date' => getdate(),
				'datetxt' => date('d-M-y H:i:s'),
				'dateonly' => date('d-M-y'),
				'timestamp' => current_time ('timestamp'),
				'content' => $content,
				'object' => $object
				) ;

		if ( $maxarray == 1 )
			$master_error = array( $maxarray => $serror_log ) ;
		else
			$master_error = $master_error + array( $maxarray => $serror_log ) ;

		if ( !get_option ( $form.'_log' ) ) {

			//	error_log ( 'Entra al log Add'  ) ;
			$deprecated = null;
			$autoload = 'no';
			add_option( $form.'_log', $master_error, $deprecated, $autoload );

		} else {

		//	error_log ( 'Entra al log Up'  ) ;
			update_option ( $form.'_log' , $master_error  ) ;

		}
		//error_log ( print_r ( $master_error,true )  ) ;

		$master_save = get_option ( $form.'_log' ) ;

	}



	public function chimp_log_delete_db () {

		$form = $this->form.'_log'   ;
		//error_log ( $form  ) ;
		$result = delete_option ( $form ) ;
		return $result ;
	}

}