(function($) {
	$.fn.milestones = function(options) {
		var opts = $.extend( {}, $.fn.milestones.defaults, options );
		var root = $(this);
		
		if (opts['selector']) {
			root.find('#selectItem').change(function() {
				reloadAction($(this).val());
			});
		}
		
		function reloadAction(item) {
			$.ajax({
				url: opts['reloadActionUrl'],
				data: { i: item },
				dataType: "json",
				success: function (result) {
					updateState(result, item);
				}
			});
		}
		
		function viewAction(item) {
			var modal = root.find('#view-modal');
			modal.find('#milestone-name').text(item['name']);
			modal.find('#milestone-description').text(item['description']);
			modal.find('#milestone-deadline').text(item['deadline']);
			modal.find('#milestone-completedAt').text(item['completedAt']);
			modal.find('#milestone-progress').html(renderProgressBar(item['progress']));
			modal.find('#view-modal-close').unbind('click').click(function(data) {
				modal.modal('hide');
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function completeAction(item) {
			var modal = root.find('#complete-modal');
			modal.find('#complete-milestone-name').text(item['name']);
			modal.find('#complete-modal-confirm').unbind('click').click(function(data) {
				$.ajax({
					url: opts['completeActionUrl'],
					data: { i: getSelectedItem(), m: item['id'] },
					dataType: "json",
					success: function (result) {
						modal.modal('hide');
						updateState(result, getSelectedItem());
					}
				});
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function cancelAction(item) {
			var modal = root.find('#cancel-modal');
			modal.find('#cancel-milestone-name').text(item['name']);
			modal.find('#cancel-modal-confirm').unbind('click').click(function(data) {
				$.ajax({
					url: opts['cancelActionUrl'],
					data: { i: getSelectedItem(), m: item['id'] },
					dataType: "json",
					success: function (result) {
						modal.modal('hide');
						updateState(result, getSelectedItem());
					}
				});
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function updateAction(item) {
			var modal = root.find('#update-modal');
			modal.find('#update-milestone-name').text(item['name']);
			modal.find('#new-milestone-progress').val(item['progress']);
			modal.find('#update-modal-save').unbind('click').click(function(data) {
				$.ajax({
					url: opts['updateActionUrl'],
					data: { i: getSelectedItem(), m: item['id'], p: modal.find('#new-milestone-progress').val() },
					dataType: "json",
					success: function (result) {
						modal.modal('hide');
						updateState(result, getSelectedItem());
					}
				});
			});
			modal.modal({keyboard: true, show: true});
		}
		
		function createViewActionButton(item) {
			return $('<button/>', {'class': 'btn btn-primary btn-xs', 'role': 'btn', 'style': 'width: 80px'}).click(function() {
				viewAction(item);
			}).text(opts['viewActionText']);
		}
		
		function createCompleteActionButton(item) {
			return $('<button/>', {'class': 'btn btn-success btn-xs', 'role': 'btn', 'style': 'width: 80px'}).click(function() {
				completeAction(item);
			}).text(opts['completeActionText']);
		}
		
		function createCancelActionButton(item) {
			return $('<button/>', {'class': 'btn btn-danger btn-xs', 'role': 'btn', 'style': 'width: 80px'}).click(function() {
				cancelAction(item);
			}).text(opts['cancelActionText']);
		}
		
		function createUpdateActionButton(item) {
			return $('<button/>', {'class': 'btn btn-default btn-xs', 'role': 'btn', 'style': 'width: 80px'}).click(function() {
				updateAction(item);
			}).text(opts['updateActionText']);
		}
		
		function updateState(data, selectedItem) {
			if (data.success === 1) {
				if (data.selection) {
					updateSelection(data.selection, selectedItem);
				}
				if (typeof data.progressBar !== 'undefined') {
					updateProgressBar(data.progressBar);
				}
				if (data.milestone) {
					updateSingleMilestone(data.milestone);
				}
				if (data.milestones) {
					updateAllMilestones(data.milestones);
				}
			}
		}
		
		function updateSelection(selection, selectedItem) {
			var selectorElement = root.find('#selectItem');
			var content = '';
			for (i in selection) {
				if (selectedItem == selection[i].id) {
					content += '<option value="'+selection[i]['id']+'" selected>'+selection[i]['name']+'</option>';
				} else {
					content += '<option value="'+selection[i]['id']+'">'+selection[i]['name']+'</option>';
				}
			}
			selectorElement.empty();
			selectorElement.html(content);
		}
		
		function updateProgressBar(progressBar) {
			var progressElement = root.find('#progressBar');
			progressElement.attr('style', 'width: '+progressBar+'%');
			if (progressBar < 50) {
				progressElement.attr('class', 'progress-bar progress-bar-danger');
			} else if (progressBar < 80) {
				progressElement.attr('class', 'progress-bar progress-bar-warning');
			} else {
				progressElement.attr('class', 'progress-bar progress-bar-success');
			}
		}
		
		function createSingleMilestone(milestone) {
			var tr = $('<tr/>', {'data-id': milestone['id']} );
			tr.append($('<td/>').append(milestone['name']));
			tr.append($('<td/>').append('<div class="progress progress-xs">'+renderProgressBar(milestone['progress'])+'</div>'));
			tr.append($('<td/>').append(renderLabel(milestone['progress'])));
			tr.append($('<td/>').append(milestone['deadline']));
			tr.append($('<td/>').append(milestone['completedAt']));
			
			var actionBox = $('<td/>');
			var actionGroup = $('<div/>', {'class': 'btn-group', 'role': 'group'});
			for(action in milestone['actions']) {
				switch (milestone['actions'][action]) {
					case 'view':
						actionGroup.append(createViewActionButton(milestone));
						break;
					case 'complete':
						actionGroup.append(createCompleteActionButton(milestone));
						break;
					case 'cancel':
						actionGroup.append(createCancelActionButton(milestone));
						break;
					case 'update':
						actionGroup.append(createUpdateActionButton(milestone));
						break;
				}
			}
			tr.append(actionBox.append(actionGroup));
			return tr;
		}
		
		function updateSingleMilestone(milestone) {
			var item = root.find('#milestones').find('tr[data-id='+milestone['id']+']');
			item.replaceWith(createSingleMilestone(milestone));
		}
		
		function updateAllMilestones(milestones) {
			var tableBody = root.find('#milestones');
			
			tableBody.empty();
			for(i in milestones) {
				tableBody.append(createSingleMilestone(milestones[i]));
			}
		}
		
		function renderProgressBar(progress) {
			if (progress < 50) {
				return '<div class="progress-bar progress-bar-danger" style="width: '+progress+'%"></div>';
			} else if(progress < 80) {
				return '<div class="progress-bar progress-bar-warning" style="width: '+progress+'%"></div>';
			} else {
				return '<div class="progress-bar progress-bar-success" style="width: '+progress+'%"></div>';
			}
		}
		
		function renderLabel(progress) {
			if (progress < 50) {
				return '<span class="badge bg-red">'+progress+'%</span>';
			} else if(progress < 80) {
				return '<span class="badge bg-orange">'+progress+'%</span>';
			} else {
				return '<span class="badge bg-green">'+progress+'%</span>';
			}
		}
		
		function getSelectedItem() {
			if (opts['selector']) {
				return root.find('#selectItem').val();
			}
			return opts['selectedEntity'];
		}
		
		reloadAction(opts['selectedEntity']);
		return this;
	};
	$.fn.milestones.defaults = {
		selector: true,
		selectedEntity: null,
		reloadActionUrl: null,
		completeActionUrl: null,
		updateActionUrl: null,
		cancelActionUrl: null,
		completeActionText: 'Complete',
		updateActionText: 'Update',
		cancelActionText: 'Cancel',
		viewActionText: 'View',
	};
}(jQuery));