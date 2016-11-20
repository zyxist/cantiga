(function($) {
	$.fn.membership = function(options) {
		var opts = $.extend( {}, $.fn.membership.defaults, options );
		var data = [];
	
		if (null !== opts['projectSelector']) {
			var selectedProject = $(opts['projectSelector']).val();
			$(opts['projectSelector']).change(function() {
				refresh($(this).val());
				selectedProject = $(this).val();
			});
		} else {
			var selectedProject = opts['selectedProject'];
		}
		if (null !== opts['membershipForm']) {
			var membershipAdder = $(opts['membershipForm']).find('input[role="member-selector"]');
			var membershipRole = $(opts['membershipForm']).find('select[role="role-selector"]');
			var membershipNote = $(opts['membershipForm']).find('input[role="note-selector"]');
			
			membershipAdder.autocomplete({
				minLength: 4,
				source: function(req, res) {
					$.ajax({
						url: opts['memberHintUrl'],
						dataType: "json",
						data: {
							q: req.term,
							p: selectedProject
						},
						success: function (data) {
							res( data );
						}
					});
				},
				select: function(event, ui) {
					if (ui.item.label.length > 3) {
						insert(ui.item.label, membershipRole.val(), membershipNote.val());
					}
				}
			});
		}
		
		function insert(who, role, note) {
			$.ajax({
				url: opts['memberAddUrl'],
				dataType: "json",
				method: "post",
				data: {
					u: who,
					p: selectedProject,
					r: role,
					n: note
				},
				success: function (data) {
					if (data.status == 1) {
						markGesture(membershipAdder, 'glyphicon-thumbs-up');
					} else {
						markGesture(membershipAdder, 'glyphicon-thumbs-down');
					}
					render(data.data);
					membershipAdder.val('');
				}
			});
		}
		
		function edit(who, whoName, startRole, startNote) {
			$(opts['editModal']).find('span[role="title"]').text(whoName);
			$(opts['editModal']).find('select[role="role-selector"]').val(startRole);
			$(opts['editModal']).find('input[role="note-selector"]').val(startNote);
			$(opts['editModalConfirm']).unbind('click').click(function(data) {
				$.ajax({
					url: opts['memberEditUrl'],
					dataType: "json",
					data: {
						p: selectedProject,
						u: who,
						r: $(opts['editModal']).find('select[role="role-selector"]').val(),
						n: $(opts['editModal']).find('input[role="note-selector"]').val()
					},
					success: function (data) {
						if (data.status == 1) {
							render(data.data);
						}
					}
				});
				$(opts['editModal']).modal('hide');
			});
			$(opts['editModal']).modal({keyboard: true, show: true});
		}
		
		function remove(who, whoName) {
			$(opts['removeModal']).find('span[role="title"]').text(whoName);
			$(opts['removeModalConfirm']).unbind('click').click(function(data) {
				$.ajax({
					url: opts['memberRemoveUrl'],
					dataType: "json",
					data: {
						p: selectedProject,
						u: who,
					},
					success: function (data) {
						if (data.status == 1) {
							render(data.data);
						}
					}
				});
				$(opts['removeModal']).modal('hide');
			});
			$(opts['removeModal']).modal({keyboard: true, show: true});
		}
		
		function refresh(projectId) {
			$.ajax({
				url: opts['memberReloadUrl'],
				dataType: "json",
				data: {	p: projectId },
				success: function (data) {
					render(data);
				}
			});
		}
		
		function render(data) {
			var tbody = $(opts['dataTable']).find('tbody');
			tbody.empty();
			for (i in data) {
				var tr = $('<tr/>');
				tr.append($('<td/>').append(data[i]['id']));
				tr.append($('<td/>').append(data[i]['name']));
				if (data[i]['role'] !== null) {
					tr.append($('<td/>').append(opts['lang'][data[i]['roleName']]));
				}
				tr.append($('<td/>').append(data[i]['note']));
				if (null !== opts['memberEditUrl']) {
					var roleBtn = $('<a/>', {class: 'btn btn-xs btn-primary', href: '#'}).append(opts['lang']['role']);
					roleBtn.click(function(id, name, role, note){ return function(data) {
						edit(id, name, role, note);
					}; }(data[i]['id'], data[i]['name'], data[i]['role'], data[i]['note']));
				}
				var removeBtn = $('<a/>', {class: 'btn btn-xs btn-danger', href: '#'}).append(opts['lang']['remove']);
				removeBtn.click(function(id, name){ return function(data) {
					remove(id, name);
				}; }(data[i]['id'], data[i]['name']));
				var optRow = $('<td/>');
				if (null !== roleBtn) {
					optRow.append(roleBtn).append(' ');
				}
				optRow.append(removeBtn);
				tr.append(optRow);
				tbody.append(tr);
			}
		}
		
		function markGesture(element, chosenGesture) {
			element.popover({
				html: true,
				content: '<span class="glyphicon '+chosenGesture+'"></span>',
				delay: 400,
				placement: 'top'
			});
			element.popover('show');
			setTimeout(function () {
				element.popover('hide');
			}, 1000);
		}
		
		refresh(selectedProject);
		return this;
	};
	
	$.fn.membership.defaults = {
		selectedProject: null,
		projectSelector: null,
		membershipForm: null,
		dataTable: null,
		
		editModal: null,
		editModalConfirm: null,
		removeModal: null,
		removeModalConfirm: null,
		
		memberHintUrl: null,
		memberReloadUrl: null,
		memberAddUrl: null,
		memberEditUrl: null,
		memberRemoveUrl: null,
		lang: {
			remove: 'Remove',
			role: 'Change role',
			Unknown: 'Unknown',
			Visitor: 'Visitor',
			Member: 'Member',
			Manager: 'Manager'
		}
	};
}(jQuery));