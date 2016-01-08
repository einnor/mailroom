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
		order: [ 5, 'desc' ],
		"columnDefs": [
			{//Type
				"render": function ( data, type, row ) {
					if(row.type=='0'){return 'Organization';}
					if(row.type=='1'){return 'Others';}
				},"targets": [1],
			},
			{//Category
				"render": function ( data, type, row ) {
					if(row.category=='0'){return 'Envelope';}
					if(row.category=='1'){return 'Bag';}
				},"targets": [2],
			},
			{//State
				"render": function ( data, type, row ) {
					if(row.state=='0'){return 'Submitted';}
					if(row.state=='1'){return 'Sighted';}
					if(row.state=='2'){return 'Missing';}
					if(row.state=='3'){return 'Exception';}
					if(row.state=='4'){return 'Dispatched';}
					if(row.state=='5'){return 'Delivered';}
					if(row.state=='6'){return 'Received';}
				},"targets": [4],
			},
			{//Change State
				"render": function ( data, type, row ) {
					var changestate = '<div class="btn-group" id="'+row.id+'"><button type="button" id="'+row.id+'" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span>'+
										'</button>'+
										'<ul class="dropdown-menu">'+
										 ' <li><a href="#" id="1">Sighted</a></li>'+
										  '<li role="separator" class="divider"></li>'+
										  '<li><a href="#" id="2">Missing</a></li>'+
										  '<li role="separator" class="divider"></li>'+
										  '<li><a href="#" id="3">Exception</a></li>'+
										  '<li role="separator" class="divider"></li>'+
										  '<li><a href="#" id="4">Dispatched</a></li>'+
										  '<li role="separator" class="divider"></li>'+
										  '<li><a href="#" id="5">Delivered</a></li>'+
										  '<li role="separator" class="divider"></li>'+
										  '<li><a href="#" id="6">Received</a></li>'+
									  '</ul></div>';
					return changestate;
				},"targets": [6],
			},
		],
		"initComplete": function(settings, json) {
			$('#display_length, #display_filter').addClass('pull-right').append('<span>&nbsp;&nbsp;&nbsp;</span>')
		}
	})//End of DT
	return oTable;
}
