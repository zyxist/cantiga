(function($) {
	$.fn.discussion = function(options) {
		var opts = $.extend( {}, $.fn.discussion.defaults, options );
		var lastPostTime = opts['lastPostTime'];
		
		function createAvatarPath(av) {
			if(!av) {
				return opts['avatars']+'/default.gif';
			}
			var fst = av.substring(0, 2);
			var snd = av.substring(2, 4);
			return opts['avatars']+'/32/'+fst+'/'+snd+'/'+av; 
		}
		
		function renderMessages(data, discussionBody) {
			var newPostTime = 0;			
			for (i in data.days) {
				if (Math.floor(lastPostTime / 86400) !== Math.floor(data.days[i]['time'] / 86400)) {
					var dayItemContent = $('<span/>', {'class': 'bg-red'}).append(data.days[i]['timeFormatted']);
					var dayItem = $('<li/>', {'class': 'time-label' }).append(dayItemContent);
					discussionBody.append(dayItem);
				}
				
				for (j in data.days[i]['posts']) {
					var icon = $('<i/>', {'class': 'fa fa-envelope bg-blue'});
					var postTime = $('<span class="time"><i class="fa fa-clock-o"> '+data.days[i]['posts'][j]['createdAtFormatted']+'</i>');
					var postHeader = $('<h3 class="timeline-header"><img src="'+createAvatarPath(data.days[i]['posts'][j]['avatar'])+'" alt="av" width="24" class="img-circle" /> <a href="'+data.days[i]['posts'][j]['profileUrl']+'">'+data.days[i]['posts'][j]['userName']+'</a></h3>');
					var postContent = $('<div class="timeline-body">' + data.days[i]['posts'][j]['content'] + '</div>');
					var postFooter = $('<div class="timeline-footer" />');
					var timelineItem = $('<div/>', {'class': 'timeline-item'}).append(postTime).append(postHeader).append(postContent).append(postFooter);
					var postItem = $('<li/>').append(icon).append(timelineItem);
					discussionBody.append(postItem);
					newPostTime = data.days[i]['posts'][j]['createdAt'];
				}
			}
			lastPostTime = newPostTime;
		}
		
		function render(data) {
			lastPostTime = 0;
			discussionBody = $(opts['discussionBody']);
			discussionBody.empty();
			renderMessages(data, discussionBody);
		}

		if (opts['canPost']) {
			$(opts['discussionForm']).submit(function(event) {
				$.ajax({
					url: opts['discussionPostUrl'],
					method: 'post',
					dataType: "json",
					data: {
						'content': $(opts['discussionForm']).find('#discussion-content').summernote('code')
					},
					success: function (data) {
						$(opts['discussionForm']).find('#discussion-content').summernote('reset');
						$(opts['discussionModal']).modal('hide');
						render(data);
					}
				});
				event.preventDefault();
			});
		}
		$(opts['discussionMore']).click(function(event) {
			$.ajax({
				url: opts['discussionFeedUrl'],
				method: 'get',
				dataType: "json",
				data: {
					'lastPostTime': lastPostTime
				},
				success: function (data) {
					if (data.success === 2) {
						$(opts['discussionMore']).attr('disabled', 'disabled');
					} else {
						renderMessages(data, $(opts['discussionBody']));
					}
				}
			});
		});
	}
	$.fn.discussion.defaults = {
		discussionMore: null,
		discussionBody: null,
		discussionForm: null,
		discussionModal: null,
		discussionFeedUrl: null,
		discussionPostUrl: null,
		canPost: 1,
		avatars: null,
		lastPostTime: null,
	};
}(jQuery));
