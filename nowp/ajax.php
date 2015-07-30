<?php

/**
* test for cookie, must have expected name and value
*/
$plugin_path = dirname(dirname(__FILE__)) . '/';

// some system data to salt with
$data = sprintf("%s\n%s\n%s\n%s", php_uname(), php_ini_loaded_file(), php_ini_scanned_files(), implode("\n", get_loaded_extensions()));

// synthesise a temporary cookie name using server name, file path, and time
// NB: only needs to be as complex/secure as the data that could be exposed, i.e. the contents of $_SERVER and script paths
$tick = ceil(time() / 120);
$cookie_name = 'sslfix_' . md5(sprintf('%s|%s|%s', $_SERVER['SERVER_NAME'], $plugin_path, $tick));
$cookie_value = md5($data);

if (!isset($_COOKIE[$cookie_name])) {
	echo 'missing nonce.';
	exit(403);
}

if ($_COOKIE[$cookie_name] !== $cookie_value) {
	echo 'bad nonce value.';
	exit(403);
}

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

	// build a list of environment variables to omit, as keys
	// some are just unnecessary, some might expose sensitive information like script paths
	$env_blacklist = array_flip(array(
		'AUTH_TYPE',
		'CONTENT_LENGTH',
		'CONTENT_TYPE',
		'CONTEXT_DOCUMENT_ROOT',
		'CONTEXT_PREFIX',
		'DOCUMENT_ROOT',
		'DOCUMENT_URI',
		'FCGI_ROLE',
		'GATEWAY_INTERFACE',
		'HOME',
		'HTTP_ACCEPT',
		'HTTP_ACCEPT_CHARSET',
		'HTTP_ACCEPT_ENCODING',
		'HTTP_ACCEPT_LANGUAGE',
		'HTTP_CONNECTION',
		'HTTP_COOKIE',
		'HTTP_HOST',
		'HTTP_ORIGIN',
		'HTTP_REFERER',
		'HTTP_USER_AGENT',
		'ORIG_PATH_INFO',
		'PATH',
		'PATH_INFO',
		'PATH_TRANSLATED',
		'PHP_AUTH_DIGEST',
		'PHP_AUTH_PW',
		'PHP_AUTH_USER',
		'PHP_SELF',
		'QUERY_STRING',
		'REDIRECT_REMOTE_USER',
		'REDIRECT_STATUS',
		'REMOTE_ADDR',
		'REMOTE_PORT',
		'REMOTE_USER',
		'REQUEST_METHOD',
		'REQUEST_TIME',
		'REQUEST_TIME_FLOAT',
		'REQUEST_URI',
		'SCRIPT_FILENAME',
		'SCRIPT_NAME',
		'SERVER_ADDR',
		'SERVER_ADMIN',
		'SERVER_NAME',
		'SERVER_PORT',
		'SERVER_PROTOCOL',
		'SERVER_SIGNATURE',
		'SERVER_SOFTWARE',
		'UNIQUE_ID',
		'USER',
	));

	// build server environment to return, without blacklisted keys
	$env = array_diff_key($_SERVER, $env_blacklist);

	$response['env'] = print_r($env, 1);

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

	// add CORS headers so that browsers permit JSON response
	@header('Access-Control-Allow-Credentials: true');
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		@header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
	}

	echo json_encode($response);
	exit;
}
