<?php

if ( ! defined('THEMIFY_UPDATER_MENU_PAGE') ) die();

$options = get_option('themify_updater_licence', '');
$username = '';
$key = '';
$hideKey = false;
$hideNotice = false;
if ( !empty($options) ) {
    $options = json_decode( $options,true);
    if ( is_array($options) ) {
        $username = $options['username'];
        $key = $options['key'];
        $hideKey = isset($options['hideKey']) ? $options['hideKey'] : false;
        $hideNotice = isset($options['hideNotice']) ? $options['hideNotice'] : false;
    }
}

if ($hideKey) {
    $key = preg_replace("/[0-9a-zA-Z]/", "*", $key);
}

require (THEMIFY_UPDATER_DIR_PATH.'/templates/admin_menu.php');