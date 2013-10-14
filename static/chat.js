
chat = new Array();

function chat_update () {
	messageid = '';

	messages = $('.message');
	if (messages && messages.length > 0) {
		messageid = '&messageid=' + messages[0].id;
	}

	parameters = $('#currentchatname').serialize() + messageid;

	$.get('api/latest_json.php?' + parameters,
		function(data) {
			messages = $.parseJSON(data);
			addMessages(messages);
			$('a.message:not(.bound)').addClass('bound').bind('click',  toggle_message_info);
	});
}

function addMessages(messages) {
	chat = $('#chat');

	for (var i=0; i < messages.length; i++) {
		message = messages[i];

		div_id = 'mdiv_' + message.message_id;
		visible_messages = $('#' + div_id);

		if (visible_messages.length > 0) {
			return;
		}

		div = $('<div/>').attr("id", div_id);

		link = $('<a href="#"/>').attr("id", message.message_id);
		link.addClass("message");
		link.append(message.timestamp + ' ');
		link.append(message.username);

		messageinfo = $('<div style="display:none"></div>').attr('id', "messageinfo_" + message.message_id);
		messageinfo.addClass("messageinfo");

		div.append(link);
		div.append('<strong>:</strong> ');
		div.append(message.text);

		div.append(messageinfo);

		chat.prepend(div);

	}
}

function timed_chat_update () {
    if ( $('#refreshbutton').attr("checked") || (  $('#refreshbutton').length == 0 )) {
		chat_update();
    }
}

function send_message(e) {
    e.preventDefault();
    $.post( "api/send.php", $("#messageform").serialize(), chat_update );
    $("#message").val('');
}

function reportMessage(e) {
    e.preventDefault();

	elem = e.currentTarget;

	console.log(e);
	console.log(elem.id);

	uri = 'api/report.php?';
	uri = uri + $('#currentchatname').serialize() +'&messageid=' + id;

    $.post(uri, { "chatname": $('#currentchatname').val(),
				  "messageid": elem.id
				}, function(data){
					$(elem).replaceWith('<span class="reportlink">Ilmoitettu</span>');
				});

}

function delete_latest_message(e) {
    e.preventDefault();
    $.post( "api/delete_latest.php", $("#messageform").serialize(), chat_update );
}

function clear_message(e) {
    e.preventDefault();
    $("#message").val('');
}

function toggle_message_info(e) {
    e.preventDefault();

	id = e.currentTarget.id;
	elem = $('#messageinfo_' + id);

	if (elem.text() != '') {
		elem.slideToggle();
		return;
	} 

	uri = 'api/messageinfo.php?';
	uri = uri + $('#currentchatname').serialize() +'&messageid=' + id;

    $.get(uri, function(data){
		info = $.parseJSON(data);
		appendMessageInfo(elem, info);
		elem.slideToggle();
	}); 

}

function appendMessageInfo(elem, data) {

	console.log(data);

	deletelink ='';
	deletesep = '';
	if (data.can_delete) {
		deletelink = $('<a href="#"/>').append("Poista viesti");
		deletelink.addClass("deletelink");
		deletelink.attr("id", data.message_id);
		deletesep = ' - ';
	}

	reportlink = '';
	reportsep = '';
	if (!data.reported_by_user) {
		reportlink = $('<a href="#"/>').append("Ilmoita asiattomaksi");
		reportlink.attr("id", data.message_id);
		reportlink.addClass("reportlink");
		reportsep = ' - ';
	}

	hidelink = $('<a href="#"/>').append("Piilota viesti tiedot");
	hidelink.addClass("hidelink");
	hidelink.attr("id", data.message_id);

	reportcount = '';
	if (data.report_count>0) {
		reportcount = $('<div/>');
		reportcount.append("Viesti raportoitu " + data.report_count + " kertaa.");
	}

	lines = [
		"<div>Nimimerkit samasta IP-osoitteesta: " +
		data.nicknames.join(", "),
		"</div>",
		deletelink,
	   	deletesep,
		reportlink,
		reportsep,
		hidelink,
		reportcount
	];

	elem.append(lines);

	$('a.hidelink:not(.bound)').addClass('bound').bind('click',  toggle_message_info);
	$('a.reportlink:not(.bound)').addClass('bound').bind('click',  reportMessage);
}


function chat_init() {
    chat_update();
    chat.interval = self.setInterval(timed_chat_update, 5000);

    $("#sendmessage").click(send_message);
    $("#deletelatest").click(delete_latest_message);
    $("#clearmessage").click(clear_message);

    // Workaround to keep newlines in textarea
    // http://api.jquery.com/val/
    $.valHooks.textarea = {
        get: function( elem ) {
            return elem.value.replace( /\r?\n/g, "\r\n" );
        }
    };
}

$(document).ready(chat_init);


