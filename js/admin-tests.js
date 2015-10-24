/*!
SSL Insecure Content Fixer admin tests script
https://ssl.webaware.net.au/
*/

(function($) {

	$.ajax({
		url:		sslfix.ajax_url_ssl,
		data:		{ action: "sslfix-environment" },
		dataType:	"json",
		method:		"GET",
		xhrFields:	{ withCredentials: true },
		error:		showError,
		success:	showResults
	});

	/**
	* show test results
	* @param {Object} response
	*/
	function showResults(response) {
		if (response.ssl) {
			switch (response.detect) {

				case "HTTPS":
				case "port":
					$("#sslfix-normal").show();
					break;

				case "HTTP_X_FORWARDED_PROTO":
					$("#sslfix-HTTP_X_FORWARDED_PROTO").show();
					break;

				case "HTTP_X_FORWARDED_SSL":
					$("#sslfix-HTTP_X_FORWARDED_SSL").show();
					break;

				case "HTTP_CF_VISITOR":
					$("#sslfix-HTTP_CF_VISITOR").show();
					break;

			}
		}
		else {
			$("#sslfix-detect_fail").show();
		}

		$("#sslfix-test-result-head").show();
		$("#sslfix-loading").hide();
		$("#sslfix-environment").show().find("pre").text(response.env);
	}

	/**
	* show test error
	* @param {Object} xhr
	* @param {String} status
	* @param {String} errmsg
	*/
	function showError(xhr, status, errmsg) {
		$("#sslfix-test-result-head").show();
		$("#sslfix-loading").hide();
		$("#sslfix-environment").show().find("pre").text(status + "\n" + errmsg);
	}

	$.ajax({
		url:		sslfix.ajax_url_wp,
		data:		{ action: "sslfix-test-https" },
		dataType:	"json",
		method:		"GET",
		xhrFields:	{ withCredentials: true },
		success:	showHttpsDetected
	});

	/**
	* show whether HTTPS was detected correctly within WordPress
	* @param {Object} response
	*/
	function showHttpsDetected(response) {
		if (response.https) {
			$("#sslfix-https-detection").addClass("dashicons dashicons-" + response.https);
		}
	}

})(jQuery);
