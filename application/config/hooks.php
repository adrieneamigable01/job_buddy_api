<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
// $hook['pre_controller'][] = array(
//     'class'    => 'Jwt_auth_hook',
//     'function' => 'check_token',
//     'filename' => 'Jwt_auth_hook.php',
//     'filepath' => 'hooks'
// );

$hook['post_controller'][] = array(
    'class'    => 'Cors',
    'function' => 'set_cors_headers',
    'filename' => 'Cors.php',
    'filepath' => 'hooks'
);