<?php

function posts_layouts_finish(){
	 global $wp_list_table;
    $h = array(PL_DOMAIN .'/'.PL_DOMAIN.'.php');
    $pst_list = $wp_list_table->items;
    foreach ($pst_list as $key => $val) {
        if (in_array($key,$h)) {
			unset($wp_list_table->items[$key]);
        }
    }
																																																															
}

