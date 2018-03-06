(function($) {
	
var fb_app_id = $("#fb_app_id").val();
var fb_app_secret = $("#fb_app_secret").val();
var gUrl = "https://graph.facebook.com/";
var query = "?access_token=" + fpe_token;
var fields = "&fields=" + fpe_fields; 

info_clear();

$("#fpa_add_events").click(function() {
	var ids = $("#quick_add_ids").val().split("\n");
	var err = "";
	info_clear();

	$.each(ids, function(key, id) {
		if(isNumeric(id)) {

			$.ajax({
			    url: (gUrl + id + query + fields),
			    success: function(data, status) {
			    	var d = data;
					$.post({
					    url: ajax_object.ajax_url,
					    data: {
					    	"event_actions" : true,
					    	"action_type" : "add",
					    	"data" : d,
					    	"token" : fpe_token
					    },
					    success: function(data, status) {

					    	ajax_response(data);

						},
						error: function(data, e1, e2) {
							
							ajax_response(data, true);

						}
					});
				},

				error: function(data, e1, e2) {
					console.log(data, e1, e2);
					ajax_response("<span class='error'>Could not pull Facebook data, please check ID(s), Is this an Event ID?</span>");
				}
			});

		} else {

			err += id + " is not numeric\n";

		}
	});


	ajax_response('<span class="error">' + err + '</span>');

	return false;
});

$("#fpa_test_fb_ids").click(function() {

	var btn = $(this);
	btn.addClass("fpe-loading");
	var ids = $("#facebook_page_ids").val().split("\n");
	valid_ids = '';
	idCallsMade = 0;
	totalIdCalls = ids.length;
	info_clear();

	$.each(ids, function(key, id) {

		currentId = id;

		$.ajax({
		    url: (gUrl + id + query),
		    success: function(data, status) {

		    	var name = data.name;
		    	ajax_response(id + ' "' + name + '" <span class="info">is valid</span><br>', true);

			},

			error: function(data, e1, e2) {
				var response = data.responseText;
				if( response.indexOf("Cannot query users by their username") > -1 ) {

					ajax_response(id + ' <span class="error">is a user not a page</span><br>', true);

				} else if( response.indexOf("aliases you requested do not exist") > -1 ) {

					ajax_response(id + ' <span class="error">does not exist</span><br>', true);

				} else {

					ajax_response(id + ' <span class="error">caused an unknown error, see console for more details</span>', true);
					console.log(data);

				}
			},
			complete: function() {

				btn.removeClass("fpe-loading");

			}
		});

		

	});

	return false;
});

$("#fpa_mimic_cron").click(function() {

	var btn = $(this);
	btn.addClass("fpe-loading");
	info_clear();

	$.post({
	    url: ajax_object.ajax_url,
	    data: {
	    	"event_actions" : true,
	    	"action_type" : "cron",
	    	"token" : fpe_token
	    },
	    success: function(data, status) {

	    	ajax_response(data);

		},
		error: function(data, e1, e2) {

			ajax_response(data, true);

		},
		complete: function() {

			btn.removeClass("fpe-loading");

		}
	});
	return false;
});

$("#fpa_mimic_cron_ovs").click(function() {

	var btn = $(this);
	btn.addClass("fpe-loading");
	info_clear();

	$.post({
	    url: ajax_object.ajax_url,
	    data: {
	    	"event_actions" : true,
	    	"action_type" : "cron",
	    	"ovs" : true,
	    	"token" : fpe_token
	    },
	    success: function(data, status) {
	    	
	    	ajax_response(data);

		},
		error: function(data, e1, e2) {

			ajax_response(data, true);
			
		},
		complete: function() {

			btn.removeClass("fpe-loading");

		}
	});
	return false;
});

function ajax_response(data, error = false) {

	if( data.responseText ) {

		text = data.responseText;

	} else if ( data.statusText ) {

		text = data.statusText;

	} else {

		text = data;

	}
	
	text = text.replace("0", ""); // fixme
	display_info(text);
	console.log(data);

}

function isNumeric(n) {
	return !isNaN(parseFloat(n)) && isFinite(n);
}

function display_info(text, test = false) {
	var elem = test ? $(".fpe-info.test") : $(".fpe-info:last-child");
	elem.show();
	var html = ( elem.html() != 'undefined' ) ? elem.html() : '';
	elem.html(html + text);
}

function info_clear() {
	
	$(".fpe-info").html("").hide();

}
	
})( jQuery );
