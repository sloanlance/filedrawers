<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>
<?php
require_once('version.php');
require_once('config.php');
?>
<?php echo "$service_name"; ?>
: Web File Access</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="/css/styles.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" text="#00338B" link="#0044BA" vlink="#4F658B" alink="#005DFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<!-- ITCS header -->
<table width="100%" border="0" cellpadding="0" cellspacing="0">
    <tr>
	<td align="left" class="color-headbar-blue">
	    <a href="http://www.itd.umich.edu/" class="titleImg"><img src="http://www.itd.umich.edu/images/itcs-banner.gif" width="455" height="20" border="0" alt="information technology central services at the university of michigan" /></a>
	</td>
	<td align="right" class="color-headbar-blue">
	    <img src="http://www.itd.umich.edu/date/<?= date( "l" ) ?>.gif" alt="<?= date( "l" ) ?>" /><img src="http://www.itd.umich.edu/date/<?= date( "F" ) ?>.gif" alt="<?= date( "F" ) ?>" /><img src="http://www.itd.umich.edu/date/<?= date( "d" ) ?>.gif" alt="<?= date( "d" ) ?>" /><img src="http://www.itd.umich.edu/date/<?= date( "Y" ) ?>.gif" alt="<?= date( "Y" ) ?>" />
	</td>
    </tr>
</table>
<!-- end ITCS header -->


<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td class="color-headbar-blue"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td align="left"><img src="/images/header_afs_cabinet.gif" width="98" height="120" hspace="6"><img src="/images/header_text_afs.gif" alt="
<?php echo "$service_name "; ?>
 - AFS file management" width="258" height="120"></td>
        <td align="right" valign="middle"><a href="http://www.itd.umich.edu/help/"><img src="/images/first_time.gif" alt="Do you require assistance?" width="232" height="30" border="0"></a><img src="/images/spacer.gif" width="25" height="1"></td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td class="color-divbar-gold"><img src="/images/spacer.gif" width="1" height="1"></td>
  </tr>
  <tr>
    <td class="contentarea"><table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="bottom" class="searchtab-r"><img src="/images/searchtab_l.gif" width="9" height="49"></td>
            <td valign="middle" class="searchbox"><a href="https://<?= $_SERVER[ 'SERVER_NAME' ] ?>/"><img src="/images/login-anim.gif" alt="Click to Log-In" width="88" height="28" border="0" class="linkpad"></a><img src="/images/spacer.gif" width="9" height="1"><a href="https://<?= $_SERVER[ 'SERVER_NAME' ] ?>/" class="searchbox-link">Log in to access your UM IFS file space via the web now.</a></td>
            <td valign="bottom" class="searchtab-r"><img src="/images/searchtab_r.gif" width="9" height="49"></td>
          </tr>
        </table>
          <br>
          <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td class="cont-textbox1"><table border="0" cellspacing="0" cellpadding="0">
                  <tr>
                    <td nowrap class="tab-text">welcome...</td>
                    <td class="tab-cap"><img src="/images/blue_tabcap_r.gif" width="8" height="24"></td>
                  </tr>
                </table>
                  <p class="content-text"><strong>
<?php echo "$service_name"; ?>
</strong> gives you secure access to your U of M IFS space from any computer with an Internet connection. Our <a href="http://www.itd.umich.edu/itcsdocs/s4311/">online documentation</a> should help get you started.
				  <br />Need help? Call (734) 764-HELP (764-4357) for assistance.
                  </p>
              </td>
            </tr>
          </table>
		</td>
        <td class="colspace1">&nbsp;</td>
        <td width="275" valign="top"><br>          
          <table width="100%" border="0" cellpadding="0" cellspacing="0" class="cont-tipbox">
          <tr>
            <td colspan="2" class="tipbox-header">IFS news & tips</td>
          </tr>
          <tr>
	  <td valign="top" class="tipbox-img-white">
	      <img src="/images/bulb_small.gif" width="14" height="20">
	  </td>
	<td class="tipbox-text-white"><p>Access the most recent back of your files in mFile using <a href="http://www.itd.umich.edu/itcsdocs/s4110/">oldfiles</a></p>
	  </td>
      </tr>
      <tr>
	  <td valign="top" class="tipbox-img-blue">
	      <img src="/images/bulb_small.gif" width="14" height="20">
	  </td>
	<td class="tipbox-text-blue"><p>You can <a href="http://www.itd.umich.edu/itcsdocs/s4311/#publishing">publish a web site</a> using mFile and your home directory</p>
              </td>
          </tr>
      <tr>
	  <td valign="top" class="tipbox-img-white">
	      <img src="/images/bulb_small.gif" width="14" height="20">
	  </td>
	<td class="tipbox-text-white"><p>Check your IFS usage and quota from the <a href="https://accounts.www.umich.edu/umce-bin/umce"> UMCE Balances & Subscriptions page</a></p>
              </td>
          </tr>
          <tr>
	  <td valign="top" class="tipbox-img-blue">
	      <img src="/images/bulb_small.gif" width="14" height="20">
	  </td>
            <td class="tipbox-text-blue"><p>If you would like to request group or class afs space, please contact  <a href = "http://www.umich.edu/cgi-bin/htmail/ifs-support@umich.edu"> IFS support </a></p>
            </td>
          </tr>
          <tr>
	  <td valign="top" class="tipbox-img-white">
	      <img src="/images/bulb_small.gif" width="14" height="20">
	  </td>
            <td class="tipbox-text-white"><p>JavaScript must be active in your browser for most mFile features.</p>
                </td>
          </tr>
        </table>
          <br>
          </td>
      </tr>
    </table>      
      <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
          <td>&nbsp;</td>
        </tr>
        <tr>
          <td class="footerbar"><a href="https://<?= $_SERVER[ 'SERVER_NAME' ] ?>" class="footerlink">manage files (log in)</a> | <a href="https://accounts.www.umich.edu/umce-bin/umce" class="footerlink">view current quota</a> | <a href="http://www.itd.umich.edu/4help/" class="footerlink">help hotline info</a> | <a href="http://www.umich.edu/~umweb/contact/" class="footerlink">contact webmaster</a></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>


