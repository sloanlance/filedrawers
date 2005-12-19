{include file="masthead.tpl"}

<div id="viewfile">
{php}
	global $afs;
	preg_match( '/^([^\/]+)\/?([^; ]*).*$/', $afs->mimetype, $Matches );
	$mType = $Matches[1];
	$mSubtype = $Matches[2];
	$supported = 1;

	echo "<!-- MIME: [$afs->mimetype] -->\n";
	switch( $mType ) {
		case 'image': 
			if ( $mSubtype == 'bmp' ) {
				$supported = 0;
				break;
			}
			echo '<img src="/download/view.php?path='.
				urlencode( $afs->path ).'" />';
			break;
		case 'audio': 
		case 'video': 
			echo '<embed src="/download/view.php?path='.
				urlencode( $afs->path ).'" type="'.$afs->mimetype.'"/>';
			break;
		case 'html': 
		case 'text': 
			if ( $mSubtype == 'rtf' ) {
				$supported = 0;
			} else {
				highlight_file( $afs->path );
			}
			break;
		case 'application':
			if ( $mSubtype == 'x-shockwave-flash' ) {
				echo '<embed src="/download/view.php?path='.
					urlencode( $afs->path ).'" type="'.$afs->mimetype.'"/>';
				break;
			}
			$supported = 0;
			break;
		default:
			$supported = 0;
			break;
		}

	if ( !$supported ) {
		$dl_link = '<a href="/download/?path='.$afs->path.'">';
		echo '<div id="error">'.
			"<h2>Unsupported MIME type</h2>\n".
			'<p>This file uses a MIME type ('.$afs->mimetype.
			") which is not viewable in most web browers.</p>\n".
			'<p>You may '.$dl_link.'download</a> '.
			$dl_link.'<img src="/images/download.gif" width="16" '.
			'height="16" alt="Download"></a> '.
			'this file, or <a href="/?path='.$afs->parPath.'">return '.
			'to the parent directory</a>.</p>'.
			'</div>';
	}

{/php}
</div>

{include file="footer.tpl"}
