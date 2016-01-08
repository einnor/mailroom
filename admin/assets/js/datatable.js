function datatable_init(url,cols,table_id){
	//$(table_id).empty();
	var oTable=$(table_id).DataTable({
		"destroy":true,
		"ajax": { "url":url,"dataSrc": "response","dataType":"json","type": "GET" },
		"aLengthMenu": [ [5,10,25,50, 100, 200, -1],[5,10,25, 50, 100, 200, "All"] ],
		"pageLength": 10, "paginate": true, "pagingType": "full_numbers",
		"scrollX": false,
		"scrollCollapse": 	false,
		"paging":        	true,
		"dom": '<"H"lTfr>t<"F"ip>',
		"columns": cols,
		//"select": true,
		order: [ 0, 'desc' ],
		"columnDefs": [
			{//Role
				"render": function ( data, type, row ) {
					if(row.role=='0'){return 'Common';}
					if(row.role=='1'){return 'Mail Room';}
					if(row.role=='2'){return 'Admin';}
				},"targets": [4],
			},
		],
		"initComplete": function(settings, json) {
			$('#display_length, #display_filter').addClass('pull-right').append('<span>&nbsp;&nbsp;&nbsp;</span>')
		}
	})//End of DT
	return oTable;
}
