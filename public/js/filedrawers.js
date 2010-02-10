if (typeof FD == "undefined" || ! FD) {
	var FD = {};
}

FD.Config = {
	maxInspFileList: 5
};

FD.Utils = {
	formatBytes: function(bytes) {
		bytes = parseInt(bytes);
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

		return size;
	}
};

FD.InspDialogCloseEvent = new YAHOO.util.CustomEvent('InspDialogCloseEvent');

FD.FileInspector = function() {
	var currentDir,
		actions = {},
		evnt = new YAHOO.util.CustomEvent("Inspector Event"),
		temp = YAHOO.util.Dom.get('actionList'),
		links = temp.getElementsByTagName('a'),
		i;

	for (i=0; i<links.length; i++) {
		action = links[i].href.match(/\/?#(.*)$/);
		actions[action[1]] = {ref: links[i]};
	}

	var updateItemInfo = function(selectedRows) {
		var itemInfo = YAHOO.util.Dom.get('itemInfo'),
			totalBytes = 0,
			filenames = [],
			record;

		if ( ! selectedRows || selectedRows.length < 1) {
			itemInfo.innerHTML = '';
			return;
		}

		for (var i=0; i< selectedRows.length; i++) {
			record = this.getRecord(selectedRows[i]).getData();
			totalBytes += record.size;
			filenames.push(record.filename);
		}

		var itemInfoContent = '<h4>Information</h4><ul><li><strong>' +
				selectedRows.length + ' items selected</strong><br />' +
				FD.Utils.formatBytes(totalBytes) + '</li></ul>';

		if (selectedRows.length < FD.Config.maxInspFileList) {
			itemInfoContent += '<ul>';
			for (var i=0; i< filenames.length; i++) {
				itemInfoContent += '<li>' + filenames[i] + '</li>';
			}
			itemInfoContent += '</ul>';
		}

		// It's better to touch the DOM just once:
		itemInfo.innerHTML = itemInfoContent;
	};

	var update = function(oArgs) {
		var numSelected, i,
			permissions = {
				r: false,
				l: false,
				i: false,
				d: false,
				w: false,
				k: false,
				a: false
			};

		if (oArgs) {
			numSelected = this.getSelectedRows().length;
			updateItemInfo.apply(this, [this.getSelectedRows()]);
		} else {
			numSelected = 0;
			updateItemInfo();
		}

		for (i=0; i < currentDir.perms.length; i++) {			
			permissions[currentDir.perms.charAt(i)] = true;
		}
		
		filesSelected = (numSelected > 0) ? true : false;

		// Set actions
		if (permissions.i) {
			YAHOO.util.Dom.addClass(actions.upload.ref, 'enabled');
			YAHOO.util.Dom.addClass(actions.createFolder.ref, 'enabled');
		} else {
			YAHOO.util.Dom.removeClass(actions.upload.ref, 'enabled');
			YAHOO.util.Dom.removeClass(actions.createFolder.ref, 'enabled');
		}

		if (filesSelected && permissions.r && permissions.d) {
			YAHOO.util.Dom.addClass(actions.cut.ref, 'enabled');
		} else {
			YAHOO.util.Dom.removeClass(actions.cut.ref, 'enabled');
		}

		if (filesSelected && permissions.r) {
			YAHOO.util.Dom.addClass(actions.copy.ref, 'enabled');
		} else {
			YAHOO.util.Dom.removeClass(actions.copy.ref, 'enabled');
		}

		// delete is weird... you need ldw and either r or i
		if (filesSelected && permissions.d && (permissions.r || permissions.i) && permissions.w) {
			YAHOO.util.Dom.addClass(actions.del.ref, 'enabled');
		} else {
			YAHOO.util.Dom.removeClass(actions.del.ref, 'enabled');
		}

		if (filesSelected && numSelected < 2 && permissions.r && permissions.i && permissions.d) {
			YAHOO.util.Dom.addClass(actions.rename.ref, 'enabled');
		} else {
			YAHOO.util.Dom.removeClass(actions.rename.ref, 'enabled');
		}

		// set permissions for folder
		/*if ( itemInfo.numSel == 1 && files[ itemInfo.lastId ].type == folderMime ) {
		setInspControl( 'permsCtrl', 'permsCtrl_cmd()',
		'Set Permissions for Folder' );
		} else {
		setInspControl( 'permsCtrl', '', 'Set Permissions for Folder' );
		}*/
	};

	clearSelection = function(e) {
		YAHOO.util.Dom.removeClass(links, 'inspSelected');
	};


	var handleClick = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		
		if (target.href) {
			YAHOO.util.Event.preventDefault(e);
			var action = target.href.match(/\/#(.*)$/);

			if (YAHOO.util.Dom.hasClass(target, 'expandable')) {
				YAHOO.util.Dom.addClass(target, 'inspSelected');
			}

			evnt.fire(action[1]);
		}
	};

	YAHOO.util.Event.on('inspector', 'click', handleClick);

	return {
		init: function(crntDir) {
			currentDir = crntDir;
			update();
		},
		
		update:update,
		evnt:evnt,
		clearSelection:clearSelection
	}
};

FD.DirList = function(files, xhrData) {
	var dsLocal = new YAHOO.util.DataSource(files),
		dirTable,
		showHidden = false,
		dsRemote = new YAHOO.util.XHRDataSource(baseUrl, {
		responseType: YAHOO.util.DataSource.TYPE_JSON,
		connXhrMode: 'queueRequests'});

	var parseDate = function(data) {
		var date = new Date(data * 1000);
		return YAHOO.util.DataSource.parseDate(date);
	};

	var formatFilename = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = '<a href="' + baseUrl + '/list' + FD.Config.path + '/' + sData + '">' + sData + '</a>';
	};
	
	var formatType = function(elCell, oRecord, oColumn, sData) {
		elCell.innerHTML = '<img src="' + baseUrl + '/images/mime/small/' + sData + '.gif" />';
	};

	var formatFileSize = function(elCell, oRecord, oColumn, oData) {
		elCell.innerHTML = FD.Utils.formatBytes(oData);
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

	var hiddenFileFilter = function (req, raw, res, cb) {
		var data     = res.results || [],
			filtered = [],
			i,l;

		if (showHidden) {
			return res;
		}

		for (i = 0, l = data.length; i < l; ++i) {
			if (data[i].filename.indexOf('.') !== 0) {
				filtered.push(data[i]);
			}
		}
		res.results = filtered;

		return res;
	};

	var handleFileSelection = function(oArgs) {
		var elCheckbox = oArgs.target;

		if (elCheckbox.checked === true) {
			var row = this.getTrEl(elCheckbox);
			this.selectRow(row);
		} else {
			var row = this.getTrEl(elCheckbox);
			this.unselectRow(row);
		}
	};

	var columnDefs = [
		{key:"checked",label:"", width:"30", formatter:YAHOO.widget.DataTable.formatCheckbox},
		{key:"mimeImage", label:"Type", formatter:formatType},
		{key:"filename", label:"Name", sortable:true, formatter:formatFilename, editor: new YAHOO.widget.TextboxCellEditor()},
		{key:"modTime", label:"Last Modified", sortable:true, formatter:"date"},
		{key:"size", label:"Size", formatter:formatFileSize, sortable:true, sortOptions:{sortFunction:sortBySize}}
	];
	
	var responseSchema = {
		resultsList: "contents",
		fields: [{key:"filename"},{key:"mimeImage"},{key:"modTime", parser: parseDate},{key:"size"},{key:"mimeImage"}]
	};

	var tableInitialSort = {key:'filename', dir:YAHOO.widget.DataTable.CLASS_ASC};
	
	var getAjaxListCallback = function(oTable) {
		var tableState = oTable.getState();

		return callback = {
			success: function (o) {
				dsRemote.responseSchema = responseSchema;
				dsRemote.doBeforeCallback = hiddenFileFilter;

				tableState.sortedBy = tableInitialSort;

				dsRemote.sendRequest('/ajaxlist' + FD.Config.path, {
					success  : oTable.onDataReturnInitializeTable,
					failure  : oTable.onDataReturnInitializeTable,
					scope    : oTable,
					argument : tableState
				});
			},

			timeout: 3000
		};
	};

	return {
		init: function() {
			dsLocal.responseType = YAHOO.util.DataSource.TYPE_JSON;
			dsLocal.responseSchema = responseSchema;

			dsLocal.doBeforeCallback = hiddenFileFilter;

			dirTable = new YAHOO.widget.DataTable("fileList", columnDefs, dsLocal, {
				sortedBy:{key:"filename", dir:"asc"}});
			dirTable.subscribe("checkboxClickEvent", handleFileSelection);

			return dirTable;
		},

		toggleHiddenFilter: function(e, action) {
			if (action != 'showHidden') {
				return;
			}

			var tableState = dirTable.getState();
			tableState.sortedBy = tableInitialSort;
			
			showHidden = ! showHidden;
	
			dsLocal.sendRequest(showHidden,{
				success  : dirTable.onDataReturnInitializeTable,
				failure  : dirTable.onDataReturnInitializeTable,
				scope    : dirTable,
				argument : tableState
			});
    	},

		deleteItems: function(e, action) {
			if (action != 'del') {
				return;
			}

			var tableState = dirTable.getState();
			var callback = getAjaxListCallback(dirTable);
			
			var postData = 'files[]=';
			var files = [];

			for (var i=0; i < tableState.selectedRows.length; i++) {
				files.push(dirTable.getRecord(tableState.selectedRows[i]).getData().filename);
			}

			postData += files.join('&files[]=');

			var sUrl = baseUrl + '/delete' + FD.Config.path;
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, postData);		
		},
		
		createNewFolder: function(e, args) {
			var callback = getAjaxListCallback(dirTable);
			var sUrl = baseUrl + '/mkdir' + FD.Config.path;
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, 'folderName=' + args[0]);
		},

		renameItem: function(e, action) {
			if (action != 'rename') {
				return;
			}
			
			var trs = dirTable.getSelectedTrEls();
			
			if ( ! trs.length) {
				return;
			}
			
			// Hack to temporarily disable document cell editor bluring
			var blurEvent = dirTable.onEditorBlurEvent;
			dirTable.onEditorBlurEvent = function() {
				dirTable.onEditorBlurEvent = blurEvent;
			};

			var td = trs[0].getElementsByTagName('td')[2];
			dirTable.showCellEditor(td);

			var callback = getAjaxListCallback(dirTable);
			var sUrl = baseUrl + '/rename' + FD.Config.path;
			
			dirTable.subscribe('editorSaveEvent', function(oArgs) {
				YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, 'oldName=' + oArgs.oldData + '&newName=' + oArgs.newData);
			});
		},

		dsLocal:dsLocal,
		dsRemote:dsRemote
	}
};


FD.InfoBar = function() {
	var currentDir,
		changeDirForm = YAHOO.util.Dom.get('changeDirForm'),
		viewHTML;

	var changeDirView = '<input type="button" name="changeDir" value="Change" />';
	changeDirForm.innerHTML += changeDirView;
	viewHTML = changeDirForm.innerHTML;

	var update = function(oArgs) {};

	var handleClick = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		
		if ( ! target.value) {
			return;
		}

		YAHOO.util.Event.preventDefault(e);

		if (target.value == 'Change') {
			changeDirForm.innerHTML =
				'<input type="text" size="40" maxlength="100" value="' +
				currentDir + '" />' +
				'<input type="submit" value="Go" />' +
				'<input type="button" value="Cancel" />';
		} else if (target.value == 'Go') {
			location = baseUrl + '/list' +
				target.parentNode.getElementsByTagName('input')[0].value;
		} else if (target.value == 'Cancel') {
			changeDirForm.innerHTML = viewHTML;
		}
	};

	var handleSubmit = function(e) {
		YAHOO.util.Event.preventDefault(e);
	};

	YAHOO.util.Event.on('infoBar', 'click', handleClick);
	YAHOO.util.Event.on('changeDirForm', 'submit', handleSubmit);

	var kl = new YAHOO.util.KeyListener(document, {keys:13}, {fn:function(){alert('enter');}});

	return {
		init: function(crntDir) {
			currentDir = crntDir;
		},

		update:update
	}
};

FD.NewFolderDialog = function() {
	var evnt = new YAHOO.util.CustomEvent("New Folder Event");

	var hide = function() {
		YAHOO.util.Dom.setStyle('newFolder', 'display', 'none');
	};

	var handleClick = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		
		if ( ! target.href) {
			return;
		}

		YAHOO.util.Event.preventDefault(e);
		hide();
		FD.InspDialogCloseEvent.fire();
	};

	var handleSubmit = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		YAHOO.util.Event.preventDefault(e);
		evnt.fire(target.folderName.value);
	};
	
	var handleFocus = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
			target.value = '';
	};
	
	YAHOO.util.Event.on('newFolder', 'click', handleClick);
	YAHOO.util.Event.on('newFolder', 'submit', handleSubmit);
	YAHOO.util.Event.on('newFold', 'focus', handleFocus);
	
	return {
		show: function(e, action) {
			if (action != 'createFolder') {
				hide();
				return;
			}

			YAHOO.util.Dom.setStyle('newFolder', 'display', 'block');
		},
		
		evnt:evnt
	}
}();


FD.FavoritesDialog = function() {
	var bd = YAHOO.util.Dom.getElementsByClassName('bd', 'div', 'favorites')[0];

	var hide = function() {
		YAHOO.util.Dom.setStyle('favorites', 'display', 'none');
	};

	var handleClick = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		
		if ( ! target.href) {
			return;
		}

		YAHOO.util.Event.preventDefault(e);
		hide();
		FD.InspDialogCloseEvent.fire();
	};
	
	YAHOO.util.Event.on('favorites', 'click', handleClick);

	// Define the callbacks for the asyncRequest
	var callbacks = {
	
		success : function (o) {			
			var favorites = [];
			try {
				var favorites = YAHOO.lang.JSON.parse(o.responseText);
			}
			catch (x) {
				alert("JSON Parse failed!");
				return;
			}

			var favList = '<ul>';

			// Remember, it's better to just touch the DOM once
			for (var i = 0, len = favorites.contents.length; i < len; ++i) {
				var f = favorites.contents[i];
				favList += '<li><a href="' + baseUrl + '/list' + f.target + '">' + f.filename + '</a></li>';
			}

			favList += '</ul>';
			bd.innerHTML = favList;
		},

		failure: function (o) {
			if ( ! YAHOO.util.Connect.isCallInProgress(o)) {
				alert("Async call failed!");
			}
		},

		timeout: 3000
	};

	return {
		show: function(e, action) {
			if (action != 'favorites') {
				hide();
				return;
			}

			YAHOO.util.Dom.setStyle('favorites', 'display', 'block');
			YAHOO.util.Connect.asyncRequest('GET',"/mfile050/favorites/", callbacks);
		}
	}
}();


FD.PermissionsDialog = function() {
	var hide = function() {
		YAHOO.util.Dom.setStyle('permissions', 'display', 'none');
	};

	var handleClick = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		
		if ( ! target.href) {
			return;
		}

		YAHOO.util.Event.preventDefault(e);
		hide();
		FD.InspDialogCloseEvent.fire();
	};

	YAHOO.util.Event.on('permissions', 'click', handleClick);

	return {
		show: function(e, action) {
			if (action != 'permissions') {
				hide();
				return;
			}

			YAHOO.util.Dom.setStyle('permissions', 'display', 'block');
		}	
	}
}();


FD.TransactionMonitor = function() {
	var notifyArea = YAHOO.util.Dom.get('notifyArea'),
		originalHTML = notifyArea.innerHTML;

	var resetNote = function() {
		notifyArea.innerHTML = originalHTML;
	};

	var setNote = function(msg) {
		notifyArea.innerHTML = '<span class="notify">' + msg + '</span>';
		setTimeout(resetNote, 3000);
	};

	return {
		handle: function(eventType, args) {
			var response;

			try {
				response = YAHOO.lang.JSON.parse(args[0].responseText);
			}
			catch (x) {
				alert("JSON Parse failed: " + x);
				return;
			}

			if ( ! response.status) {
				return;
			}

			if (response.status == 'success') {
				setNote(response.message);
			} else if (response.status == 'fail') {
				alert(response.message);
			} else {
				alert('An unknown error occured');
			}
		},

		handleFailure: function(eventType, args) {
			if ( ! YAHOO.util.Connect.isCallInProgress(args[0])) {
				alert("Async call failed!");
			}
		}
	}
};


FD.FileManager = function(e, files) {
	FD.Config.path = files.path;

	var dirList = new FD.DirList(files),
		inspector = new FD.FileInspector(),
		infoBar = new FD.InfoBar(),
		txMonitor = new FD.TransactionMonitor();

	dirList.dsLocal.subscribe('responseEvent', function(oDS){inspector.init(oDS.response.contents[0]);});
	dirList.dsLocal.subscribe('responseEvent', function(oDS){infoBar.init(oDS.response.path);});

	//dirList.dsRemote.subscribe('responseEvent', function(oDS){alert(YAHOO.lang.dump(oDS.response)); alert('parseEvent');});

	var dirTable = dirList.init();
	inspector.evnt.subscribe(dirList.toggleHiddenFilter);
	inspector.evnt.subscribe(dirList.deleteItems);
	inspector.evnt.subscribe(dirList.renameItem);
	inspector.evnt.subscribe(FD.NewFolderDialog.show);
	inspector.evnt.subscribe(FD.FavoritesDialog.show);
	inspector.evnt.subscribe(FD.PermissionsDialog.show);

	YAHOO.util.Connect.successEvent.subscribe(txMonitor.handle);
	YAHOO.util.Connect.failureEvent.subscribe(txMonitor.handleFailure);

	// Event associations
	dirTable.subscribe('rowSelectEvent', inspector.update);
	dirTable.subscribe('rowUnselectEvent', inspector.update);
	dirTable.subscribe('rowUnselectEvent', inspector.update);
	dirTable.subscribe('initEvent', inspector.update);
	
	FD.NewFolderDialog.evnt.subscribe(dirList.createNewFolder);

	FD.InspDialogCloseEvent.subscribe(inspector.clearSelection);
};