(function($) {
	$.fn.contacts = function(options) {
		var opts = $.extend( {}, $.fn.contacts.defaults, options );
		var root = $(this);
		var data = [];
		
		function startWait(where) {
			root.find(where).css('display', 'block');
		}
		
		function stopWait(where) {
			root.find(where).css('display', 'none');
		}
		
		function reloadAction() {
			$.ajax({
				url: opts['contactReloadUrl'],
				data: {},
				dataType: "json",
				success: function (result) {
					startWait('#contact-overlay');
					updateState(result);
					stopWait('#contact-overlay');
				}
			});
		}

		function updateAction(item) {
			var modal = root.find('#update-modal');
			modal.find('#update-project-name').text(item['name']);
			if (item['required']) {
				modal.find('#contact-data-required').show();
			} else {
				modal.find('#contact-data-required').hide();
			}
			
			modal.find('#contact-mail').val(item['email']);
			modal.find('#contact-telephone').val(item['telephone']);
			modal.find('#notes').val(item['notes']);
			modal.find('#update-modal-save').unbind('click').click(function(data) {
				var formdata = new FormData();
				formdata.append('id', item['id']);
				formdata.append('email', modal.find('#contact-mail').val());
				formdata.append('telephone', modal.find('#contact-telephone').val());
				formdata.append('notes', modal.find('#notes').val());
				$.ajax({
					url: opts['contactUpdateUrl'],
					method: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function (result) {
						modal.modal('hide');
						startWait('#contact-overlay');
						updateState(result);
						stopWait('#contact-overlay');
					}
				});
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function installLocationUpdater() {
			root.find('#location-update-btn').unbind('click').click(function() {
				var formdata = new FormData();
				formdata.append('location', root.find('#contact-location').val());
				$.ajax({
					url: opts['contactLocationUrl'],
					method: 'POST',
					data: formdata,
					processData: false,
					contentType: false,
					success: function (result) {
						startWait('#location-overlay');
						updateState(result);
						stopWait('#location-overlay');
					}
				});
			});
		}
		
		function createUpdateActionButton(item) {
			return $('<button/>', {'class': 'btn btn-default btn-xs', 'role': 'btn', 'style': 'width: 80px'}).click(function() {
				updateAction(item);
			}).text(opts['lang']['update']);
		}
		
		function createSingleProject(project) {
			var tr = $('<tr/>', {'data-id': project['id']} );
			tr.append($('<td/>').append(project['name']));
			if (project['email']) {
				tr.append($('<td/>').append('<a href="mailto:'+project['email']+'">'+project['email']+'</a>'));
			} else {
				tr.append($('<td/>'));
			}
			tr.append($('<td/>').append(project['telephone']));
			tr.append($('<td/>').append(project['notes']));
			
			var actionBox = $('<td/>');
			var actionGroup = $('<div/>', {'class': 'btn-group', 'role': 'group'});
			actionGroup.append(createUpdateActionButton(project));
			tr.append(actionBox.append(actionGroup));
			return tr;
		}
		
		function updateSingleProject(project) {
			var item = root.find('#contacts').find('tr[data-id='+project['id']+']');
			item.replaceWith(createSingleProject(project));
		}
		
		function updateAllProjects(projects) {
			var tableBody = root.find('#contacts');
			
			tableBody.empty();
			for(i in projects) {
				tableBody.append(createSingleProject(projects[i]));
			}
		}
		
		function updateState(data) {
			if (data.success === 1) {
				if (data.project) {
					updateSingleProject(data.project);
				}
				if (data.projects) {
					updateAllProjects(data.projects);
				}
				if (data.location) {
					markGesture(root.find('#contact-location'), 'fa-thumbs-up');
				}
			} else {
				if (data.location) {
					markGesture(root.find('#contact-location'), 'fa-thumbs-down');
				}
			}
		}
		
		function markGesture(element, chosenGesture) {
			element.popover({
				html: true,
				content: '<span class="fa '+chosenGesture+'"></span>',
				delay: 400,
				placement: 'top'
			});
			element.popover('show');
			setTimeout(function () {
				element.popover('destroy');
			}, 1000);
		}
		
		installLocationUpdater();
		reloadAction();
		return this;
	};
	
	$.fn.contacts.defaults = {	
		contactReloadUrl: null,
		contactUpdateUrl: null,
		contactLocationUrl: null,
		lang: {
			update: 'Update',
		}
	};
}(jQuery));