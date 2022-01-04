<?php
/**
 * Created by PhpStorm.
 * User: v0id
 * Date: 23.02.19
 * Time: 12:42
 */

class wpWoofTools
{

    static function convertToJSStringArray($str,$needle = ','){


        if(empty($needle)) $needle=',';
        $preselect = '';
        if (strpos($str, $needle) !== false ) {
            $aData = explode(',', $str);

            if(!empty($aData) && is_array($aData)){
                foreach ($aData as $elm) {
                    $preselect .= "'".$elm."', ";
                }
                $preselect = rtrim($preselect, ', ');
            }
        } else {
            $preselect = "'$str'";
        }
        return $preselect;
    }

}



