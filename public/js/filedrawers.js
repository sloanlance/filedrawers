var myJSONdata,
    myPerms,
    homeURL,
    currentURL,
    baseUrl = '';

if (typeof FD == "undefined" || ! FD) {
    var FD = {};
}

function roundNum( num )
{
    return Math.round( num * Math.pow( 10, 1 )) / 
        Math.pow( 10, 1 );
}

FD.DirList = function() {	

    var showHidden = false;

    formatDate = function(elCell, oRecord, oColumn, oData) {
        var oDate = new Date(oData*1000);
        var str = (oDate.getMonth() + 1) + '/' + oDate.getDate() + '/' + oDate.getFullYear();
        elCell.innerHTML = str;
    }

    formatBytes = function(elCell, oRecord, oColumn, oData)	{
        var bytes = oData;
        var str;

        if ( bytes >= 1073741824 ) {
            str = roundNum( bytes / 1073741824 ) + ' GB';
        } else if ( bytes >= 1048576 ) {
            str = roundNum( bytes / 1048576 ) + ' MB';
        } else if ( bytes > 102 ) {
            str =  roundNum( bytes / 1024 ) + ' KB';
        } else if ( bytes > 0 ) {
            str = bytes + ' B';
        } else if ( true /*readPriv*/ ) { // size unknown - file is probably not readable
            str = 'empty';
        } else {
            str = '-';
        }

        elCell.innerHTML = str;
    }

    formatType = function(elCell, oRecord, oColumn, sData) {					
        elCell.innerHTML = '<img src="' + baseUrl + 'images/mime/small/' + sData + '.gif" />';
    };

    formatURL = function(elCell, oRecord, oColumn, sData) {	
        // this works for files.  add code to handle folders.

        if (oRecord.getData("type") == "dir") {
            elCell.innerHTML = '<a id="folderLink">' + sData + '</a>';
        } else {
            elCell.innerHTML = '<a href="webservices/download/?path=' + currentURL + '/' + sData + '">' + sData + '</a>';
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
            myDataSource.sendRequest("list/?format=json&path=" + currentURL + "/" + oArgs.target.innerHTML, dirTable.onDataReturnInitializeTable, dirTable);
        }		

    };

    var myColumnDefs = [
    {key:"checked",label:"", width:"30", formatter:YAHOO.widget.DataTable.formatCheckbox},
    {key:"type", sortable:true, resizeable:true},
    {key:"filename", label:"Name", formatter:formatURL, sortable:true, resizeable:true},
    {key:"modTime", label:"Last Modified", formatter:formatDate, sortable:true, sortOptions:{defaultDir:YAHOO.widget.DataTable.CLASS_DESC}},
    {key:"size", label:"Size", formatter:formatBytes, sortable:true, resizeable:true},
    {key:"mimeImage", label:"Type", formatter:formatType, sortable:true, resizeable:true},
    //{key:"perms", sortable:true, resizeable:true},
    {key:"mimeType", sortable:true, resizeable:true}
    ];

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

    return {
init: function() {

          myDataSource = new YAHOO.util.DataSource("webservices/");  // first call
          myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON; 
          myDataSource.responseSchema = {
resultsList: "contents",
             fields: ["type","filename","modTime","size","mimeImage","perms","mimeType"]
          };

          myDataSource.doBeforeCallback = hiddenFileFilter;

          myDataSource.subscribe('responseEvent', function(oDS){  // triggered by data return
                  myJSONdata = YAHOO.lang.JSON.parse(oDS.response.responseText);
                  currentURL = YAHOO.lang.dump(myJSONdata.path);

                  if (!homeURL) {
                  homeURL = currentURL;
                  }

                  myPerms = YAHOO.lang.dump(myJSONdata.contents[0].perms);

                  var currentDir,
                  changeDirForm = YAHOO.util.Dom.get('changeDirForm'),
                  viewHTML;

                  changeDirForm.innerHTML = YAHOO.lang.dump(myJSONdata.path);

                  }
                  );				

          dirTable = new YAHOO.widget.DataTable("content", myColumnDefs, myDataSource, {initialRequest:"list/?format=json"});

          dirTable.subscribe('checkboxClickEvent', handleFileSelection);
          dirTable.subscribe('click', handleTableClick);

          return dirTable;
      },

toggleHiddenFilter: function(e, action) {

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



                        myDataSource.sendRequest("list/?format=json", dirTable.onDataReturnInitializeTable, dirTable);

                        /*						
                                                                        myDataSource.sendRequest(showHidden,{
success  : dirTable.onDataReturnInitializeTable,
failure  : dirTable.onDataReturnInitializeTable,
scope    : dirTable,
argument : tableState
});*/
                        }

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

        alert(target.value);

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

    var locationClick = function(e) {
        alert("location clicked");
    }

    YAHOO.util.Event.on('infoBar', 'click', handleClick);
    YAHOO.util.Event.on('changeDirForm', 'submit', handleSubmit);

    YAHOO.util.Event.on('notifyArea', 'click', locationClick);

    var kl = new YAHOO.util.KeyListener(document, {keys:13}, {fn:function(){alert('enter');}});

    return {
init: function(crntDir) {
          currentDir = crntDir;
      },

update:update
    }
};	

FD.FileInspector = function() {

    var currentDir,
        actions = {},
        temp = YAHOO.util.Dom.get('actionList'),
        evnt = new YAHOO.util.CustomEvent("Inspector Event"),
        links = temp.getElementsByTagName('a'),
        i;

    // called upon instantiation.
    for (i=0; i<links.length; i++) {
        action = links[i].hash.match(/#(.*)$/);  // converts link to an array, with original in index 0 and #-less in index 1
        actions[action[1]] = {ref: links[i]};  // actions becomes an array of objects with char indexes based on action, and links to that action.  example:  actions["cut"] hold href obj for cut
    }

    /*var updateItemInfo = function(selectedRows) {
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
    //FD.Utils.formatBytes(totalBytes) + '</li></ul>';

    if (selectedRows.length < 1000) {
    itemInfoContent += '<ul>';
    for (var i=0; i< filenames.length; i++) {
    itemInfoContent += '<li>' + filenames[i] + '</li>';
    }
    itemInfoContent += '</ul>';
    }

    // It's better to touch the DOM just once:
    itemInfo.innerHTML = itemInfoContent;
    };*/

    // makes menu options active or inactive based on the permissions
    var update = function(oArgs) {

        var numSelected,
            i,
            permissions = {
r: false,   // read
   l: false,   // list
   i: false,   // insert
   d: false,   // delete
   w: false,   // write
   k: false,   // lock
   a: false    // administrative
            };

        if (oArgs) {
            numSelected = this.getSelectedRows().length;
            //updateItemInfo.apply(this, [this.getSelectedRows()]);
        } else {
            numSelected = 0;
            //updateItemInfo();
        }

        for (i=0; i < myPerms.length; i++) {
            permissions[myPerms.charAt(i)] = true;
            //alert(myPerms.charAt(i) + " = " + permissions[myPerms.charAt(i)]);
        }

        //alert(actions.upload.ref);

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

    var handleClick = function(e) {

        var target = YAHOO.util.Event.getTarget(e);

        if (target.href) {
            YAHOO.util.Event.preventDefault(e);
            var action = target.hash.match(/#(.*)$/);

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
       evnt:evnt
           //clearSelection:clearSelection
    }

}

YAHOO.util.Event.addListener(window, "load", function() {

        var dirList = new FD.DirList(),
        dirTable = dirList.init(),
        inspector = new FD.FileInspector(),
        infoBar = new FD.InfoBar();

        inspector.evnt.subscribe(dirList.toggleHiddenFilter);

        dirTable.subscribe('rowSelectEvent', inspector.update);
        dirTable.subscribe('rowUnselectEvent', inspector.update);
        dirTable.subscribe('initEvent', inspector.update);

        YAHOO.util.Event.on('homeBtn', 'click', function() {
            //alert(homeURL);
            myDataSource.sendRequest("list/?format=json", dirTable.onDataReturnInitializeTable, dirTable);
            });

        });

