/*!
SSL Insecure Content Fixer admin tests script
https://ssl.webaware.net.au/
*/

(function($) {

	$.ajax({
		url:		sslfix.ajax_url_ssl,
		cache:		false,
		data:		{ action: "sslfix-environment" },
		dataType:	"json",
		method:		"GET",
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
			$("#sslfix-detect_fail").show().find("pre").text(response.server);
		}

		$("#sslfix-test-result-head").show();
		$("#sslfix-loading").hide();
	}

})(jQuery);
