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



function generateHeader($token) {

    $dc = explode("-", $token);
    $vc_date = date('d-M-Y.H:i:s');
    $vc_user_agent = '.' . SPARTAN_MCE_VERSION . '.' . $vc_date;
    $vc_headers = ["Content-Type" => "application/json", "Authorization" => "Bearer $dc[0]"];
    $opts = ['headers' => $vc_headers, 'user-agent' => 'mce-r' . $vc_user_agent,'timeout'     => 15000];


    return $opts;
}



function callApiGet($token, $url) {

    $header = generateHeader($token);
    $mergerfield = wp_remote_get($url, $header);
    $resultbody = wp_remote_retrieve_body($mergerfield);
    return [json_decode($resultbody, true),$header,$mergerfield];
}



function callApiGetWithoutToken($url) {

    $mergerfield = wp_remote_get($url);
    $resultbody = wp_remote_retrieve_body($mergerfield);
    return [json_decode($resultbody, true),$mergerfield];
}



function callApiPost($token, $url, $body) {

    $header = generateHeader($token);
    $body = array('body' => $body);
    $data = $header + $body;
    $resptres = wp_remote_post($url, $data);
    $response = wp_remote_retrieve_body($resptres);
    return [json_decode($response, true),$resptres];
}



function callApiPatch($token, $url, $body) {

    $header = generateHeader($token);
    $body = array('body' => $body);
    $patch = array('method' => 'PATCH');
    $data = $header + $body+$patch;
    $resptres = wp_remote_post($url, $data);
    $response = wp_remote_retrieve_body($resptres);
    return [json_decode($response, true),$resptres];
}