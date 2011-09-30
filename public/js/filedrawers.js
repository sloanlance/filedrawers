var api,
myJSONdata,
folderPerms,
homeURL,
currentURL,
currentService,
cutCopyURL,
cutCopyFiles = [],
clipboardState,
baseUrl = '',
dirList,
inspector,
infoBar,
favorites,
timer1,
timer2,
services = {},
History = YAHOO.util.History;

if (typeof FD == "undefined" || ! FD) {
	var FD = {};
}

FD.api = function() {
    var _apiUrl = baseUrl +'webservices/';
    var _urlParams = { 'format': 'json', 'wappver': webAppVersion.toString() };
    var _dataParams = {};
    var _getParams = function( type, params ) {
        var merged_params = {};
        var key;

        if ( typeof params == 'object' ) {
            for ( key in type ) {
                merged_params[ key ] = type[ key ];
            }

            for ( key in params ) {
                merged_params[ key ] = params[ key ];
            }
        } else {
            return type
        }

    return merged_params;
    };
    var _setParam = function( type, key, value ) {
        type[ key ] = value;
    };
    return {
	
        getUrlParams: function( params ) {
            return _getParams( _urlParams, params );
        },
        setUrlParam: function( key, value ) {
            _setParam( _urlParams, key, value );
        },

        getData: function( params ) {
            return _getParams( _dataParams, params );
        },
        setDataParam: function( key, value ) {
            _setParam( _dataParams, key, value );
        },

        getActionUrl: function( action, params, merge ) {
			
            if ( typeof params == 'undefined' ) {
               params = _urlParams;
            } else if ( typeof merge != 'undefined' && merge ) {
                params = this.getUrlParams( params );
            }

            var query = FD.Utils.urlEncode( params );
            if ( query != '' ) {
                query = '?'+ query;
            }

            return _apiUrl + action + query;
        },

        post: function( actionUrl, callback, postData ) {
        
            var data = this.getData( postData );
			
			userFeedback.startTimer("post");

            var getTokenSuccessHandler = function(o) {
                    data[ 'formToken' ] = YAHOO.lang.JSON.parse(o.responseText).formToken;
                    YAHOO.util.Connect.asyncRequest('POST', actionUrl, callback, FD.Utils.urlEncode( data ));
            };

            var getTokenFailureHandler = function(o) {
                FD.Utils.checkResponse(o);
                //alert(o.status + " : " + o.statusText);
            };

            YAHOO.util.Connect.asyncRequest("GET", this.getActionUrl( 'gettoken' ), {
                    success: getTokenSuccessHandler,
                    failure: getTokenFailureHandler
            });
        },

    }
}

FD.UserFeedback = function() {
	
	return {
	
		startTimer: function(action) {
			if (action != 'services') {
				YAHOO.util.Dom.setStyle('spinner', 'display', 'inline');
				timer1 = setTimeout("userFeedback.displayLoading()", 1000);
			}
			
            /* 
            if (action == 'list') {
				timer2 = setTimeout("userFeedback.displayCancel()", 3000);
			}
            */
		},
		
		displayLoading: function() {
			YAHOO.util.Dom.setStyle('loadingTxt', 'display', 'inline');
		},
		
		/*
        displayCancel: function() {
			YAHOO.util.Dom.setStyle('cancelBtn', 'display', 'inline');
		},
        */
		
		stopTimer: function() {
			YAHOO.util.Dom.setStyle('spinner', 'display', 'none');
			YAHOO.util.Dom.setStyle('loadingTxt', 'display', 'none');
			//YAHOO.util.Dom.setStyle('cancelBtn', 'display', 'none');
			clearTimeout(timer1);
			clearTimeout(timer2);
		},
		
		displayFeedback: function(o) {
		
			myJSONdata = YAHOO.lang.JSON.parse(o.responseText);
			
			if (!myJSONdata.errorMsg && !myJSONdata.message) {
				return;
			}
						
		        var msg = "";
	
			if (myJSONdata.errorMsg) {
                            switch (typeof myJSONdata.errorMsg) {
                                case "string":
                                    msg = YAHOO.lang.dump(myJSONdata.errorMsg);
                                break;
                                case "object":
                                    // a hackish way to get the first error in the errorMsg object
                                    for (var type in myJSONdata.errorMsg) break;
                                    for (var code in myJSONdata.errorMsg[type]) break;
                                    msg = myJSONdata.errorMsg[type][code];
                                    break;
                            }
                        } else if (myJSONdata.message) {
				msg = YAHOO.lang.dump(myJSONdata.message);
			}

                        this.showFeedback(msg);
		},

                showFeedback: function(msg) {
                    if (msg != "") {
                        YAHOO.util.Dom.get('feedback').innerHTML = msg;
                        YAHOO.util.Dom.setStyle('feedback', 'display', 'block');
                    }
                },

		hideFeedback: function() {
                    YAHOO.util.Dom.get('feedback').innerHTML = "";
                    YAHOO.util.Dom.setStyle('feedback', 'display', 'none');
		}
	}
}

FD.Utils = {

    formatBytes: function(bytes)
    {
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
    },

    roundNum: function roundNum( num )
    {
        return Math.round( num * Math.pow( 10, 1 )) /
            Math.pow( 10, 1 );
    },

    urlEncode: function( data )
    {
        var query_parts = [];
        for ( var key in data ) {
            value = data[ key ];
            if ( typeof value == 'string' || typeof value == 'boolean' ) {
                query_parts.push( escape( key ) +'='+ escape( value ));
            } else if ( typeof value == 'object' ) {
                if ( value instanceof Array ) {
                    for ( var i in value ) {
                        query_parts.push( escape( key ) +'[]='+ escape( value[ i ] ));
                    }
                } else if ( value instanceof String ) {
                    query_parts.push( escape( key ) +'='+ escape( value ));
                } else {
                    console.warn( key +' is not a String or an Array' );
                }
            }
        }

        return query_parts.join( '&' );
    },

    pathConcat: function()
    {
        var sep = '/';

        var path = '';
        if ( arguments[ 0 ].charAt( 0 ) == sep ) {
            path = path.concat( sep );
        }

        var pathParts = [];
        var part;

        for ( var i = 0; i < arguments.length; i++ ) {
            part = arguments[ i ];
            if ( part.charAt( 0 ) == sep ) {
                part = part.slice( 1 );
            }
            if ( part.charAt( part.length - 1 ) == sep ) {
                part = part.slice( 0, part.length - 1 );
            }

            if ( part != '' ) {
                pathParts.push( part );
            }
        }

        return path.concat( pathParts.join( '/' ));
    },

    checkResponse: function(resp)
    {
        console.log(resp);
        switch (resp.status) {
            case 302:
            case 0:
                userFeedback.stopTimer()
                userFeedback.showFeedback('A problem occurred, try <a href="javascript: window.location.reload();">reloading</a>.');
                break;
        }
        return true;
    }
};

FD.cutCopyEvent = new YAHOO.util.CustomEvent('cutCopyEvent');

FD.DirList = function() {	

	var showHidden = false;
		
	formatDate = function(elCell, oRecord, oColumn, oData) {
		if (oData) {
			var oDate = new Date(oData*1000);
			var str = (oDate.getMonth() + 1) + '/' + oDate.getDate() + '/' + oDate.getFullYear();
		} else {
			str = "";
		}
		elCell.innerHTML = str;
	}
	
	formatBytes = function(elCell, oRecord, oColumn, oData)	{
		var bytes = oData;
		elCell.innerHTML = FD.Utils.formatBytes(bytes);
	}
	
	formatType = function(elCell, oRecord, oColumn, sData) {					
		elCell.innerHTML = '<img src="' + baseUrl + 'images/mime/small/' + sData + '.gif" />';
	};
    
    formatPerms = function(elCell, oRecord, oColumn, sData) {					
		//elCell.innerHTML = YAHOO.lang.dump(sData);
        var tempPerms = "";
        
        if (sData.read == true) {
            tempPerms += "R ";
        }
        if (sData.write == true) {
            tempPerms += "W ";
        }
        if (sData.delete == true) {
            tempPerms += "D ";
        }
        if (sData.lock == true) {
            tempPerms += "L ";
        }
        if (sData.admin == true) {
            tempPerms += "A";
        }
        elCell.innerHTML = tempPerms;
	};
	
	formatURL = function(elCell, oRecord, oColumn, sData) {	
		
		if (oRecord.getData("type") == "dir") {
			elCell.innerHTML = '<a id="folderLink">' + sData + '</a>';
		} else {
            elCell.innerHTML = '<a href="' + api.getActionUrl('download', {'path':FD.Utils.pathConcat(currentURL, sData)}, true) + '">' + sData + '</a>';
		}
	};
	
	handleFileSelection = function(oArgs) {
		var elCheckbox = oArgs.target;

		if (elCheckbox.checked === true) {
			var row = this.getTrEl(elCheckbox);
			this.selectRow(row);
		} else {
			var row = this.getTrEl(elCheckbox);
			this.unselectRow(row);
		}
	};
	
	handleTableClick = function(oArgs) {
					
		if (oArgs.target.id == "folderLink") {
			var newDir = FD.Utils.pathConcat( currentURL, oArgs.target.innerHTML );
			userFeedback.hideFeedback();
			History.navigate(newDir);
                }		
		
	};
	
	var myColumnDefs = [
		{key:"checked",label:"", width:"30", formatter:YAHOO.widget.DataTable.formatCheckbox},
		//{key:"type", sortable:true, resizeable:true},
		{key:"filename", label:"Name", formatter:formatURL, sortable:true, resizeable:true, editor: new YAHOO.widget.TextboxCellEditor({disableBtns:true})},
		{key:"modTime", label:"Last Modified", formatter:formatDate, sortable:true, sortOptions:{defaultDir:YAHOO.widget.DataTable.CLASS_DESC}},
		{key:"size", label:"Size", formatter:formatBytes, sortable:true, resizeable:true},
		{key:"mimeImage", label:"Type", sortable:true, resizeable:true, formatter:formatType},
		{key:"perms.read", label:"R", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatCheckbox},
        {key:"perms.write", label:"W", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatCheckbox},
        {key:"perms.delete", label:"D", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatCheckbox},
        {key:"perms.lock", label:"L", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatCheckbox},
        {key:"perms.admin", label:"A", sortable:true, resizeable:true, formatter:YAHOO.widget.DataTable.formatCheckbox},
		//{key:"mimeType", sortable:true, resizeable:true}
	];
	
	var getAjaxListCallback = function(oTable) {
	
		var tableState = oTable.getState();
	        var generalCallback =function (o) {

                    console.log(o);
                    if ( ! FD.Utils.checkResponse(o)) {
                        return false;
                    }

                    userFeedback.displayFeedback(o);

                    //myDataSource.responseSchema = responseSchema;
                    myDataSource.doBeforeCallback = hiddenFileFilter;

                    //tableState.sortedBy = tableInitialSort;

                    myDataSource.sendRequest( api.getActionUrl( 'list' ), {
                            success  : oTable.onDataReturnInitializeTable,
                            failure  : oTable.onDataReturnInitializeTable,  // add errorHandling
                            scope    : oTable,
                            argument : tableState
                    }); 	
                };
		return callback = {
			success: generalCallback,
                        failure: generalCallback,
			timeout: 3000
		};
	};
		
	var hiddenFileFilter = function (req, raw, res, cb) {

		var data     = res.results || [],
		filtered = [],
		i,
		l;

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
	
	var clearTable = function() {
		dirTable.unselectAllRows();
		rows = dirTable.getRecordSet().getRecords();
		for (i=0; i < rows.length; i++) {
			dirTable.getRecordSet().updateKey(rows[i], "checked", "");
		}
		dirTable.render();
	};
		
	
	return {
		init: function(bookmarkDir, bookmarkService) {

			myDataSource = new YAHOO.util.DataSource( '' );  // first call
			myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON; 
			myDataSource.responseSchema = {
				resultsList: "contents",
				fields: ["type","filename","modTime","size","mimeImage","perms.read","perms.write","perms.delete","perms.lock","perms.admin","mimeType"]
			};

							
			myDataSource.doBeforeCallback = hiddenFileFilter;
								
			myDataSource.subscribe('dataErrorEvent', function(oDS){  // triggered by data return error
                                FD.Utils.checkResponse(oDS.response);
                        });

			myDataSource.subscribe('responseEvent', function(oDS){  // triggered by data return
			
				userFeedback.stopTimer();
				
				userFeedback.displayFeedback(oDS.response);
				
				myJSONdata = YAHOO.lang.JSON.parse(oDS.response.responseText);
				currentURL = YAHOO.lang.dump(myJSONdata.path);
                                api.setUrlParam( 'path', currentURL );
				currentService = YAHOO.lang.dump(myJSONdata.service);
                                api.setUrlParam( 'service', currentService );
								
				if (!homeURL) {
					homeURL = currentURL;
				}
				
				//folderPerms = YAHOO.lang.dump(myJSONdata.contents[0].perms);
                //console.warn("folderPerms = " + folderPerms);
                folderPerms = myJSONdata.contents[0].perms;               
				
				var currentLocationPath = YAHOO.util.Dom.get('currentLocationPath'),
				changeLocationNewPath = YAHOO.util.Dom.get('changeLocationNewPath'),
				viewHTML,
				locationParts = currentURL.split("/"),
				linkPaths = [],
				locLinks = "";
				
				locationParts.splice(0,1);
				
				//chop up location and rebuild as links w path info here.
				for (i=0; i < locationParts.length; i++) {
					var linkTemp =[];
					for (j=0; j <= i; j++) {
						linkTemp += "/" + locationParts[j];
					}
					locLinks += '/ <a href="#" id="' + linkTemp + '">' + locationParts[i] + '</a> ';
				}
				
				currentLocationPath.innerHTML = locLinks;
                                changeLocationNewPath.value = currentURL;
                                infoBar.setService( myJSONdata.service );
			}
			);	
			
                        if ( bookmarkService ) {
							infoBar.setService(bookmarkService);
						}
						
						if ( bookmarkDir ) {
                            var params = { 'path': bookmarkDir };
                        } else {
                            var params = {};
                        }
                        var initReq = api.getActionUrl( 'list', params, true );

					
			dirTable = new YAHOO.widget.DataTable("content", myColumnDefs, myDataSource, {initialRequest:initReq});
			userFeedback.startTimer("list");
			
			dirTable.subscribe('checkboxClickEvent', handleFileSelection);
			dirTable.subscribe('click', handleTableClick);
            
            // hides permission columns
            dirTable.hideColumn("perms.read");
            dirTable.hideColumn("perms.write");
            dirTable.hideColumn("perms.delete");
            dirTable.hideColumn("perms.lock");
            dirTable.hideColumn("perms.admin");
			
			return dirTable;
		},
		
		toggleHiddenFilter: function(e, action) {
        
            console.warn("toggleHiddenFilter entered");
	
			if (action != 'showHidden') {
				return;
			}

			var tableState = dirTable.getState();

			showHidden = ! showHidden;			

			var l = document.getElementById( 'hidnFilesCtrl' );
				if ( showHidden ) {
				var t = document.createTextNode( 'Hide Hidden Files' );
				l.replaceChild( t, l.firstChild );
			} else {
				var t = document.createTextNode( 'Show Hidden Files' );
				l.replaceChild( t, l.firstChild );
			}
			
			myDataSource.sendRequest( api.getActionUrl( 'list' ), dirTable.onDataReturnInitializeTable, dirTable);
			userFeedback.startTimer("list");
			
			/*						
			myDataSource.sendRequest(showHidden,{
				success  : dirTable.onDataReturnInitializeTable,
				failure  : dirTable.onDataReturnInitializeTable,
				scope    : dirTable,
				argument : tableState
			});*/
		},
		
		deleteItems: function(e, action) {
			if (action != 'del') {
				return;
			}

			var tableState = dirTable.getState();
			var callback = getAjaxListCallback(dirTable);	
		
			var files = [];

			for (var i=0; i < tableState.selectedRows.length; i++) {
				files.push(dirTable.getRecord(tableState.selectedRows[i]).getData().filename);
			}

			api.post( api.getActionUrl( 'delete' ), callback, { 'files': files, 'path': currentURL } );				
		
		},

		createNewFolder: function(e, args) {
                        var callback = getAjaxListCallback(dirTable);
                        api.post( api.getActionUrl( 'mkdir' ), callback, { 'folderName': args[ 0 ], 'path': currentURL } );
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

			var td = trs[0].getElementsByTagName('td')[1];
						
			dirTable.showCellEditor(td);
		},
		
		handleNameEditorSave: function(oArgs) {
		
			var callback = getAjaxListCallback(this);			
			api.post( api.getActionUrl( 'rename' ), callback, { 'oldName': oArgs.oldData, 'newName':oArgs.newData, 'path': currentURL } );		

		},
		
		cutCopyItems: function(e, action) {
			if (action == 'copy') {
				clipboardState = "copy";
			} else if (action == 'cut') {
				clipboardState = "cut";
			} else {
				return;
			}
			
			var tableState = dirTable.getState();
			var callback = getAjaxListCallback(dirTable);
			
			cutCopyFiles = [];

			for (var i=0; i < tableState.selectedRows.length; i++) {
				cutCopyFiles.push(dirTable.getRecord(tableState.selectedRows[i]).getData().filename);
			}
			
			YAHOO.util.Dom.get('feedback').innerHTML = "Added " + tableState.selectedRows.length + " item(s) to the clipboard.";
			YAHOO.util.Dom.setStyle('feedback', 'display', 'block');
			
			cutCopyURL = currentURL;
			
			clearTable();
						
			FD.cutCopyEvent.fire();
						
		},
		
		pasteItems: function(e, action) {
			if (action != 'paste') {
				return;
			}			
			
			var callback = getAjaxListCallback(dirTable);
				
			if (clipboardState == "cut") {
				var pasteAction = 'move';
			} else if (clipboardState == "copy") {
				var pasteAction = 'copy';
			} else {
				alert("no clipboardState available");
			}
			
			api.post( api.getActionUrl( pasteAction, { 'path': cutCopyURL }, true ), callback, { 'files': cutCopyFiles, 'fromPath': cutCopyURL, 'toPath': currentURL} );
			
			cutCopyFiles = [];
			cutCopyURL = "";
				
			FD.cutCopyEvent.fire();
			
		},
		
		reqSender: function(directory) {
            params = { 'path': directory };
			myDataSource.sendRequest( api.getActionUrl( 'list', params, true ), dirTable.onDataReturnInitializeTable, dirTable);
			userFeedback.startTimer("list");			
		},
							
		/*
		var myCallback = function() {
			dirTable.onDataReturnAppendRows(arguments);
			alert("callback called.");
		};
		
		var callback1 = {
			success : myCallback,
			failure : myCallback,
			//scope : myDataTable
		};
		*/
		
		//myDataSource.sendRequest("", callback1);

						
		
		/*
		return {
			init: function() {
				//dirTable = new YAHOO.widget.DataTable("content", myColumnDefs, myDataSource);
				//return dirTable;
			},
		}
		*/
	}
	
}

FD.InfoBar = function() {

	var currentDir,
	locationDiv = YAHOO.util.Dom.get('location'),
    currentLocation = YAHOO.util.Dom.get( 'currentLocation' ),
    changeLocation  = YAHOO.util.Dom.get( 'changeLocation' ),
	viewHTML;

	var update = function(oArgs) {};

	var handleClick = function(e) {
	
		YAHOO.util.Event.preventDefault(e);
	
		var target = YAHOO.util.Event.getTarget(e);
		
		if (target.id == "goUp") {
			var upDir = currentURL.slice( 0, currentURL.lastIndexOf("/") );
			userFeedback.hideFeedback();
			History.navigate(upDir);
		} else if (target.id == "refresh") {
			userFeedback.hideFeedback();
			myDataSource.sendRequest( api.getActionUrl( 'list' ), dirTable.onDataReturnInitializeTable, dirTable);
			userFeedback.startTimer("list");
		}		
/*
		if ( ! target.value) {
			return;
		}
*/

		if (target.id == 'currentLocationChange') {
            YAHOO.util.Dom.setStyle( currentLocation, 'display', 'none' );
            YAHOO.util.Dom.setStyle( changeLocation, 'display', 'inline' );
		} else if (target.id == 'changeLocationGo') {
			YAHOO.util.Dom.setStyle( currentLocation, 'display', 'inline' );
            YAHOO.util.Dom.setStyle( changeLocation, 'display', 'none' );
            setService( YAHOO.util.Dom.get( 'changeLocationNewService' ).value );
			userFeedback.hideFeedback();
			
            History.navigate(YAHOO.util.Dom.get('changeLocationNewPath').value, YAHOO.util.Dom.get('changeLocationNewService').value);
			
		} 
            
            else if (target.id == 'changeLocationCancel') {
            YAHOO.util.Dom.setStyle( currentLocation, 'display', 'inline' );
            YAHOO.util.Dom.setStyle( changeLocation, 'display', 'none' );
		}
            
	};

	var handleSubmit = function(e) {
		YAHOO.util.Event.preventDefault(e);
	};
	
	var locationClick = function(e) {
		YAHOO.util.Event.preventDefault(e);
		if (e.target.href) {
			userFeedback.hideFeedback();
			History.navigate(e.target.id);
		}
	};

        var serviceChange = function(e) {
            YAHOO.util.Event.preventDefault(e);
            YAHOO.util.Dom.get('changeLocationNewPath').value = services.contents[e.target.value].home;
        };

        var setService = function( service ) {
            // workaround for no value on history.register
            if (typeof services.contents != 'undefined') {
                YAHOO.util.Dom.get( 'currentLocationService' ).innerHTML = services.contents[ service ].label;
            }
            //YAHOO.util.Dom.get( 'currentLocationService' ).innerHTML = service;
            api.setUrlParam( 'service', service );
        };
	YAHOO.util.Event.on('infoBar', 'click', handleClick);
	YAHOO.util.Event.on('location', 'submit', handleSubmit);
	YAHOO.util.Event.on('notifyArea', 'click', locationClick);
    YAHOO.util.Event.on('changeLocationNewService', 'change', serviceChange);

	var kl = new YAHOO.util.KeyListener(document, {keys:13}, {fn:function(){alert('enter');}});

	return {
		init: function(crntDir) {
			currentDir = crntDir;
		},
        setServiceOptions: function( services ) {
            var serviceOptionsHtml = '<label for="changeLocationNewService">Service: </label><select name="changeLocationNewService" id="changeLocationNewService">';
            for ( var id in services.contents ) {
                serviceOptionsHtml += '<option value="'+ id +'">'+ services.contents[ id ].label +'</option>';
            }
            serviceOptionsHtml += '</select>';
            YAHOO.util.Dom.get('setServicesWrapper').innerHTML = serviceOptionsHtml;
        },
        setService:setService,
		update:update
	}
};	

FD.PermissionsDialog = function() {

	var evnt = new YAHOO.util.CustomEvent("Permissions Event");

	var hide = function() {
		var newPermissionsDialog = YAHOO.util.Dom.get('permissions');
		YAHOO.util.Dom.setStyle(newPermissionsDialog, 'display', 'none');
		// reenable when dialog becomes a form
        //newPermissionsDialog.reset();
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
		// TO DO update line below to gather and send new data
        //evnt.fire(target.folderName.value);
		hide();
		FD.InspDialogCloseEvent.fire();
	};

	YAHOO.util.Event.on('permissions', 'click', handleClick);
	YAHOO.util.Event.on('permissions', 'submit', handleSubmit);
	

	return {
		show: function(e, action) {
        
            if (action != 'permissions') {
                return;
            }
            
            console.warn("permissionsDialog.show");
            
            FD.InspDialogCloseEvent.fire();
            
          YAHOO.util.Dom.setStyle('upload', 'display', 'none');
          YAHOO.util.Dom.setStyle('newFolder', 'display', 'none');
          YAHOO.util.Dom.setStyle('permissions', 'display', 'block');
          //document.getElementById('newFold').focus(); 
                                  
        },

		evnt:evnt
	}
}();   //END FD.PermissionsDialog

FD.NewFolderDialog = function() {

	var evnt = new YAHOO.util.CustomEvent("New Folder Event");

	var hide = function() {
		var newFolderForm = YAHOO.util.Dom.get('newFolder');
		YAHOO.util.Dom.setStyle(newFolderForm, 'display', 'none');
		newFolderForm.reset();
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
		hide();
		FD.InspDialogCloseEvent.fire();
	};

	// improved this function (imho) to set focus in the text box when 'create new folder'
	// is clicked and highlights instruction text for easy delete/overwrite with new folder name - cwL
	
	/*
	var handleFocus = function(e) {
		var target = YAHOO.util.Event.getTarget(e);
		target.value = 'enter name';
	};
	*/

	// YAHOO.util.Event.on('newFold', 'focus', handleFocus);
	YAHOO.util.Event.on('newFolder', 'click', handleClick);
	YAHOO.util.Event.on('newFolder', 'submit', handleSubmit);
	

	return {
		show: function(e, action) {
        
            if (action != 'createFolder') {
                return;
            }
            
            FD.InspDialogCloseEvent.fire();
            
          YAHOO.util.Dom.setStyle('upload', 'display', 'none');
          YAHOO.util.Dom.setStyle('permissions', 'display', 'none');
          YAHOO.util.Dom.setStyle('newFolder', 'display', 'block');
          document.getElementById('newFold').focus(); 
                                  
        },

		evnt:evnt
	}
}();   //END FD.NewFolderDialog

FD.UploadDialog = function() {

    var overwrite = false;
    var evnt = new YAHOO.util.CustomEvent("Upload Event");
    
    var hide = function() {
		var newUploadForm = YAHOO.util.Dom.get('upload');
		YAHOO.util.Dom.setStyle(newUploadForm, 'display', 'none');
		newUploadForm.reset();
	};
    
    var handleClick = function(e) {
        var target = YAHOO.util.Event.getTarget(e);

        if (target.id == 'overwrite') {
            overwrite = target.checked;
        }

        if ( ! target.href) {
            return;
        }

        //YAHOO.util.Event.preventDefault(e);
        //hide();
        //FD.InspDialogCloseEvent.fire();
    };
    
    YAHOO.util.Event.on('upload', 'click', handleClick);

    return {
        init: function() {
            $("#uploader").pluploadQueue({
                runtimes : 'html5,gears,html4',
                multipart: false,
            });

            // Client side form validation
            $('#upload').submit(function(e) {
                var uploader = $('#uploader').pluploadQueue();

                // Files in queue upload them first
                if (uploader.files.length > 0) {
                    // When all files are uploaded submit form
                    uploader.bind('StateChanged', function() {
                        if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                            $('form')[0].submit();
                        }
                    });

                    uploader.start();
                } else {
                        alert('You must queue at least one file.');
                }

                return false;
            });

            var uploader = $('#uploader').pluploadQueue();
            uploader.bind('BeforeUpload', function(up, file){
		//check status of overwrite before upload
		uploader.settings.url = api.getActionUrl('upload') + "&overwrite="+overwrite;
            });
        },

        show: function(e, action) {

            if (action != 'upload') {
                return;
            }

            YAHOO.util.Dom.setStyle('upload', 'visibility', 'visible');
            YAHOO.util.Dom.setStyle('upload', 'display', 'block');

            return;
       //============== 
          var settings = {
            runtimes : 'html5,html4',
            multipart: false,
            };
          var uploader = new plupload.Uploader(settings);
          // initialize to get features object
          uploader.init();

          if (!uploader.features.html5) {
              settings.multipart = true;
              settings.runtimes = 'html5,html4';
          }

          // now create the real instance with all settings
          settings.browse_button = 'pickfiles';
          uploader = new plupload.Uploader(settings);

          uploader.bind('Init', function(up, params) {
                  YAHOO.util.Dom.get('filelist').innerHTML = "<div>Current runtime: " + params.runtime + "</div>";
                  });

          uploader.bind('FilesAdded', function(up, files) {
                  for (var i in files) {
                  YAHOO.util.Dom.get('filelist').innerHTML += '<div id="' + files[i].id + '">' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
                  }
                  });

          uploader.bind('UploadFile', function(up, file) {
                  YAHOO.util.Dom.get('upload').innerHTML += '<input type="hidden" name="file-' + file.id + '" value="' + file.name + '" />';
                  });

          uploader.bind('BeforeUpload', function(up, file){
		//check status of overwrite before upload
		uploader.settings.url = api.getActionUrl('upload') + "&overwrite="+overwrite;
          });

/* After file has uploaded check server side response, if errorcode exists 
print errormsg and set file.percent to 0 and status to 3,
so that it shows in progress bar.
*/

		uploader.bind('FileUploaded', function(up, file, response) {
			var myJSONdata = YAHOO.lang.JSON.parse(response.response);
			if ( myJSONdata.error ) {
				file.status = 3;
				file.percent = 0;
				alert("'" + file.name + "' : " + myJSONdata.error.message);
			}
		});

		uploader.bind('error', function(up, err) {
			alert("Upload error: " + err.message);
		});

		uploader.bind('UploadProgress', function(up, file, response) {
                  YAHOO.util.Dom.get(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
                  });

          uploader.bind('UploadComplete', function(up, files) {
                  userFeedback.hideFeedback();
                  myDataSource.sendRequest( api.getActionUrl( 'list' ), dirTable.onDataReturnInitializeTable, dirTable);
                  userFeedback.startTimer("list");
                  YAHOO.util.Dom.setStyle('upload', 'visibility', 'hidden');
                  FD.InspDialogCloseEvent.fire();
                  });

          YAHOO.util.Dom.get('uploadfiles').onclick = function() {
              uploader.start();
              return false;
          };

          uploader.init();
          YAHOO.util.Dom.setStyle('upload', 'visibility', 'visible');
          YAHOO.util.Dom.setStyle('upload', 'display', 'block');

   
        },

		evnt:evnt
	}

}();

FD.InspDialogCloseEvent = new YAHOO.util.CustomEvent('InspDialogCloseEvent');

FD.FileInspector = function() {

	var currentDir,
	actions = {},
	temp = YAHOO.util.Dom.get('actionList'),
	evnt = new YAHOO.util.CustomEvent("Inspector Event"),
	links = temp.getElementsByTagName('a'),
	i;
	
	YAHOO.util.Dom.get('versionNumber').innerHTML = webAppVersion;
	
	// called upon instantiation.
	for (i=0; i<links.length; i++) {
		action = links[i].hash.match(/#(.*)$/);  // converts link to an array, with original in index 0 and #-less in index 1
		actions[action[1]] = {ref: links[i]};  // actions becomes an array of objects with char indexes based on action, and links to that action.  example:  actions["cut"] holds href obj for cut
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

		if (selectedRows.length < 1000) {
			itemInfoContent += '<ul>';
			for (var i=0; i< filenames.length; i++) {
				itemInfoContent += '<li>' + filenames[i] + '</li>';
			}
			itemInfoContent += '</ul>';
		}

		itemInfo.innerHTML = itemInfoContent; 
	};
	
	// makes menu options active or inactive based on the permissions
	var update = function(oArgs) {
		
		var numSelected,
		i,
		selectionPerms = {
			r: false,   // read
			w: false,   // write
			d: false,   // delete
			l: false,   // lock
			a: false    // admin
		};

		if (oArgs) {
            numSelected = dirTable.getSelectedRows().length;
			updateItemInfo.apply(dirTable, [dirTable.getSelectedRows()]);
            
            selectedRows = dirTable.getSelectedRows();
            
            // read
            for (i=0; i < numSelected; i++) {
                if (dirTable.getRecord(selectedRows[i])._oData["perms.read"] == true) {
                    selectionPerms.r = true;
                } else {
                    selectionPerms.r = false;
                    break;
                }
            }
            
            // write
            for (i=0; i < numSelected; i++) {
                if (dirTable.getRecord(selectedRows[i])._oData["perms.write"] == true) {
                    selectionPerms.w = true;
                } else {
                    selectionPerms.w = false;
                    break;
                }
            }
            
            // delete
            for (i=0; i < numSelected; i++) {
                if (dirTable.getRecord(selectedRows[i])._oData["perms.delete"] == true) {
                    selectionPerms.d = true;
                } else {
                    selectionPerms.d = false;
                    break;
                }
            }
            
            // lock
            for (i=0; i < numSelected; i++) {
                if (dirTable.getRecord(selectedRows[i])._oData["perms.lock"] == true) {
                    selectionPerms.l = true;
                } else {
                    selectionPerms.l = false;
                    break;
                }
            }
            
            // admin
            for (i=0; i < numSelected; i++) {
                if (dirTable.getRecord(selectedRows[i])._oData["perms.admin"] == true) {
                    selectionPerms.a = true;
                } else {
                    selectionPerms.a = false;
                    break;
                }
            }
            
		} else {
			numSelected = 0;
			updateItemInfo();
		}    
		
		filesSelected = (numSelected > 0) ? true : false;

                switch (currentService) {
                    // these service specific tests are a work around until the API can normalize permissions for the interface
                    case 'ifs':
                        YAHOO.util.Dom.setStyle(actions.upload.ref.parentNode, 'display', 'inline');
                        YAHOO.util.Dom.setStyle(actions.copy.ref.parentNode, 'display', 'inline');
                        YAHOO.util.Dom.setStyle(actions.permissions.ref.parentNode, 'display', 'inline');

                        // Set actions
                        if (folderPerms.write) {
                            YAHOO.util.Dom.addClass(actions.upload.ref, 'enabled');
                            YAHOO.util.Dom.addClass(actions.createFolder.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.upload.ref, 'enabled');
                            YAHOO.util.Dom.removeClass(actions.createFolder.ref, 'enabled');
                        }

                        if (filesSelected && selectionPerms.r && selectionPerms.d) {
                            YAHOO.util.Dom.addClass(actions.cut.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.cut.ref, 'enabled');
                        }

                        if (filesSelected && selectionPerms.r) {
                            YAHOO.util.Dom.addClass(actions.copy.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.copy.ref, 'enabled');
                        }

                        if (filesSelected && selectionPerms.d) {
                            YAHOO.util.Dom.addClass(actions.del.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.del.ref, 'enabled');
                        }

                        if (filesSelected && numSelected < 2 && selectionPerms.r && selectionPerms.w) {
                            YAHOO.util.Dom.addClass(actions.rename.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.rename.ref, 'enabled');
                        }

                        // paste condition
                        if (folderPerms.write && cutCopyURL) {
                            YAHOO.util.Dom.addClass(actions.paste.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.paste.ref, 'enabled');
                        }

                        // set permissions for folder
                        // TO DO: add condition which checks if user has rights to modify permissions
                        if ( numSelected == 1 && dirTable.getRecord(selectedRows[0])._oData["type"] == "dir" ) {
                            YAHOO.util.Dom.addClass(actions.permissions.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.permissions.ref, 'enabled');
                        }
                        break;
                    case 'mainstreamStorage':
                        YAHOO.util.Dom.addClass(actions.createFolder.ref, 'enabled');

                        YAHOO.util.Dom.removeClass(actions.upload.ref, 'enabled');
                        YAHOO.util.Dom.removeClass(actions.copy.ref, 'enabled');
                        YAHOO.util.Dom.removeClass(actions.permissions.ref, 'enabled');

                        YAHOO.util.Dom.setStyle(actions.upload.ref.parentNode, 'display', 'none');
                        YAHOO.util.Dom.setStyle(actions.copy.ref.parentNode, 'display', 'none');                      
                        YAHOO.util.Dom.setStyle(actions.permissions.ref.parentNode, 'display', 'none');

                        if (filesSelected) {
                            YAHOO.util.Dom.addClass(actions.cut.ref, 'enabled');
                            YAHOO.util.Dom.addClass(actions.del.ref, 'enabled');
                            YAHOO.util.Dom.addClass(actions.rename.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.cut.ref, 'enabled');
                            YAHOO.util.Dom.removeClass(actions.del.ref, 'enabled');
                            YAHOO.util.Dom.removeClass(actions.rename.ref, 'enabled');
                        }
                        if (!filesSelected && cutCopyURL) {
                            YAHOO.util.Dom.addClass(actions.paste.ref, 'enabled');
                        } else {
                            YAHOO.util.Dom.removeClass(actions.paste.ref, 'enabled');
                        }
                        break;
                }
        };
	
	clearSelection = function(e) {
		YAHOO.util.Dom.removeClass(links, 'inspSelected');
	};
	
	var handleClick = function(e) {

		var target = YAHOO.util.Event.getTarget(e);
            
            if (!YAHOO.util.Dom.hasClass(target, 'enabled')) {
                YAHOO.util.Event.preventDefault(e);
            } else if (target.href) {
			YAHOO.util.Event.preventDefault(e);
			var action = target.hash.match(/#(.*)$/);

			if (YAHOO.util.Dom.hasClass(target, 'expandable')) {
				YAHOO.util.Dom.addClass(target, 'inspSelected');
			}

                console.warn(action[1]);
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
	
}

FD.Favorites = function() {

    var thisCurrentFav,
    thisChangeFav,
    alreadyAdding = false;
	
	myFavsSource = new YAHOO.util.DataSource("webservices/favorites/");
	
	// doesn't seem like this stuff is needed for 'list', but perhaps once Favs gets more complex...
	/*	
		myFavsSource.responseType = YAHOO.util.DataSource.TYPE_JSON; 
		myFavsSource.responseSchema = {
			fields: ["service","name","path"]
		};
	*/
	
	myFavsSource.subscribe('responseEvent', function(oDS){
        
        var thisCurrentFav,
        thisChangeFav;
		
		myFavs = YAHOO.lang.JSON.parse(oDS.response.responseText);        
				
		var linksListHTML = '<ul>';	
                for ( var key in myFavs.services) {
                    var obj = myFavs.services[key];
                    var keyValue = services.contents[key].label;
                    
                    if ( obj != null){
                        
                        linksListHTML += '<li><a id="homeLink" href="' + services.contents[key].home + '">' + keyValue + '</a></li><div id="' + key + '">';
                        
                        for ( i=0; i < obj.count; i++ ) {
                        
                            linksListHTML += '<li><form id="changeFav"><input type="text" name="editFav" id="editFav" size="10" value="' + obj.contents[i].name + '"/></form>';
                            linksListHTML += '<span id="currentFav"><a id="folderLink" href="' + obj.contents[i].path + '">' + obj.contents[i].name + '</a>';
                            linksListHTML += '<span id="editFavBtns">&nbsp;&nbsp;<a href="#edit"><img src="images/pencil.png" id="editBtn" /></a>&nbsp;<a href="#delete"><img src="images/delete.png" id="deleBtn"/></a></span></span></li>'; 
                            
                        }
                        
                        linksListHTML += '</div>';
                     
                    } 
                 
                 }

                linksListHTML += '</ul>';
		
		YAHOO.util.Dom.get('favsLinks').innerHTML = linksListHTML;
	});
    
    var getFavsListCallback = function() {
	
		return callback = {
			success: function (o) {
                
                userFeedback.displayFeedback(o);
                
                userFeedback.stopTimer();
                
                myFavsSource.sendRequest( "list?format=json" );
                
                /*
				myFavsSource.sendRequest( api.getActionUrl( 'list' ), {
					success  : oTable.onDataReturnInitializeTable,
					failure  : oTable.onDataReturnInitializeTable,  // add errorHandling
					scope    : oTable,
					argument : tableState
				});
                */
			},

			timeout: 3000
		};
	};
    
    var editFav = function(target) {
    
        if (thisCurrentFav!='undefined') {
            YAHOO.util.Dom.setStyle( thisCurrentFav, 'display', 'inline' );
            YAHOO.util.Dom.setStyle( thisChangeFav, 'display', 'none' );
        }
    
        userFeedback.hideFeedback();
    
        if (target.id == "editBtn") {
        
            klEsc.enable();
        
            thisCurrentFav = target.parentNode.parentNode.parentNode;
            thisChangeFav = target.parentNode.parentNode.parentNode.previousSibling;
                        
            YAHOO.util.Dom.setStyle( thisCurrentFav, 'display', 'none' );
            YAHOO.util.Dom.setStyle( thisChangeFav, 'display', 'inline' );
            
            YAHOO.util.Event.on(thisChangeFav, 'submit', handleEditFavChange);
            YAHOO.util.Event.on(thisChangeFav, 'cancel', handleEditFavCancel);
            
        } else if (target.id == "deleBtn") {
            thisCurrentFav = target.parentNode.parentNode.parentNode;    
            var favName = target.parentNode.parentNode.parentNode.firstChild.innerHTML;
            var params = { 'format': 'json', 'path': thisCurrentFav.firstChild.href, 'service': thisCurrentFav.parentNode.parentNode.id, 'wappver': webAppVersion.toString() }; 
            var callback = getFavsListCallback();			
            api.post( api.getActionUrl( 'favorites/delete',params ), callback, { 'folderName': favName } );
            
        } else if (target.id == "addBtn") {
        
            if (!alreadyAdding) {
            
                alreadyAdding = true;
                
                var linksListHTML = YAHOO.util.Dom.get('favsLinks').innerHTML;
                
                // trims off the last </ul> of the favs list
                linksListHTML = linksListHTML.substr(0, linksListHTML.length - 5);
                
                // grabs the highest folder from the current path
                lastFolder = currentURL.substr(currentURL.lastIndexOf("/")+1);
                
                linksListHTML += '<li><form id="newFavForm"><input type="text" name="newFav" id="newFav" size="10" value="' + lastFolder + '"/></form></li></ul>';
                YAHOO.util.Dom.get('favsLinks').innerHTML = linksListHTML;
                
                document.getElementById("newFav").focus();
                
                YAHOO.util.Event.on("newFavForm", 'submit', handleAddFav);
                YAHOO.util.Event.on("newFavForm", 'focusout', handleAddFavCancel);
                klEscAdd.enable();
                
            }
        }
    }
    
    var handleEditFavChange = function(e) {
        YAHOO.util.Event.preventDefault(e);
        klEsc.disable();
        
        oldName = thisCurrentFav.firstChild.innerHTML;
        newName = thisChangeFav.firstChild.value;
       
        var params = { 'format': 'json', 'path': thisCurrentFav.firstChild.href, 'service': thisCurrentFav.parentNode.parentNode.id, 'wappver': webAppVersion.toString() };
 
        var callback = getFavsListCallback();			
		api.post( api.getActionUrl( 'favorites/rename',params), callback, { 'oldName': oldName, 'newName': newName } );
        
        YAHOO.util.Dom.setStyle( thisCurrentFav, 'display', 'inline' );
        YAHOO.util.Dom.setStyle( thisChangeFav, 'display', 'none' );
        
    }
    
    var handleEditFavCancel = function(e) {
        YAHOO.util.Event.preventDefault(e);
        klEsc.disable();
        
        YAHOO.util.Dom.setStyle( thisCurrentFav, 'display', 'inline' );
        YAHOO.util.Dom.setStyle( thisChangeFav, 'display', 'none' );
    }
    
    var handleAddFav = function(e) {
        YAHOO.util.Event.preventDefault(e);
        var newFavName = document.getElementById("newFav").value;
        var callback = getFavsListCallback();	
        api.post( api.getActionUrl( 'favorites/add' ), callback, { 'folderName': newFavName, 'path': currentURL } );
        klEscAdd.disable();
        alreadyAdding = false;
    };
    
    var handleAddFavCancel = function(e) {
        
        YAHOO.util.Event.preventDefault(e);
        klEscAdd.disable();
        
        var linksListHTML = YAHOO.util.Dom.get('favsLinks').innerHTML;
                
        // trims off the last <li> to the end of the favs list
        linksListHTML = linksListHTML.substr(0, linksListHTML.lastIndexOf("<li>"));
        linksListHTML += '</ul>';
        YAHOO.util.Dom.get('favsLinks').innerHTML = linksListHTML;
        alreadyAdding = false;
    };

	myFavsSource.sendRequest( "list?format=json" );
    
    var klEsc = new YAHOO.util.KeyListener(document, { keys:27 }, { fn:handleEditFavCancel } );
    var klEscAdd = new YAHOO.util.KeyListener(document, { keys:27 }, { fn:handleAddFavCancel } );
    
    return {
        editFav:editFav
    }
}

FD.History = function()
{
    var pathStateChangeHandler = function(state)
    {
        dirList.reqSender(state);
    };

    var serviceStateChangeHandler = function(state)
    {
        infoBar.setService(state);
    };

    return {
        init: function()
        {
            var pathInitialState = YAHOO.util.History.getBookmarkedState("path") || services.contents[services.defaultService].home;
            var serviceInitialState = YAHOO.util.History.getBookmarkedState("service") || services.defaultService;

            YAHOO.util.History.register('service', serviceInitialState, serviceStateChangeHandler);
            YAHOO.util.History.register('path', pathInitialState, pathStateChangeHandler);
            YAHOO.util.History.initialize("yui-history-field", "yui-history-iframe");

            dirTable = dirList.init(pathInitialState, serviceInitialState);
            dirTable.subscribe('rowSelectEvent', inspector.update);
            dirTable.subscribe('rowUnselectEvent', inspector.update);
            dirTable.subscribe('initEvent', inspector.update);
            dirTable.subscribe('editorSaveEvent', dirList.handleNameEditorSave);
            //stops dirTable "Data error." behavior when path does not exist
            dirTable.doBeforeLoadData = function(oRequest, oResponse, oPayload) {
                if (!oResponse.error) {
                    return true;
                }
            };
        },
        navigate: function(path, service)
        {
            if (typeof path == 'undefined' ) {
                path = YAHOO.util.History.getCurrentState('path');
            }

            if (typeof service == 'undefined' ) {
                service = YAHOO.util.History.getCurrentState('service');
            }

            YAHOO.util.History.multiNavigate({"service": service, "path": path});
        } 
    };
};

FD.init = function()
{
    infoBar = new FD.InfoBar();
    api = new FD.api();
    dirList = new FD.DirList();
    userFeedback = new FD.UserFeedback();
    History = new FD.History();

    FD.UploadDialog.init();

    inspector = new FD.FileInspector();

    inspector.evnt.subscribe(dirList.toggleHiddenFilter);
    inspector.evnt.subscribe(dirList.deleteItems);
    inspector.evnt.subscribe(dirList.cutCopyItems);
    inspector.evnt.subscribe(dirList.pasteItems);
    inspector.evnt.subscribe(dirList.renameItem);
    inspector.evnt.subscribe(FD.NewFolderDialog.show);
    inspector.evnt.subscribe(FD.UploadDialog.show);
    inspector.evnt.subscribe(FD.PermissionsDialog.show);

    var callback = {
        'success': function( o ) {
            services = YAHOO.lang.JSON.parse(o.responseText).services;
            infoBar.setServiceOptions( services );
            //favorites is now dependent on services data return
            favorites = new FD.Favorites();	
            History.init();
        }
    };

    YAHOO.util.Connect.asyncRequest('GET', api.getActionUrl( 'services' ), callback, null );
};

YAHOO.util.Event.addListener(window, "load", function() {

        FD.init();
	
	FD.cutCopyEvent.subscribe(inspector.update);
	
	// TODO add errorChecking to myDataSource.doBeforeCallback = hiddenFileFilter
	// to catch error on initial load, where path in URL doesn't exist.
	
	FD.NewFolderDialog.evnt.subscribe(dirList.createNewFolder);
	
	FD.InspDialogCloseEvent.subscribe(inspector.clearSelection);
	
	YAHOO.util.Event.on('homeBtn', 'click', function(e) {
		YAHOO.util.Event.preventDefault(e);
		userFeedback.hideFeedback();
		History.navigate(homeURL, 'ifs');
	});	
	
	YAHOO.util.Event.on('favsList', 'click', function(e) {
        YAHOO.util.Event.preventDefault(e);
        if (e.target.id == "folderLink") {
                userFeedback.hideFeedback();
                thisService = e.target.parentNode.parentNode.parentNode.id;
                thisPath = e.target.getAttribute("href");
                History.navigate(thisPath, thisService);
        } else if (e.target.id == "homeLink") {
                userFeedback.hideFeedback();
                thisService = e.target.parentNode.nextSibling.id;
                thisPath = e.target.getAttribute("href");
                History.navigate(thisPath, thisService);
        } else if (e.target.parentNode.href) {
            favorites.editFav(e.target);
        }
		
	});    
});
