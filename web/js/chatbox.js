(function($) {
	$.fn.chatbox = function(options) {
		var opts = $.extend( {}, $.fn.chatbox.defaults, options );

		function refresh() {
			$.ajax({
				url: opts['chatboxFeedUrl'],
				dataType: "json",
				success: function (data) {
					render(data);
				}
			});
		}
		
		function createAvatarPath(av) {
			if(!av) {
				return opts['avatars']+'/default.gif';
			}
			var fst = av.substring(0, 2);
			var snd = av.substring(2, 4);
			return opts['avatars']+'/32/'+fst+'/'+snd+'/'+av; 
		}
		
		function render(data) {
			chatboxBody = $(opts['chatboxBody']);
			chatboxBody.empty();
			$(opts['chatboxSummary']).text(data.messageNum);
			for (i in data.messages) {
				var msg = $('<div/>', {'class': data.messages[i]['dir'] ? 'direct-chat-msg right' : 'direct-chat-msg'});
				var msgInfo = $('<div/>', {'class': 'direct-chat-info clearfix'});
				if (data.messages[i]['dir']) {
					var author = $('<span/>', {'class': 'direct-chat-name pull-right'}).append(data.messages[i]['author']);
					var time = $('<span/>', {'class': 'direct-chat-timestamp pull-left'}).append(data.messages[i]['time']);
				} else {
					var author = $('<span/>', {'class': 'direct-chat-name pull-left'}).append(data.messages[i]['author']);
					var time = $('<span/>', {'class': 'direct-chat-timestamp pull-right'}).append(data.messages[i]['time']);
				}
				var avatar = $('<img/>', {'class': 'direct-chat-img', 'src': createAvatarPath(data.messages[i]['avatar']), 'alt': data.messages[i]['author']});
				var body = $('<div/>', {'class': 'direct-chat-text'}).append(data.messages[i]['message']);
				
				msgInfo.append(author).append(time);
				msg.append(msgInfo).append(avatar).append(body);
				chatboxBody.append(msg);
			}
		}
		$(opts['chatboxForm']).submit(function(event) {
			$.ajax({
				url: opts['chatboxPostUrl'],
				method: 'post',
				dataType: "json",
				data: {
					'message': $(opts['chatboxForm']).find('input[name="message"]').val()
				},
				success: function (data) {
					$(opts['chatboxForm']).find('input[name="message"]').val('');
					render(data);
				}
			});
			event.preventDefault();
		});
		refresh();
	}
	$.fn.chatbox.defaults = {
		chatboxSummary: null,
		chatboxBody: null,
		chatboxForm: null,
		chatboxFeedUrl: null,
		chatboxPostUrl: null,
		avatars: null,
	};
}(jQuery));