// JavaScript Document
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

var imgStore         = 'images';       // General UI icons
var mimeStore        = 'images/mime';  // Mime type icons
var uploadLocation   = 'progress.php'; // Where the upload form posts
var downloadURI      = 'download'      // Location of download script
var folderMime       = '0000000dir';   // Fake mime type used for folders
var clipboardSeparat = '*#~!@@@';
var maxInspFileList  = 6;
var showHiddenFiles  = 0;
var agent            = navigator.userAgent.toLowerCase();
var browserSafari    = ( agent.indexOf( "safari" ) != -1 );
var sigFigures       = 0;              // Number of significant figures for fractions
var notifyHoldTime   = 2200;           // Time to display notification messages
var previousHTML     = '';
var sortDecending    = 0;
var sortBy           = 'title';        // Default category to sort by

// Determines if a value exists in an array
// From: embimedia.com
Array.prototype.inArray = function ( value )
{
    for ( var i = 0; i < this.length; i++ ) {
        // Matches identical (===), not just similar (==).
        if ( this[i] === value ) {
            return true;
        }
    }

    return false;
};

// From: embimedia.com
function sortFunc( file1, file2 )
{
	if ( file1[sortBy] < file2[sortBy] ) {
		retVal = ( sortDecending == 1 ) ? 1 : -1;
	} else if ( file1[sortBy] > file2[sortBy] ) {
		retVal = ( sortDecending == 1 ) ? -1 : 1;
	} else {
		retVal = 0;
	}

	return retVal;
}

// Perform all of the intial tasks when the page first loads
function startPage( notifyMsg, warnMsg )
{
    showHiddenFiles = readCookie( 'showHiddenFiles' );

    if ( showHiddenFiles != 1 ) {
        showHiddenFiles = 0;
    }

    displayFileList();
	fileInspector();

    if ( notifyMsg ) {
        notifyUser( notifyMsg );
    }

    if ( warnMsg ) {
        alert( unescape( warnMsg ));
        if ( window.location.href.indexOf( 'error=true' )!= -1 ) {
            var regex=/\?error=true/gi;
            window.location.href = window.location.href.replace( regex, '' );
        }
    }
}

// Returns a form checkbox or something else if approriate
function createItemSelect( id, cuts )
{
	if ( readonly ) {
		return document.createTextNode( '' );
	} else if ( cuts && cuts.inArray( files[id].title ) ) {
        return document.createTextNode( 'cut' );
    } else {
        // Create a checkbox and add it to the file list
        var c = document.createElement( 'input' );
        c.setAttribute( 'name','softsel[]' );
        c.setAttribute( 'type','checkbox' );
        c.setAttribute( 'value', id);
        c.id = 'CB' + id;
        c.onclick = function() { processCheckedItem(this); }

        if ( files[id].selected ) {
            c.setAttribute( 'checked', 'checked' );
        }

        return c;
    }
}

// Return the small icon or large icon associated with a given file
function createListIcon( id )
{    
    var i = document.createElement( 'img' );
    i.setAttribute( 'src',  mimeStore + '/small/' + files[id].type + '.gif' );
    i.setAttribute( 'width', '16' );
    i.setAttribute( 'height', '16' );

    if ( files[id].type == folderMime ) {
        var l = document.createElement( 'a' );
        l.setAttribute( 'href', './?path=' + path + '/' + files[id].title );
        l.appendChild(i);
        return l;
    } else {
        return i;
    }
}


function createFileName( id )
{
    if ( files[id].type == folderMime ) {
        var l = document.createElement( 'a' );
        l.setAttribute( 'href', './?path=' + path + '/' + files[id].title );
        l.appendChild( document.createTextNode( unescape( files[id].title )));
        return l;
    } else if ( readonly ) {
        return document.createTextNode( unescape( files[id].title ));
    } else {
        var l = document.createElement( 'a' );
        l.setAttribute( 'href', downloadURI + '/?path=' + path + '/' +
          files[id].title );
        l.appendChild( document.createTextNode( unescape( files[id].title )));
        return l;
    }
}

// Returns a download icon that is linked to the appropriate place
function createDlIcon( id )
{
    if ( files[id].type !== folderMime && ! readonly ) {
        var i = document.createElement( 'img' );
        i.setAttribute( 'src',  imgStore + '/download.gif' );
        i.setAttribute( 'width', '16' );
        i.setAttribute( 'height', '16' );

        var l = document.createElement( 'a' );
        l.setAttribute( 'href', downloadURI + '/?path=' + path + '/'
          + files[id].title );
        l.appendChild(i);
        return l;
    } else {
        return document.createTextNode( '' );
    }
}

// Removes all indications of a column selection in the file list
function unselectColumn()
{
    switch( readCookie( 'sortby' )) {
        case 'type':
            var typeCol = document.getElementById( 'typesel' );
            typeCol.className = '';
            typeCol.removeChild( typeCol.lastChild );
            break;
        case 'date':
            var dateCol = document.getElementById( 'datesel' );
            dateCol.className = '';
            dateCol.removeChild( dateCol.lastChild );
            break;
        case 'size':
            var sizeCol = document.getElementById( 'sizesel' );
            sizeCol.className = '';
            sizeCol.removeChild( sizeCol.lastChild );
            break;
        default:
            var titleCol = document.getElementById( 'titlesel' );
            titleCol.className = '';
            titleCol.removeChild( titleCol.lastChild );
    }
}

// Marks a specified file list column as selected and provides a sort control
function selectColumn( sortFlag )
{
    var i = document.createElement( 'img' );
    var l = document.createElement( 'a' );

    switch( sortFlag ) {
        case 'type':
            var parentElem = document.getElementById( 'typesel' );
            break;
        case 'date':
            var parentElem = document.getElementById( 'datesel' );
            break;
        case 'size':
            var parentElem = document.getElementById( 'sizesel' );
            break;
        default:
            var parentElem = document.getElementById( 'titlesel' );
    }

    parentElem.className = 'selectedCol';

    if ( sortDecending == 1 ) {
        i.setAttribute( 'src',  'images/sort_decend.gif' );
    } else {
        i.setAttribute( 'src',  'images/sort_ascend.gif' );
    }

    l.setAttribute( 'href', "javascript:reorderFileList('" + sortFlag + "');" );
    l.appendChild( i );
    parentElem.appendChild( l );
    setCookie( 'sortby', sortFlag );
    setCookie( 'sortDecending', sortDecending );
}

function reorderFileList( sortByCol )
{
    var mythead = document.getElementById( 'fileListHead' );
    var mytbody = document.getElementById( 'sortResults' );

    if ( sortByCol ) {
        //Sort the file data
        if ( sortByCol == readCookie( 'sortby' )) {
            sortDecending = ( sortDecending == 1 ) ? 0 : 1;
        }

        sortBy = sortByCol;
        files.sort( sortFunc );

        unselectColumn();
        selectColumn( sortBy );
    }

    // Update the file list with the new sort order
    while ( mytbody.hasChildNodes && mytbody.lastChild != null ) {
        mytbody.removeChild( mytbody.lastChild );
    }

    createFileList();
}

function displayFileList()
{
    sortByCookie = readCookie( 'sortby' );

    if ( sortByCookie != 'null' && sortByCookie != null ) {
        sortBy = sortByCookie;
    }

    sortDecending = readCookie( 'sortDecending' );
	files.sort( sortFunc );

    // Display the file list
    createFileList();
    selectColumn( sortBy );
}

// This function turns the array of file info into an html table
function createFileList()
{
    var cuts = getClipboard();  // Gets a list of files currently marked as cut
    var mytable = document.getElementById( 'fileList' );
    var mytbody = document.getElementById( 'sortResults' );
    var myNewtbody = document.createElement( 'tbody' );
    myNewtbody.id = 'sortResults';
    var docFragment = document.createDocumentFragment();
    var trElem, tdElem, txtNode;
    var i = 0;  // Only counts rows that are actually displayed

    for ( var j = 0; j < files.length; j++ ) {

        // Skip over hidden files if a user doesn't want to see them
        if ( showHiddenFiles == 0 && files[j].title.indexOf( '.' ) === 0 ) {
            continue;
        }

        trElem = document.createElement( 'tr' );
        trElem.className = 'row' + ( i % 2 );
        i++;

        trElem.id = 'TR' + j;

        tdElem  = document.createElement( 'td' );
        tdElem.appendChild( createItemSelect( j, cuts ));
        trElem.appendChild( tdElem );

        tdElem  = document.createElement( 'td' );
        tdElem.appendChild( createListIcon( j ));
        trElem.appendChild( tdElem );

        tdElem  = document.createElement( 'td' );
        tdElem.appendChild( createFileName( j ));
        trElem.appendChild( tdElem );

        tdElem = document.createElement( 'td' );
        tdElem.appendChild( createDlIcon( j ));
        trElem.appendChild( tdElem );

        tdElem = document.createElement( 'td' );
        txtNode = document.createTextNode( formatBytes( files[j].size ));
        tdElem.appendChild( txtNode );
        trElem.appendChild( tdElem );

        tdElem = document.createElement( 'td' );
        txtNode = document.createTextNode( formatDate( files[j].date ));
        tdElem.appendChild( txtNode );
        trElem.appendChild( tdElem );

        docFragment.appendChild( trElem );
    }

    if ( files.length < 3 ) {
        trElem = document.createElement( 'tr' );
        tdElem = document.createElement( 'td' );
        tdElem.setAttribute( 'colspan', '6' );
        var emElem = document.createElement( 'em' );
        txtNode = document.createTextNode( 'This folder contains no files or '
          + 'folders.' );
        emElem.appendChild( txtNode );
        tdElem.appendChild( emElem );
        trElem.appendChild( tdElem );

        docFragment.appendChild( trElem );
    }

    myNewtbody.appendChild( docFragment );
    mytable.replaceChild( myNewtbody, mytbody );
}

// Rounds a number to the specified number of significant figures
// Necessary because early versions of Safari don't support toFoxed()
function roundNum( num )
{
    return Math.round( num * Math.pow( 10, sigFigures )) / Math.pow( 10,
      sigFigures );
}

// Returns a user friendly file size
function formatBytes( bytes )
{
 	if ( bytes >= 1073741824 ) {
		return ( bytes / 1073741824 ) + ' GB';
	} else if ( bytes >= 1048576 ) {
		return roundNum( bytes / 1048576 ) + ' MB';
	} else if ( bytes >= 1024 ) {
		return roundNum( bytes / 1024 ) + ' KB';
	} else if ( bytes > 768 && bytes < 1024 ) {
		return bytes + '1 KB';
    } else if ( bytes > 0 && bytes < 768 ) {
		return '< 1 KB';
	} else {
		return "--";
	}
}

function formatDate( rawDate )
{
    var myDate = new Date( parseInt( rawDate ) * 1000 );

    if ( myDate == 'Invalid Date' ) {
        return '';
    }

    return ( myDate.getMonth() + 1 ) + '/' + myDate.getDate() + '/'
      + myDate.getFullYear();
}

// File Object - All files and folders displayed are objects of this type
function File( title, date, size, selected, type )
{
	this.title    = title;    // The title of the item
	this.date     = date;     // The modify date of the item
	this.size     = size;     // The size of the item
	this.selected = selected; // Is the item selected?
	this.type     = type;     // The file type of the item
}

// Selected File Information Object - stores information about a file or files
function SelectedFileInfo()
{
	this.filelist  = '';
	this.numSel    = 0;
	this.totalSize = 0;
	this.lastId    = 0;
	var ids        = getSelectedItems();

	for ( var j=0; j < ids.length; j++ ) {
		this.filelist += unescape( files[ids[j]].title ) + "<br />";
		this.totalSize += files[ids[j]].size;
		this.numSel++;
		this.lastId = ids[j];
	}

	this.info = '<h4>Information</h4><ul><li><strong>'
		+ this.numSel + ' items selected</strong><br />'
		+ formatBytes( this.totalSize ) + '</li></ul>';

	this.info += ( this.numSel < maxInspFileList ) ? '<ul><li>'
		+ this.filelist + '</li></ul>' : '';
}

// Notify user of status changes (e.g. upload complete, file deleted, etc.)
function notifyUser( msg )
{
    originalNotify = document.getElementById( 'notifyArea' ).innerHTML;
	document.getElementById( 'notifyArea' ).innerHTML = '<span class="notify">'
      + msg + '</span>';
	setTimeout( "document.getElementById('notifyArea').innerHTML = originalNotify",
      notifyHoldTime );
}

// Get the contents of the clipboard from the browser cookie file
function getClipboard()
{
	var clipboard  = readCookie( 'clipboard' );
	var clipPath   = readCookie( 'filepath' );
	var clipAction = readCookie( 'clipaction' );
	
	if ( clipboard && clipAction == 'cut' && path == clipPath) {
		return clipboard.split( clipboardSeparat );
	} else {
		return null;
	}
}

// Submit a paste command with the contents of the clipboard and reset the clipboard.
function paste()
{
	document.cmd.selectedItems.value = readCookie( 'clipboard' );
    document.cmd.command.value = readCookie( 'clipaction' );
    document.cmd.originPath.value = readCookie( 'filepath' );
	setCookie( 'clipboard', '' );
	document.cmd.submit();
}

// Read the items marked as selected in the files array and put them in the clipboard
function setClipboard( action )
{
	var ids           = getSelectedItems();
	var clipArray     = new Array();    // Forget previous contents of clipboard
	var selectedItems = 0;

	for ( var j = 0; j < ids.length; j++ ) {
		files[ids[j]].selected = false;
		clipArray.push( files[ids[j]].title );
		selectedItems++;
	}

	var clipboard = clipArray.join( clipboardSeparat );

	// Add selected items to clipboard cookie
	setCookie( 'filepath', path );
	setCookie( 'clipaction', action );
	setCookie( 'clipboard', clipboard );

	// Reset display so that item(s) are marked as 'cut'
	reorderFileList();

    // Refresh the inspector display
    fileInspector();

	// Display clipboard action notification to user
	notifyUser( 'Added ' + selectedItems + ' item(s) to the clipboard.' );
}

// Generic function to set a specific field in the cookie file
function setCookie( field, value )
{
	var theCookie = field + '=' +  escape( value );
	document.cookie = theCookie + "; path=/; secure;";
}

// Origninally from www.quirksmode.org
function readCookie( name )
{
    var nameEQ    = name + '=';
	var theCookie = document.cookie;
    var ca        = theCookie.split( ';' );

    for ( var i = 0; i < ca.length; i++ ) {
        var c = ca[i];

        while ( c.charAt(0) == ' ' ) {
            c = c.substring( 1, c.length );
        }

        if ( c.indexOf( nameEQ ) == 0 ) {
            return unescape( c.substring( nameEQ.length, c.length ));
        }
    }

    return null;
}

// Adds an inspector menu item layer over top of the mFile UI
function expandItem( itemCtrl, overlay )
{
    document.getElementById( itemCtrl ).className = 'inspSelected';
    document.getElementById( overlay ).style.display = 'block';
}

// Removes an inspector menu item layer that was placed over the mFile UI
function closeItem( itemCtrl, overlay )
{
    document.getElementById( overlay ).style.display = 'none';
    document.getElementById( itemCtrl ).className = '';
}

// Reveals the file upload progress UI element
function showProgress()
{
	document.getElementById( 'uploadctrl' ).style.display = 'none';
	document.getElementById( 'progbar' ).style.display    = 'block';
}

// Begins the upload process by submitting the upload form and revealing
// the upload progress bar
function startUpload()
{
	var baseUrl = uploadLocation;
	baseUrl += '?sessionid=' + sid;
	document.getElementById( 'progbar' ).src = baseUrl;
	showProgress();
	document.getElementById( 'upload' ).submit();
}

function setPMpath()
{
    var itemInfo = new SelectedFileInfo();
    document.getElementById( 'permpanel' ).src = 'perm_manager.php?target='
      + path + '/' + files[ itemInfo.lastId ].title;
}

function setFavPath()
{
    document.getElementById( 'favpanel' ).src = 'viewfavorites.php?target='
      + path;
}

// Toggles the show hidden files variable
function setShowHidden()
{
	showHiddenFiles = ( showHiddenFiles == 1 ) ? 0 : 1;
    setCookie( 'showHiddenFiles', showHiddenFiles );

    // Update UI toggle control
    setHiddenFilesCtrl();

	// Refresh display
    reorderFileList();
}

// Sets the text for the Show/Hide hidden files control
function setHiddenFilesCtrl()
{
    var l = document.getElementById( 'hidnFilesCtrl' );

	if ( showHiddenFiles == 1 ) {
        var t = document.createTextNode( 'Hide Hidden Files' );
        l.replaceChild( t, l.firstChild );
    } else {
        var t = document.createTextNode( 'Show Hidden Files' );
        l.replaceChild( t, l.firstChild );
    }
}

function setInspControl( id, cmd, label )
{
    var item = document.getElementById( id );

    if ( cmd ) {
        var newItem = document.createElement( 'a' );
        newItem.appendChild( document.createTextNode( label ));
        newItem.setAttribute( 'href', 'javascript:' + cmd + ';' );
    } else {
        var newItem = document.createElement( 'span' );
        newItem.className = 'greyOut';
        newItem.appendChild( document.createTextNode( label ));
    }

    if ( item.hasChildNodes()) {
        item.replaceChild( newItem, item.firstChild );
    } else {
        item.appendChild( newItem );
    }
}

// Replaces the "Location:" path display with a text box to input a new afs path
function activateLocationCtrl( active )
{
    if ( active == true ) {
        document.getElementById( 'location' ).style.display     = 'none';
        document.getElementById( 'changeDir' ).style.display    = 'none';
        document.getElementById( 'newLoc' ).style.display       = 'inline';
        document.getElementById( 'goChange' ).style.display     = 'inline';
        document.getElementById( 'cancelChange' ).style.display = 'inline';
    } else {
        document.getElementById( 'location' ).style.display     = 'inline';
        document.getElementById( 'changeDir' ).style.display    = 'inline';
        document.getElementById( 'newLoc' ).style.display       = 'none';
        document.getElementById( 'goChange' ).style.display     = 'none';
        document.getElementById( 'cancelChange' ).style.display = 'none';
    }
}

// Display a locked folder icon if this directory is read only
function getFolderIcon()
{
	if ( readonly ) {
        document.getElementById('selectedItem').background = imgStore
          + '/folder_locked.gif';
	} else {
		document.getElementById('selectedItem').background = imgStore
          + '/folder.gif';
	}
}

// Displays a dynamic inspector-type interface that changes based on user selections
function fileInspector()
{
	var itemInfo  = new SelectedFileInfo();
	var content = '';

    // Only show the paste option if there is something in the clipboard
	if ( readCookie( "clipboard" )) {
        setInspControl( 'pasteCtrl', 'paste()', 'Paste to This Folder' );
    } else {
        setInspControl( 'pasteCtrl', '', 'Paste to This Folder' );
    }

	if ( itemInfo.numSel == 0 ) {
        if ( readonly ) {
            setInspControl( 'uploadCtrl', '', 'Upload File(s)' );
            setInspControl( 'newFolderCtrl', '', 'Create a New Folder' );
        } else {
            setInspControl( 'uploadCtrl', 'expandItem(\'uploadCtrl\',\'upload\')',
              'Upload File(s)' );
            setInspControl( 'newFolderCtrl',
              'expandItem(\'newFolderCtrl\',\'newFolder\')',
              'Create a New Folder' );
        }

        setInspControl( 'cutCtrl', '', 'Cut Selected Item(s)' );
        setInspControl( 'copyCtrl', '', 'Copy Selected Item(s)' );
        setInspControl( 'renameCtrl', '', 'Rename Selected Item' );
        setInspControl( 'deleteCtrl', '', 'Delete Selected Item(s)' );
        setInspControl( 'permsCtrl', '', 'Set Permissions for Folder' );
        document.getElementById( 'inspTitle' ).innerHTML = 'Folder Properties';
        document.getElementById( 'selectedItem' ).innerHTML = foldername;
        document.getElementById( 'itemInfo' ).innerHTML = '';

	} else if ( itemInfo.numSel == 1 ) {
        setInspControl( 'uploadCtrl', '', 'Upload File(s)' );
        setInspControl( 'cutCtrl', 'setClipboard(\'cut\')',
          'Cut Selected Item(s)' );
        setInspControl( 'copyCtrl', 'setClipboard(\'copy\')',
          'Copy Selected Item(s)' );
        setInspControl( 'renameCtrl', 'rename()', 'Rename Selected Item' );
        setInspControl( 'deleteCtrl', 'delFiles(files)',
          'Delete Selected Item(s)' );
        setInspControl( 'newFolderCtrl', '', 'Create a New Folder' );
        
        if ( files[ itemInfo.lastId ].type == folderMime ) {
            setInspControl( 'permsCtrl',
              'setPMpath();expandItem(\'permsCtrl\',\'permissions\')',
              'Set Permissions for Folder' );
        } else {
            setInspControl( 'permsCtrl', '', 'Set Permissions for Folder' );
        }

        setInspControl( 'pasteCtrl', '', 'Paste to This Folder' );
        document.getElementById( 'inspTitle' ).innerHTML =
          'Selected Item Properties';
        document.getElementById( 'selectedItem' ).innerHTML =
          unescape( files[itemInfo.lastId].title );
        document.getElementById( 'itemInfo' ).innerHTML = itemInfo.info;
	} else {
        setInspControl( 'uploadCtrl', '', 'Upload File(s)' );
        setInspControl( 'cutCtrl', 'setClipboard(\'cut\')',
          'Cut Selected Item(s)' );
        setInspControl( 'copyCtrl', 'setClipboard(\'copy\')',
          'Copy Selected Item(s)' );
        setInspControl( 'renameCtrl', '', 'Rename Selected Item' );
        setInspControl( 'deleteCtrl', 'delFiles(files)',
          'Delete Selected Item(s)' );
        setInspControl( 'newFolderCtrl', '', 'Create a New Folder' );
        setInspControl( 'permsCtrl', '', 'Set Permissions for Folder' );
        setInspControl( 'pasteCtrl', '', 'Paste to This Folder' );
        document.getElementById( 'inspTitle' ).innerHTML =
          'Selected Items Properties';
		document.getElementById( 'selectedItem' ).innerHTML = 'Multiple Items';
        document.getElementById( 'itemInfo' ).innerHTML = itemInfo.info;
	}

    setHiddenFilesCtrl();
}

// Toggles the display of a hidden object
function toggleDisplay( id )
{
	var obj = document.getElementById( id ).style;
    obj.display = ( obj.display == 'none' ) ? 'block' : 'none';
}

// Process the request to begin a file rename
function rename()
{
    var ids = getSelectedItems();
    var id = ids[0];
    var tBox = document.createElement( 'input' );
    tBox.setAttribute( 'name', '' );
    tBox.setAttribute( 'size', '15' );
    tBox.setAttribute( 'maxlength', '50' );
    tBox.setAttribute( 'value' , unescape( files[id].title ));
    var rButton = document.createElement( 'a' );
    rButton.setAttribute( 'href', '#' );
    rButton.appendChild( document.createTextNode( 'Rename' ));
    var spacer = document.createTextNode( ' | ' );
    var cButton = document.createElement( 'a' );
    cButton.setAttribute( 'href', '#' );
    cButton.appendChild( document.createTextNode( 'Cancel' ));

    var trElem = document.getElementById( 'TR' + id );
    var cell = trElem.getElementsByTagName( 'td' )[2];
    oldContent = cell.replaceChild( tBox, cell.firstChild );
    cell.appendChild( rButton );
    cell.appendChild( spacer );
    cell.appendChild( cButton );
    cButton.onclick = function() { cancelRename( oldContent, cell ); }
    rButton.onclick = function(id) { finishRename( tBox.value, oldContent, cell ); }
    tBox.focus();

    // Safari does .select() automatically. Will crash if called manually
    if ( ! browserSafari ) {
        tBox.select();
    }
}

// Removes the file rename text field from the file list and restores the
// original file name html
function cancelRename( oldContent, cell )
{
    cell.replaceChild( oldContent, cell.firstChild );
    cell.removeChild( cell.lastChild );
    cell.removeChild( cell.lastChild );
    cell.removeChild( cell.lastChild );
}

// Submits the updated file name
function finishRename( newName, oldContent, cell )
{
    var ids = getSelectedItems();
    var id = ids[0];

    if ( files[id].title != newName ) {
        var form = document.getElementById( 'cmd' );
        form.selectedItems.value = files[id].title;
        form.command.value = 'rename';
        form.newName.value = newName;
        form.submit();
    } else {
        cancelRename( oldContent, cell );
    }
}

// Gets the ids of all items marked as selected in the files array
function getSelectedItems()
{
	var ids = new Array();

	for ( var j = 0; j < files.length; j++ ) {
		if ( files[j].selected === true ) {
			ids.push(j);
		}
	}

	return ids;
}

// Processs the request to delete files
function delFiles()
{
	var ids     = getSelectedItems();
	var delMsg  = "Are you sure you want to delete:\n";
	var delList = '';

	// Print the list of items to be deleted
	for ( var j = 0; j < ids.length; j++ ) {
		delList += unescape( files[ids[j]].title ) + "\n";
	}

	if ( confirm( delMsg + delList )) {
		document.cmd.selectedItems.value = delList;
        document.cmd.command.value = 'delete';
		document.cmd.submit();
	}

	ids.length = 0;	
}

// Changes the background color of a list item when it is selected
function processCheckedItem( checkbox )
{
	if ( trElem = checkbox.parentNode.parentNode ) {
		if ( files[checkbox.value].selected === true ) {
            trElem.className = 'row' + ( checkbox.value % 2 );
            files[checkbox.value].selected = false;
		} else {
            trElem.className = 'selectedrow';
			files[checkbox.value].selected = true;
		}
	}

    fileInspector();
}
