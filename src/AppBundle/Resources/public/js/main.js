/*jq2 = jQuery.noConflict();
jq2(function( $ ) {*/
$(document).ready(function() {
	var groupColumn = 1;
	$('.table-datatable:not(#consumptiontable, #vehicletable)').DataTable({
		//pageLength: 50,
		// responsive: true,
		dom: 'Bfrtip',
		// buttons: [
		//   'copy', 'csv', 'excel', 'pdf', 'print'
		// ],
		buttons: {
			buttons: [{
				extend: 'print',
				text: '<i class="fa fa-print"></i> Print',
				title: $('h1').text(),
				exportOptions: {
					columns: ':not(.no-print)'
				},
				footer: true,
			}, {
				extend: 'pdfHtml5',
				text: '<i class="fa fa-file-pdf-o"></i> PDF',
				title: $('h1').text(),
				pageSize: 'A0',
				orientation: 'landscape',
				exportOptions: {
					columns: ':not(.no-print)'
				},
				footer: true
			}, {
				extend: 'copy'
			}, {
				extend: 'csv'
			}, {
				extend: 'excel'
			}],
			dom: {
				container: {
					className: 'dt-buttons'
				},
				button: {
					className: 'btn btn-default'
				}
			}
		},
		paging: false,
		fixedHeader: {
			header: true,
			footer: true
		},
		language: {
			"sEmptyTable": "Keine Daten in der Tabelle vorhanden",
			"sInfo": "_START_ bis _END_ von _TOTAL_ Einträgen",
			"sInfoEmpty": "0 bis 0 von 0 Einträgen",
			"sInfoFiltered": "(gefiltert von _MAX_ Einträgen)",
			"sInfoPostFix": "",
			"sInfoThousands": ".",
			"sLengthMenu": "_MENU_ Einträge anzeigen",
			"sLoadingRecords": "Wird geladen...",
			"sProcessing": "Bitte warten...",
			"sSearch": "Suchen",
			"sZeroRecords": "Keine Einträge vorhanden.",
			"oPaginate": {
				"sFirst": "Erste",
				"sPrevious": "Zurück",
				"sNext": "Nächste",
				"sLast": "Letzte"
			},
			"oAria": {
				"sSortAscending": ": aktivieren, um Spalte aufsteigend zu sortieren",
				"sSortDescending": ": aktivieren, um Spalte absteigend zu sortieren"
			}
		},
	});

	$('#vehicletable').DataTable({
		//pageLength: 50,
		dom: 'Bfrtip',
		buttons: {
			buttons: [{
				extend: 'print',
				text: '<i class="fa fa-print"></i> Print',
				title: $('h1').text(),
				exportOptions: {
					columns: ':not(.no-print)'
				},
				footer: true,
			}, {
				extend: 'pdfHtml5',
				text: '<i class="fa fa-file-pdf-o"></i> PDF',
				title: $('h1').text(),
				pageSize: 'A0',
				orientation: 'landscape',
				exportOptions: {
					columns: ':not(.no-print)'
				},
				footer: true
			}, {
				extend: 'copy'
			}, {
				extend: 'csv'
			}, {
				extend: 'excel'
			}],
			dom: {
				container: {
					className: 'dt-buttons'
				},
				button: {
					className: 'btn btn-default'
				}
			}
		},
		paging: false,
		fixedHeader: {
			header: true,
			footer: true
		},
		order: [
			[4, 'desc']
		],
		language: {
			url: "//cdn.datatables.net/plug-ins/1.10.15/i18n/German.json"
		},
		order: [
			[groupColumn, 'asc']
		],
		//   columnDefs: [
		//       { "visible": false, "targets": groupColumn }
		//   ],
		//   drawCallback: function ( settings ) {
		//   var api = this.api();
		//   var rows = api.rows( {page:'current'} ).nodes();
		//   var last=null;
		//
		//   api.column(groupColumn, {page:'current'} ).data().each( function ( group, i ) {
		//       if ( last !== group ) {
		//           $(rows).eq( i ).before(
		//               '<tr class="group"><td colspan="5">'+group+'</td></tr>'
		//           );
		//
		//           last = group;
		//       }
		//   } );
		//
		// }

	});
	moment.locale("de");
});


function ajaxdelete(data, route, e) {
	e.preventDefault();

	if (_.isObject(data)) {
		var ldata = data;
		_.assign(data, {
			'_method': 'DELETE'
		});
	} else {
		var data = {
			id: data,
			'_method': 'DELETE'
		}
	}
	swal({
		title: "Sind Sie sich sicher?",
		type: "error",
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Ja!",
		showCancelButton: true,
		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: "POST",
					url: route,
					data: data,
					success: function() {
						swal("Erfolgreich!", "Der Eintrag wurde erfolgreich gelöscht!", "success");
						window.location.reload();
					},
					error: function(xhr, ajaxOptions, thrownError) {
						swal("Fehlgeschlagen!", "Bitte versuchen Sie es nochmal.", "error");
					}
				});
			});
		}
	});
}

function ajaxrestore(data, route, e) {
	e.preventDefault();

	if (_.isObject(data)) {
		var ldata = data;
		_.assign(data);
	} else {
		var data = {
			id: data
		}
	}
	swal({
		title: "Element wiederherstellen?",
		type: "warning",
		confirmButtonClass: "btn-danger",
		confirmButtonText: "Ja!",
		showCancelButton: true,
		preConfirm: function() {
			return new Promise(function(resolve) {
				$.ajax({
					type: "POST",
					url: route,
					data: data,
					success: function() {
						swal("Erfolgreich!", "Der Eintrag wurde erfolgreich wiederhergestellt!", "success");
						window.location.reload();
					},
					error: function(xhr, ajaxOptions, thrownError) {
						swal("Fehlgeschlagen!", "Bitte versuchen Sie es nochmal.", "error");
					}
				});
			});
		}
	});
}