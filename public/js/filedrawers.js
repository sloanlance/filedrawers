YAHOO.util.Event.addListener(window, "load", function() {
    YAHOO.example.XHR_JSON = function() {
		var test = true;
        var formatFilename = function(elCell, oRecord, oColumn, sData) {
            elCell.innerHTML = '<a href="/~username/mfile050/list' + files.path + '/' + sData + '">' + sData + '</a>';
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
			} else if ( readPriv ) { // size unknown - file is probably not readable
				size = 'empty';
			} else {
				size = '-';
			}
			
			elCell.innerHTML = size;
		};

        var myColumnDefs = [
            {key:"filename", label:"Name", sortable:true, formatter:formatFilename},
            {key:"modTime", label:"Last Modified", sortable:true, formatter:YAHOO.widget.DataTable.formatDate},
            {key:"size", label:"Size", formatter:formatFileSize}
        ];
		
		if (files) {
			var myDataSource = new YAHOO.util.DataSource(files);
		} else {
			var myDataSource = new YAHOO.util.DataSource("/~username/mfile050/list");
		}
		
		parseDate = function(data) {
			var date = new Date(data * 1000);
			return DataSourceBase.parseDate(date);
		};

        myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
        //myDataSource.connXhrMode = "queueRequests";
        myDataSource.responseSchema = {
            resultsList: "contents",
            fields: [{key:"filename"},{key:"modTime", parser:parseDate},{key:"size"},{key:"permissions"},{key:"viewable"},{key:"mimeImage"}]
        };

        var myDataTable = new YAHOO.widget.DataTable("fileList", myColumnDefs, myDataSource, {sortedBy:{key:"filename", dir:"asc"}});

        var mySuccessHandler = function() {
            this.set("sortedBy", null);
            this.onDataReturnInitializeTable.apply(this,arguments);
        };

        var myFailureHandler = function() {
            this.showTableMessage(YAHOO.widget.DataTable.MSG_ERROR, YAHOO.widget.DataTable.CLASS_ERROR);
            this.onDataReturnInitializeTable.apply(this,arguments);
        };

        var callbackObj = {
            success : mySuccessHandler,
            failure : myFailureHandler,
            scope : myDataTable
        };

        return {
            oDS: myDataSource,
            oDT: myDataTable
        };
    }();
});
