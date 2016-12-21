(function($) {
	$.fn.membersDisplay = function(options) {
		var opts = $.extend( {}, $.fn.membersDisplay.defaults, options );
		var data = null;
			
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var target = $(e.target).attr("href");
			if (target === '#'+opts['tab']) {
				if (null === data) {
					download();
				}
			}
		});
		
		function download() {
			$.ajax({
				url: opts['url'],
				dataType: "json",
				success: function (result) {
					if (result.status === 1) {
						data = result.data;
					}
					render();
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
		
		function render() {
			var tbody = $(opts['table']).find('tbody');
			for (i in data) {
				var tr = $('<tr/>');
				tr.append($('<td/>', {'width': '32px'}).append($('<img/>', {'src': createAvatarPath(data[i]['avatar']), 'class': 'img-circle', 'width': '32px'})));
				tr.append($('<td><a href="'+data[i]['url']+'">'+data[i]['name']+'</a></td>'));
				tr.append($('<td/>').append(data[i]['location']));
				tr.append($('<td/>').append(data[i]['note']));
				if (data[i]['contactMail']) {
					tr.append($('<td/>').append($('<a/>', {'href': 'mailto:'+data[i]['contactMail']}).append(data[i]['contactMail'])));
				} else {
					tr.append($('<td/>').append('---'));
				}
				tbody.append(tr);
			}
		}
		return this;
	};
	$.fn.membersDisplay.defaults = {
		url: null,
		table: null,
		tab: null,
		avatars: null,
	};
}(jQuery));
