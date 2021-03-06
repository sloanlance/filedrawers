// JavaScript Document
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

var imgStore		 = '/images';	   // General UI icons
var mimeStore		= '/images/mime';  // Mime type icons
var uploadLocation   = '/progress.php'; // Where the upload form posts
var downloadURI	  = 'download'	  // Location of download script
var folderMime	   = '0000000dir';   // Fake mime type used for folders
var clipboardSeparat = '*#~!@@@';
var maxInspFileList  = 6;
var showHiddenFiles  = 0;
var clobberFiles	 = 0;
var agent			= navigator.userAgent.toLowerCase();
var browserSafari	= ( agent.indexOf( "safari" ) != -1 );
var sigFigures	   = 1;			  // significant figures for fractions
var notifyHoldTime   = 5000;		   // Time to display notification messages
var previousHTML	 = '';
var sortDecending	= 0;
var sortBy		   = 'title';		// Default category to sort by

var uploadloopcnt = 0;

function ajaxupload() {
	document.getElementById( 'overwrite_file' ).value =
		(( document.getElementById( 'overwrite_box' ).checked ) ?
		document.getElementById( 'overwrite_box' ).value : "" );

	if ( uploadloopcnt == 0 ) {
		document.getElementById( 'upload' ).submit();
	}
	document.getElementById( 'lbtop' ).style.visibility = 'visible';

	var xmlHttpReq = false;
	var self = this;
	if ( window.XMLHttpRequest ) {
		self.xmlHttpReq = new XMLHttpRequest();
	} else if ( window.ActiveXObject ) {
		self.xmlHttpReq = new ActiveXObject( "Microsoft.XMLHTTP" );
	}
	self.xmlHttpReq.open( 'POST', "/upload-info-server.php", true );
	self.xmlHttpReq.setRequestHeader( 'Content-Type', 
		'application/x-www-form-urlencoded' );

	self.xmlHttpReq.onreadystatechange = function() {
		if ( self.xmlHttpReq.readyState == 4 && 
				self.xmlHttpReq.status == 200 ) {
			var results = self.xmlHttpReq.responseText;
			var fields = results.split( ":" );
			var total = fields[1];
			if ( fields.length >= 3 ) {
				var uploaded = fields[2];
				if ( uploaded.match( /^[0-9]+/ ) && 
						total.match( /^[0-9]+$/ ) && total > 0 ) {
					var percent = Math.round( uploaded / total * 100 );
					document.getElementById( 'lbinner' ).style.width = 
						percent + '%';
					document.getElementById( 'lbpercent' ).innerHTML = 
						percent + '%';
					document.getElementById( 'fileinfo' ).innerHTML = 
						"Uploaded: " + formatBytes( uploaded ) + "/" + 
						formatBytes( total );
				}
			}
			ajaxtimerid = setTimeout( 'ajaxupload()', 2000 );
			uploadloopcnt++;
		}
	}
	self.xmlHttpReq.send( 'filename = ' + sid );
}

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

	init_formvals();
	// only initialize filemanager if we're viewing files
	if ( displayfileman ) {
		displayFileList();
		fileInspector();
	}

	if ( notifyMsg ) {
		notifyUser( notifyMsg );
	}

	if ( warnMsg ) {
		alert( unescape( warnMsg ));
		if ( window.location.href.indexOf( 'error=true' ) != -1 ) {
			var regex = /\?error=true/gi;
			window.location.href = window.location.href.replace( regex, '' );
		}
	}
}

// Initialize form input values that can't be done with html.
function init_formvals()
{
	document.getElementById( 'newLoc' ).value = path;

	// if upload doesn't exist, no sidebar, then following vars don't exist
	if ( !document.getElementById( 'upload' )) {
		return 0;
	}
	document.getElementById( 'sessionid' ).value = sid;
	document.getElementById( 'uploadpath' ).value = path;
	document.getElementById( 'returnToURI' ).value = returnToURI;

	return 1;
}

// Returns a form checkbox or something else if approriate
function createItemSelect( id, cuts )
{
	if ( cuts && cuts.inArray( files[id].title )) {
		return document.createTextNode( 'cut' );
	}

	// Create a checkbox and add it to the file list
	var c = document.createElement( 'input' );
	c.setAttribute( 'name','softsel[]' );
	c.setAttribute( 'type','checkbox' );
	c.setAttribute( 'value', id );
	c.id = 'CB' + id;
	c.onclick = function() { processCheckedItem( this ); }

	if ( files[id].selected ) {
		c.setAttribute( 'checked', 'checked' );
	}

	return c;
}

// Return the small icon or large icon associated with a given file
function createListIcon( id )
{	
	var i = document.createElement( 'img' );

	/*
	if ( files[id].type == 'jpeg' || 
			files[id].type == 'gif' ||
			files[id].type == 'png' ) {
		i.setAttribute( 'src',  
			'/download/view.php?path=' + getFilenameUrl( id ));
		i.setAttribute( 'width', '16' );
		i.setAttribute( 'height', '16' );
		i.onmouseover = function( evt ) { 
			if ( !evt ) evt = window.event;
			showImage( id, evt.clientX, evt.clientY );
		}
	} else { */
		i.setAttribute( 'src',  mimeStore + '/small/' + files[id].type + '.gif' );
		i.setAttribute( 'width', '16' );
		i.setAttribute( 'height', '16' );
	// }

	var l = document.createElement( 'a' );
	// only add link if this is a folder, or readable and not empty
	if ( files[id].type == folderMime || 
			( readPriv && files[id].size != 0 )) {
		if ( files[id].viewable ) {
			l.setAttribute( 'href', './?path=' + getFilenameUrl( id ));
		} else {
			l.setAttribute( 'href', downloadURI + '/?path=' + getFilenameUrl( id ));
		}
	}
	l.appendChild( i );

	return l;
}


function createFileName( id )
{
	title = files[id].title;

	if ( title == '.' ) {
		title += ' (current directory)';
	}
	if ( title == '..' ) {
		title += ' (parent directory)';
	}

	// only add link if this is a folder, or readable and not empty
	if ( files[id].type == folderMime || 
			( readPriv && files[id].size != 0 )) {
		var l = document.createElement( 'a' );

		if ( files[id].viewable ) {
			l.setAttribute( 'href', './?path=' + getFilenameUrl( id ));
		} else {
			l.setAttribute( 'href', 'download/?path=' + getFilenameUrl( id ));
		}
		l.appendChild( document.createTextNode( title ));
		return l;
	} else {
		return document.createTextNode( title );
	}
}

// Returns a download icon that is linked to the appropriate place
function createDlIcon( id )
{
	if ( files[id].type !== folderMime && readPriv && 
			files[id].size != 0 ) {
		var i = document.createElement( 'img' );
		i.setAttribute( 'src',  imgStore + '/download.gif' );
		i.setAttribute( 'width', '16' );
		i.setAttribute( 'height', '16' );
		i.setAttribute( 'alt', 'Download' );

		var l = document.createElement( 'a' );
		l.setAttribute( 'href', downloadURI + '/?path=' +
			 getFilenameUrl( id ));
		l.appendChild( i );
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
		break;
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
		break;
	}

	parentElem.className = 'selectedCol';

	if ( sortDecending == 1 ) {
		i.setAttribute( 'src', imgStore + '/' + 'sort_decend.gif' );
	} else {
		i.setAttribute( 'src', imgStore + '/' + 'sort_ascend.gif' );
	}

	l.setAttribute( 'href', "javascript:reorderFileList( '" + sortFlag + "' );" );
	l.appendChild( i );
	parentElem.appendChild( l );
	setCookie( 'sortby', sortFlag );
	setCookie( 'sortDecending', sortDecending );
}

// Refreshes the table containing the file list when a user changes
// the sort order
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

	// Update the file list with the new sort order. Desttroys the
	// table containing the existing file list so that a table with
	// the new sort order can be created in its place
	while ( mytbody.hasChildNodes && mytbody.lastChild != null ) {
		mytbody.removeChild( mytbody.lastChild );
	}

	createFileList();
}

// Performs the initial display of the file list when the page loads
// Checks to see what to sort by, sorts the files, and displays the results
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

function mouseOver() {
	var obj = this;

	if ( obj.id.match( /^img_/ )) {
		var fileindex = obj.id.replace( /^img_/, "" );
		var curleft = curtop = 0;

		if ( obj.offsetParent ) {
			curleft = obj.offsetLeft
			curtop = obj.offsetTop
			while ( obj = obj.offsetParent ) {
				curleft += obj.offsetLeft
				curtop += obj.offsetTop
			}
		}
	} else {
		var imgs = document.getElementsByTagName( 'img' );
		for ( var j = 0; j<imgs.length; j++ ) {
			if ( imgs[j].id.match( /^theimg_/ )) {
				imgs[j].style.visibility = 'hidden';
			}
		}
	}
}

function showImage( id, x, y ) {
	try {
		var i = document.getElementById( 'theimg_' + id );
		i.style.left = x + 'px';
		i.style.top = y + 'px';
		var w = i.width;
		var h = i.height;
		var scale = 1;

		if ( w > 200 || h > 200 ) {
			scale = 200 / Math.max( w,h );
		}
		if ( w != 0 && h != 0 ) {
			i.setAttribute( 'width', w * scale );
			i.setAttribute( 'height', h * scale );
		}
		
		i.style.visibility = 'visible';
	} catch ( error ) {
		if ( !document.getElementById( "display" )) {
			var div = document.createElement( 'div' );
			document.body.appendChild( div );
			div.id = 'display';
		}
		document.getElementById( "display" ).innerHTML = 'showImage: ' + error;
	}
}

function addImage( fileurl, id ) {
	try {
		var image = new Image();
		image.onload = function ( evt ) { }
		image.onerror = function ( evt ) {
			if ( !document.getElementById( "loaderror" )) {
				var div = document.createElement( 'div' );
				document.body.appendChild( div );
				div.id = 'loaderror';
			}
			document.getElementById( "loaderror" ).innerHTML = 
				'load error: ' + evt;
		}

		image.src = '/download/view.php?path=' + fileurl;
		image.id = 'theimg_' + id;
		image.style.border = '1px solid #000000';
		image.style.position = 'absolute';
		image.style.left = '0px';
		image.style.top = '0px';
		image.style.visibility = 'hidden';
		image.onmouseover = function( evt ) { 
			if ( !evt ) evt = window.event;
			showImage( id, evt.clientX, evt.clientY );
		}
		document.body.appendChild( image );
	} catch ( error ) {
		if ( !document.getElementById( "display" )) {
			var div = document.createElement( 'div' );
			document.body.appendChild( div );
			div.id = 'display';
		}
		document.getElementById( "display" ).innerHTML = 'addImage: ' + error;
	}
}

// Abstracts the steps used to put up a warning message
function addWarningMessage( msg )
{
	if ( msg.length > 0 ) {
		trElem = document.createElement( 'tr' );
		tdElem = document.createElement( 'td' );
		tdElem.colSpan = 6;
		var emElem = document.createElement( 'em' );
		txtNode = document.createTextNode( msg );
		emElem.appendChild( txtNode );
		tdElem.appendChild( emElem );
		trElem.appendChild( tdElem );
		return trElem;
	}
}

// This function turns the array of file info into an html table
function createFileList()
{
	var cuts = getClipboard();  // Gets a list of files currently marked as cut
	var mytbody = document.getElementById( 'sortResults' );
	var myNewtbody = document.createElement( 'tbody' );
	myNewtbody.id = 'sortResults';
	var docFragment = document.createDocumentFragment();
	var trElem, tdElem, txtNode;
	var i = 0;  // Only counts rows that are actually displayed
	var className = '';
	var mytable = '';
	var numHidden = 0;

	if ( !readPriv ) {
		docFragment.appendChild( addWarningMessage( 'Note: You do not ' + 
			'have read privileges for files in this directory.' ));
	} else if ( files.length < 3 ) {
		docFragment.appendChild( addWarningMessage( 
			'This folder contains no files or folders.' ));
	} 

	for ( var j = 0; j < files.length; j++ ) {

		// Skip over hidden files if a user doesn't want to see them
		if ( showHiddenFiles == 0 && files[j].title.indexOf( '.' ) === 0 ) {
			numHidden++;
			continue;
		}

		trElem = document.createElement( 'tr' );
		className = 'row' + ( i % 2 );
		trElem.className = className
		files[j].className = className;
		i++;

		trElem.id = 'TR' + j;

		/*
		 * checkbox column 
		 * these perms should correspond with smarty/filelist.tpl
		 * AFS needs i and w ACLs in order to delete
		 */
		if ( adminPriv || readPriv || 
				( insertPriv && writePriv && deletePriv )) {
			tdElem  = document.createElement( 'td' );
			if ( files[j].title != '.' && files[j].title != '..' ) {
				tdElem.appendChild( createItemSelect( j, cuts ));
			} 
			trElem.appendChild( tdElem );
		}

		// mime type icon column
		tdElem  = document.createElement( 'td' );
		if ( lookupPriv || files[j].type == folderMime ) {
			/*
			if ( files[j].type == 'jpeg' || 
					files[j].type == 'jpg' ||
					files[j].type == 'gif' ||
					files[j].type == 'png' ) {
				tdElem.setAttribute( 'id', 'img_' + j );
				addImage( getFilenameUrl( j ), j );
			}
			*/
			tdElem.appendChild( createListIcon( j ));
		}
		trElem.appendChild( tdElem );

		// title or filename column
		tdElem  = document.createElement( 'td' );
		tdElem.appendChild( createFileName( j ));
		trElem.appendChild( tdElem );

		// download icon column
		// this is suppressed in smarty template if not readPriv
		if ( readPriv ) {
			tdElem = document.createElement( 'td' );
			tdElem.appendChild( createDlIcon( j ));
			trElem.appendChild( tdElem );
		}

		// size column
		tdElem = document.createElement( 'td' );
		txtNode = document.createTextNode( formatBytes( files[j].size ));
		tdElem.appendChild( txtNode );
		tdElem.style.textAlign='right'; 
		trElem.appendChild( tdElem );

		// date column
		tdElem = document.createElement( 'td' );
		txtNode = document.createTextNode( formatDate( files[j].date ));
		tdElem.appendChild( txtNode );
		tdElem.style.textAlign='right'; 
		trElem.appendChild( tdElem );

		docFragment.appendChild( trElem );
	}

	if ( numHidden == files.length && files.length > 2 ) {
		docFragment.appendChild( addWarningMessage( 
			'This directory only contains hidden files' ));
	}

	// Attaches all of the html generated above to the document
	myNewtbody.appendChild( docFragment );
	if ( document.getElementById( 'fileList' )) {
		mytable = document.getElementById( 'fileList' );
		mytable.replaceChild( myNewtbody, mytbody );

		var tds = document.getElementsByTagName( 'td' );
		for ( var j = 0; j < tds.length; j++ ) {
			tds[j].onmouseover = mouseOver;
		}   
	}
}

// Rounds a number to the specified number of significant figures
// Necessary because early versions of Safari don't support toFoxed()
function roundNum( num )
{
	return Math.round( num * Math.pow( 10, sigFigures )) / 
		Math.pow( 10, sigFigures );
}

// Returns a user friendly file size
function formatBytes( bytesstr )
{
	var bytes = parseInt( bytesstr );
	var tmp = 0.0;

	if ( bytes >= 1073741824 ) {
		return ( bytes / 1073741824 ) + ' GB';
	} else if ( bytes >= 1048576 ) {
		return roundNum( bytes / 1048576 ) + ' MB';
	} else if ( bytes > 102 ) {
		return roundNum( bytes / 1024 ) + ' KB';
	} else if ( bytes > 0 ) {
		return '< .1 KB';
	} else if ( readPriv ) {
		return 'empty';
	}

	// size unknown - file is probably not readable
	return '-';
}

function formatDate( rawDate )
{
	var myDate = new Date( parseInt( rawDate ) * 1000 );

	if ( myDate == 'Invalid Date' ) {
		return '';
	}

	// don't display a date if it's not readable, or unknown
	if ( !readPriv || ( Date.parse( myDate ) == 0 )) {
		return '-';
	}

	return ( myDate.getMonth() + 1 ) + '/' + myDate.getDate() + '/'
	  + myDate.getFullYear();
}

// File Object - All files and folders displayed are objects of this type
function File( title, date, size, selected, type, viewable )
{
	this.title = title;	// The title of the item
	this.date = date * 1; // The modify date of the item
				// multiply by 1 to cast to an int
	this.size = size;	 // The size of the item
	this.selected = selected; // Is the item selected?
	this.type = type;	 // The file type of the item
	this.viewable = viewable;	 // Can we display without download?
	this.className = false;	// The original class style of the file's row
}

// Selected File Information Object - stores information about a file or files
function SelectedFileInfo()
{
	this.filelist  = '';
	this.numSel	= 0;
	this.totalSize = 0;
	this.lastId	= 0;
	var ids		= getSelectedItems();

	for ( var j = 0; j < ids.length; j++ ) {
		this.filelist += files[ids[j]].title + "<br />";
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

// Notify user of status changes ( e.g. upload complete, file deleted, etc. )
function notifyUser( msg )
{
	originalNotify = document.getElementById( 'notifyArea' ).innerHTML;
	document.getElementById( 'notifyArea' ).innerHTML =
			'<span class="notify" id="location" >' + msg + '</span>';
	setTimeout( "resetnotifyArea( originalNotify )", notifyHoldTime );
}

function resetnotifyArea( original )
{
	document.getElementById( 'notifyArea' ).innerHTML = original;
}

// Get the contents of the clipboard from the browser cookie file
function getClipboard()
{
	var clipboard  = readCookie( 'clipboard' );
	var clipPath   = readCookie( 'filepath' );
	var clipAction = readCookie( 'clipaction' );
	
	if ( clipboard && clipAction == 'cut' && path == clipPath ) {
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

/*
 * Read the items marked as selected in the files array and put
 * them in the clipboard
 */
function setClipboard( action )
{
	var ids		   = getSelectedItems();

	// Forget previous contents of clipboard
	var clipArray	 = new Array();
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
	var nameEQ	= name + '=';
	var theCookie = document.cookie;
	var ca		= theCookie.split( ';' );

	for ( var i = 0; i < ca.length; i++ ) {
		var c = ca[i];

		while ( c.charAt( 0 ) == ' ' ) {
			c = c.substring( 1, c.length );
		}

		if ( c.indexOf( nameEQ ) == 0 ) {
			return unescape( c.substring( nameEQ.length, c.length ));
		}
	}

	return null;
}

// Adds an inspector menu item layer over top of the UI
function expandItem( itemCtrl, overlay )
{
	document.getElementById( itemCtrl ).className = 'inspSelected';
	document.getElementById( overlay ).style.display = 'block';
}

// Removes an inspector menu item layer that was placed over the UI
function closeItem( itemCtrl, overlay )
{
	document.getElementById( overlay ).style.display = 'none';
	document.getElementById( itemCtrl ).className = '';
}

// Reveals the file upload progress UI element
function showProgress()
{
	document.getElementById( 'uploadctrl' ).style.display = 'none';
	document.getElementById( 'progbar' ).style.display	= 'block';
}

// Begins the upload process by submitting the upload form and revealing
// the upload progress bar
function startUpload()
{
	var baseUrl = uploadLocation;
	baseUrl += '?sessionid=' + sid;
	document.getElementById( 'progbar' ).src = baseUrl;
	showProgress();
	/*
	 * overwrite_file must come before the files in the form submission,
	 * but we want to be able to display the checkbox after the file list.
	 * therefore, we need to set it manually before submit.
	 */
	document.getElementById( 'overwrite_file' ).value =
		(( document.getElementById( 'overwrite_box' ).checked ) ?
			document.getElementById( 'overwrite_box' ).value : "" );
	document.getElementById( 'upload' ).submit();
}

// This function sets the target of the iframe that displays the permissions
// for the selected folder. PM stands for permissions manager
function setPMpath()
{
	var itemInfo = new SelectedFileInfo();

	document.getElementById( 'permpanel' ).src = 
		'/perm_manager.php?target=' + getFilenameUrl( itemInfo.lastId );
}

// Sets the path for the iframe that displays the user's list of favorite locations
function setFavPath()
{
	document.getElementById( 'favpanel' ).src = 
		'/viewfavorites.php?target=' + path;
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

// This function creates an item in the list of controls that are displayed in
// the sidebar on the left. Inspector refers to the sidebar on the left
// This function greys out a link when it isn't appropriate
function setInspControl( id, cmd, label )
{
	var item = '';

	if ( !document.getElementById( id )) {
		return;
	}

	item = document.getElementById( id );
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
		document.getElementById( 'newLoc' ).value = path;
		document.getElementById( 'location' ).style.display	 = 'none';
		document.getElementById( 'changeDir' ).style.display	= 'none';
		document.getElementById( 'newLoc' ).style.display	   = 'inline';
		document.getElementById( 'goChange' ).style.display	 = 'inline';
		document.getElementById( 'cancelChange' ).style.display = 'inline';
	} else {
		document.getElementById( 'location' ).style.display	 = 'inline';
		document.getElementById( 'changeDir' ).style.display	= 'inline';
		document.getElementById( 'newLoc' ).style.display	   = 'none';
		document.getElementById( 'goChange' ).style.display	 = 'none';
		document.getElementById( 'cancelChange' ).style.display = 'none';
	}
}

// Display a locked folder icon if this directory is read only
function getFolderIcon()
{
	if ( !writePriv ) {
		document.getElementById( 'selectedItem' ).style.backgroundImage = 
			'url( ' + imgStore + '/folder_locked.gif )';
	} else {
		document.getElementById( 'selectedItem' ).style.backgroundImage = 
			'url( ' + imgStore + '/folder.gif )';
	}
}

/* Displays a dynamic inspector-type interface that changes based on 
 * user selections. This function displays inspector items as greyed out 
 * or clickable links as appropriate
 * This entire function assumes that we have lookup access, otherwise we
 * won't be able to see the items in this directory...
 */
function fileInspector()
{
	var itemInfo  = new SelectedFileInfo();
	var content = '';

	getFolderIcon();

	// upload option and create new folder
	if ( itemInfo.numSel == 0 && insertPriv ) {
		setInspControl( 'uploadCtrl', 'initClobberCheckbox();' +
		   'expandItem( "uploadCtrl", "upload" )', 'Upload File(s)' );
		setInspControl( 'newFolderCtrl',
		  'expandItem( "newFolderCtrl", "newFolder" )', 'Create a New Folder' );
	} else {
		setInspControl( 'uploadCtrl', '', 'Upload File(s)' );
		setInspControl( 'newFolderCtrl', '', 'Create a New Folder' );
	}

	/*
	 * cut item(s)
	 * don't allow parent or current to be removed
	 */
	if ( itemInfo.numSel > 0 && readPriv && deletePriv ) {
		setInspControl( 'cutCtrl', 'setClipboard( "cut" )',
			'Cut Selected Item(s)' );
	} else {
		setInspControl( 'cutCtrl', '', 'Cut Selected Item(s)' );
	}

	// copy item(s)
	if ( itemInfo.numSel > 0 && readPriv ) {
		setInspControl( 'copyCtrl', 'setClipboard( "copy" )',
		  'Copy Selected Item(s)' );
	} else {
		setInspControl( 'copyCtrl', '', 'Copy Selected Item(s)' );
	}

	// Only show the paste option if there is something in the clipboard
	if ( readCookie( "clipboard" ) && itemInfo.numSel == 0 && insertPriv ) {
		setInspControl( 'pasteCtrl', 'paste()', 'Paste to This Folder' );
	} else {
		setInspControl( 'pasteCtrl', '', 'Paste to This Folder' );
	}

	/*
	 * delete item(s)
	 * delete is weird... you need ldw and either r or i
	 */
	if ( itemInfo.numSel > 0 && deletePriv && 
			( readPriv || insertPriv ) && writePriv ) {
		setInspControl( 'deleteCtrl', 'delFiles( files )',
			'Delete Selected Item(s)' );
	} else {
		setInspControl( 'deleteCtrl', '', 'Delete Selected Item(s)' );
	}

	// rename item
	if ( itemInfo.numSel == 1 && readPriv && insertPriv && deletePriv ) {
		setInspControl( 'renameCtrl', 'rename()', 'Rename Selected Item' );
	} else {
		setInspControl( 'renameCtrl', '', 'Rename Selected Item' );
	}

	// set permissions for folder
	if ( itemInfo.numSel == 1 && files[ itemInfo.lastId ].type == folderMime ) {
		setInspControl( 'permsCtrl', 'permsCtrl_cmd()',
			'Set Permissions for Folder' );
	} else {
		setInspControl( 'permsCtrl', '', 'Set Permissions for Folder' );
	}

	// display information about selected item(s)
	if ( itemInfo.numSel == 0 ) {
		document.getElementById( 'selectedItem' ).innerHTML = foldername;
		document.getElementById( 'itemInfo' ).innerHTML = '';
		document.getElementById( 'inspTitle' ).innerHTML = 'Folder Properties';
	} else if ( itemInfo.numSel == 1 ) {
		document.getElementById( 'selectedItem' ).innerHTML =
			files[itemInfo.lastId].title;
		document.getElementById( 'itemInfo' ).innerHTML = itemInfo.info;
		document.getElementById( 'inspTitle' ).innerHTML =
		  'Selected Items Properties';
	} else {
		document.getElementById( 'selectedItem' ).innerHTML =
			'Multiple Items';
		document.getElementById( 'itemInfo' ).innerHTML = itemInfo.info;
		document.getElementById( 'inspTitle' ).innerHTML =
		  'Selected Items Properties';
	}

	setHiddenFilesCtrl();
}

// Helper function for permsCtrl
function permsCtrl_cmd()
{
	setPMpath();
	expandItem( 'permsCtrl', 'permissions' );
}

// Toggles the display of a hidden object. I don't this this is still used
function toggleDisplay( id )
{
	var obj = document.getElementById( id ).style;
	obj.display = ( obj.display == 'none' ) ? 'block' : 'none';
}

// Process the request to begin a file rename. Create the UI text field to
// allow a rename
function rename()
{
	var ids = getSelectedItems();
	var id = ids[0];
	var tBox = document.createElement( 'input' );
	tBox.setAttribute( 'name', '' );
	tBox.setAttribute( 'size', '15' );
	tBox.setAttribute( 'maxlength', '50' );
	tBox.setAttribute( 'value' , files[id].title );
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
	rButton.onclick = function( id )
			{ finishRename( tBox.value, oldContent, cell ); }
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
					ids.push( j );
			}
	}

	return ids;
}

// Processs the request to delete files
function delFiles()
{
	var ids	 = getSelectedItems();
	var delMsg  = "Are you sure you want to delete:\n";
	var delList = '';

	// Print the list of items to be deleted
	for ( var j = 0; j < ids.length; j++ ) {
			delList += files[ids[j]].title + "\n";
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
			trElem.className = files[checkbox.value].className;
			files[checkbox.value].selected = false;
		} else {
			trElem.className = 'selectedrow';
			files[checkbox.value].selected = true;
		}
	}

	fileInspector();
}

/*
 * Save the value of the clobber files checkbox when checked
 * or unchecked.
 */
function processClobberCheckbox()
{
	setCookie( 'clobberFiles',
			   ( document.getElementById( 'overwrite_box' ).checked ) ? 1 : 0 );
}

function initClobberCheckbox()
{
	clobberFiles = readCookie( 'clobberFiles' );
	clobberFiles = (( clobberFiles == 1 ) ? clobberFiles : 0 );
	document.getElementById( 'overwrite_box' ).checked = clobberFiles;
}

/*
 * Returns the filename of the file with id "id" and returns
 * a properly url-encoded path to it.
 */
function getFilenameUrl( id )
{
	return urlescape( path + '/' + files[id].title );
}

// escape() doesn't handle the "+" character? odd.
function urlescape( str )
{
	str = escape( str );
	str = str.replace( /\+/g, "%2B" );
	return str;
}

// Adds ability to show/hide sidebar sections
function switchMenu(obj) {
        var el = document.getElementById(obj);
        if ( el.style.display != "none" ) {
                el.style.display = 'none';
        }
        else {
                el.style.display = '';
        }
}

