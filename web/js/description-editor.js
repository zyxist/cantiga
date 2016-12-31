(function($) {
	$.fn.descriptions = function(options) {
		var opts = $.extend( {}, $.fn.descriptions.defaults, options );
		var root = $(this);
		
		if (opts['tabActivator']) {
			$(opts['tabActivator']).on('shown.bs.tab', function (e) {
				if ($(e.target).attr('aria-controls') === opts['noteTab']) {
					loadNotes();
				}
			});
		} else {
			loadNotes();
		}
		
		function loadNotes() {
			$.ajax({
				url: opts['reloadActionUrl'],
				data: { id: opts['id'] },
				dataType: "json",
				success: function (result) {
					updateState(result);
				}
			});
		}

		function updateState(data) {
			if (data.success === 1) {
				if (data.notes) {
					updateAllNotes(data.notes);
				}
				if (data.note) {
					updateSingleNote(data.note);
				}
			}
		}
		
		function editAction(item) {
			var modal = root.find('#note-modal');
			modal.find('#note-name').text(item['name']);
			modal.find('#note-content-editor').summernote('reset');
			modal.find('#note-content-editor').summernote('code', item['editable']);
			modal.find('#note-modal-save').unbind('click').click(function(data) {
				$.ajax({
					url: opts['updateActionUrl'],
					data: { i: item['id'], c: modal.find('#note-content-editor').val() },
					dataType: "json",
					success: function (result) {
						modal.modal('hide');
						updateState(result);
					}
				});
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function createSingleNote(note) {
			var div = $('<div/>', {'data-id': note['id'], 'class': 'editable-content', 'title': opts['clickToEditText']} );
			div.tooltip();
			
			div.click(function() {
				editAction(note);
			});

			div.append('<h4>'+note['name']+'</h4>');
			div.append(note['content']);
			div.append('<hr>');
			
			return div;
		}
		
		function updateAllNotes(notes) {
			var noteDiv = root.find('#note-location');
			noteDiv.empty();
			for(i in notes) {
				noteDiv.append(createSingleNote(notes[i]));
			}
		}
		
		function updateSingleNote(note) {
			var item = root.find('#note-location').find('div[data-id='+note['id']+']');
			item.replaceWith(createSingleNote(note));
		}
	};
		
	$.fn.descriptions.defaults = {
		id: null,
		tabActivator: null,
		noteTab: null,
		reloadActionUrl: null,
		updateActionUrl: null,
		clickToEditText: 'Click to edit',
	};
}(jQuery));
