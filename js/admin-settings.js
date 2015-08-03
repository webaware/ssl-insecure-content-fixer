/*!
SSL Insecure Content Fixer admin settings script
https://ssl.webaware.net.au/
*/

(function($) {

	$.ajax({
		url:		sslfix.ajax_url_ssl,
		cache:		false,
		data:		{ action: "sslfix-get-recommended" },
		dataType:	"json",
		method:		"GET",
		xhrFields:	{ withCredentials: true },
		success:	showRecommended
	});

	/**
	* show recommended settings
	* @param {Object} response
	*/
	function showRecommended(response) {
		if (response.recommended) {
			var label = $("label[for=" + response.recommended_element + "]");
			label.addClass("sslfix-recommended");
			label.html(label.html() + "<br/><span>" + sslfix.msg.recommended + "</span>");
		}
	}

})(jQuery);
