<?php

/**
* run some AJAX functions outside of WordPress, so that we can see the raw environment
*/

if (isset($_GET['action'])) {
	switch ($_GET['action']) {

		case 'sslfix-get-recommended':
			sslfix_get_recommended();
			break;

		case 'sslfix-environment':
			sslfix_environment();
			break;

		default:
			exit(404);

	}
}

/**
* test environment and recommend settings
*/
function sslfix_get_recommended() {
	$env = sslfix_get_environment();

	$response = array();

	switch ($env['detect']) {

		case 'HTTPS':
		case 'port':
			$response['recommended'] = 'normal';
			break;

		case 'HTTP_X_FORWARDED_PROTO':
			$response['recommended'] = 'HTTP_X_FORWARDED_PROTO';
			break;

		case 'HTTP_X_FORWARDED_SSL':
			$response['recommended'] = 'HTTP_X_FORWARDED_SSL';
			break;

		case 'HTTP_CF_VISITOR':
			$response['recommended'] = 'HTTP_CF_VISITOR';
			break;

		default:
			$response['recommended'] = 'detect_fail';
			break;

	}

	$response['recommended_element'] = 'proxy_fix_' . $response['recommended'];

	sslfix_send_json($response);
}

/**
* test environment to see what can be detected
*/
function sslfix_environment() {
	$response = sslfix_get_environment();
	$response['server'] = print_r($_SERVER, 1);

	sslfix_send_json($response);
}

/**
* test environment to see what can be detected
*/
function sslfix_get_environment() {
	$env = array(
		'ssl' => false,
	);

	if (isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] === '1')) {
		$env['detect'] = 'HTTPS';
		$env['ssl'] = true;
	}
	elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] === '443')) {
		$env['detect'] = 'port';
		$env['ssl'] = true;
	}
	elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
		$env['detect'] = 'HTTP_X_FORWARDED_PROTO';
		$env['ssl'] = true;
	}
	elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
		$env['detect'] = 'HTTP_X_FORWARDED_SSL';
		$env['ssl'] = true;
	}
	elseif (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false) {
		$env['detect'] = 'HTTP_CF_VISITOR';
		$env['ssl'] = true;
	}
	else {
		$env['detect'] = 'fail';
		$env['ssl'] = false;
	}

	return $env;
}

/**
* send JSON response and terminate
* @param array $response
*/
function sslfix_send_json($response) {
	@header('Content-Type: application/json; charset=UTF-8');
	@header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
	@header('Cache-Control: no-cache, must-revalidate, max-age=0');
	@header('Pragma: no-cache');

	// add CORS header so that browsers permit JSON response
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		@header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	}

	echo json_encode($response);
	exit;
}
