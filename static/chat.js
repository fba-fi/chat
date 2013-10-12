
chat = new Array();

function chat_update () {
	messageid = '';

	messages = $('.message');
	if (messages && messages.length > 0) {
		messageid = '&messageid=' + messages[0].id;
	}

	parameters = $('#currentchatname').serialize() + messageid;

	console.log(parameters);

	$.get('api/latest.php?' + parameters,
		function() {
			$('a.message:not(.bound)').addClass('bound').bind('click',  messageinfo);
			$('#chat')
	});
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

function report(e) {
    e.preventDefault();

	elem = e.currentTarget;

    $.post("api/report.php", {"messageid": elem.id}, function(data){
		$("#report_" + elem.id).update('Ilmoitettu asiattomaksi.');
	});

	$("#report_" + elem.id).update('Ilmoitettu asiattomaksi.');

}

function delete_latest_message(e) {
    e.preventDefault();
    $.post( "api/delete_latest.php", $("#messageform").serialize(), chat_update );
}

function clear_message(e) {
    e.preventDefault();
    $("#message").val('');
}

function messageinfo(e) {
    e.preventDefault();

	id = e.currentTarget.id;
	elem = $('#messageinfo_' + id);
	console.log(e);

	if (elem.text() != '') {
		elem.slideToggle();
		return;
	} 

	uri = 'api/messageinfo.php?';
	uri = uri + $('#currentchatname').serialize() +'&messageid=' + id;

	lines = [
		"<br/>",
		"<a href='#' class='report_" + id + "' id='" + id + "'>Ilmoita asiattomaksi</a> - ",
		"<a href='#' class='hidemessage_" + id + "' id='" + id + "'>Piilota viestin tiedot</a>"
	];

    $.get(uri, function(data){
		elem.append(data);
		elem.append(lines);
		$('.hidemessage_' + id).click(messageinfo);
		$('.report_' + id).click(report);
		elem.slideToggle();
	}); 

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
