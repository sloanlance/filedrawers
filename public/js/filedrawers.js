if (typeof Filedrawers == "undefined" || !Filedrawers) {
	var Filedrawers = {};
}

Filedrawers.Directory = function() {
	var directoryPath = null;

	var parseDate = function(data) {
		var date = new Date(data * 1000);
		return YAHOO.util.DataSource.parseDate(date);
	};
	
	var formatFilename = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = '<a href="' + baseUrl + '/list' + directoryPath + '/' + sData + '">' + sData + '</a>';
	};
	
	var formatType = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = '<img src="' + baseUrl + '/images/mime/small/' + sData + '.gif" />';
	};
	
	var formatFileSize = function(elCell, oRecord, oColumn, oData) {
		var bytes = parseInt( oData );
		var tmp = 0.0;
		var size = null;
		
		if ( bytes >= 1073741824 ) {
			size = (Math.round((bytes/1073741824)*100)/100) + ' GB';
		} else if ( bytes >= 1048576 ) {
			size =(Math.round((bytes/1048576)*100)/100) + ' MB';
		} else if ( bytes >= 1024 ) {
			size = (Math.round((bytes/1024)*100)/100) + ' KB';
		} else if ( bytes > 0 ) {
			size = bytes + ' Bytes';
		} else if ( true /*readPriv*/ ) { // size unknown - file is probably not readable
			size = 'empty';
		} else {
			size = '-';
		}

		elCell.innerHTML = size;
	};
	
	var sortBySize = function(a, b, desc) {
		// Deal with empty values
		if ( ! YAHOO.lang.isValue(a)) {
			return (!YAHOO.lang.isValue(b)) ? 0 : 1;
		}
		else if (!YAHOO.lang.isValue(b)) {
			return -1;
		}
		
		// First compare by size
		var comp = YAHOO.util.Sort.compare;
		var compFile = comp(a.getData("size"), b.getData("size"), desc);

		// If sizes are equal, then compare by filename
		return (compFile !== 0) ? compFile : comp(a.getData("filename"), b.getData("filename"), desc);
	};
	
	return {
		init: function(files) {
			directoryPath = files.path;

			var myColumnDefs = [
				{key:"mimeImage", label:"Type", formatter:formatType},
				{key:"filename", label:"Name", sortable:true, formatter:formatFilename},
				{key:"modTime", label:"Last Modified", sortable:true, formatter:"date"},
				{key:"size", label:"Size", formatter:formatFileSize, sortable:true, sortOptions:{sortFunction:sortBySize}}
			];

			var myDataSource = new YAHOO.util.DataSource(files);
			
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
			myDataSource.responseSchema = {
			resultsList: "contents",
				fields: [{key:"filename"},{key:"mimeImage"},{key:"modTime", parser: parseDate},{key:"size"},{key:"mimeImage"}]
			};
			
			var fileTable = new YAHOO.widget.DataTable("fileList", myColumnDefs, myDataSource, {sortedBy:{key:"filename", dir:"asc"}});
		}
	}
}(); // Singleton
