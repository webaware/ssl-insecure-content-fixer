<?php
// attempt to diagnose why SSL cannot be detected by WordPress

/**
* a copy if the is_ssl() function from WordPress, wp-includes/functions.php
*/
function is_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}
?>
<!DOCTYPE html>
<html lang="en-au">
<head>
<meta charset="utf-8" />
<title>SSL Insecure Content Fixer - is_ssl() test</title>
</head>

<body>
	<h1>SSL Insecure Content Fixer - is_ssl() test</h1>

	<p>This page checks to see whether WordPress can even test for SSL. If it can't, something else needs fixing.</p>

	<p>is_ssl() says: <strong><?php echo is_ssl() ? 'yes, SSL detected' : 'no, SSL not detected' ?></strong></p>

	<?php if (!is_ssl()): ?>
		<?php if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'): ?>
		<p><strong>Your server is behind a load balancer or reverse proxy.</strong></p>
		<p>Please add the following code to your wp-config.php file, above the require_once:</p>
		<pre>
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    $_SERVER['HTTPS']='on';
		</pre>
		<?php else: ?>
		<p><strong>Your server may be behind a load balancer or reverse proxy.</strong></p>
		<p>Please ask your web hosting provider why your website can't detect SSL, and show them this article:</p>
		<p><a href="http://snippets.webaware.com.au/snippets/wordpress-is_ssl-doesnt-work-behind-some-load-balancers/">WordPress is_ssl() doesn’t work behind some load balancers</a></p>
		<?php endif; ?>
	<?php endif; ?>

<script>
if (document.location.protocol != "https:") {
	var msg = "\
This page wasn't loaded via SSL (HTTPS).\n\
Attempt to reload with SSL?\n\
(if this message shows again, something is forcing your browser to load the page via HTTP)\
";
	if (confirm(msg)) {
	    document.location = document.URL.replace(/^http:/i, "https:");
	}
}
</script>

</body>

</html>

