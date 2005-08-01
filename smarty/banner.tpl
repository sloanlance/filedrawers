<div id="itcsBanner">
	<div id="date">
	{$smarty.now|date_format:"%A"}
		<strong>{$smarty.now|date_format:"%B %e"}</strong>
	{$smarty.now|date_format:"%Y"}
	</div>

	<div id="itcs">
	<a href="http://www.itcs.umich.edu">
		<strong>Information Technology Central Services</strong>
	</a>
	at the <a href="http://www.umich.edu">University of Michigan</a>
	</div>
</div>

<div id="banner">
<h1>mFile: {$trouser_title}</h1>
<ul id="menubar">
	{if $homeSelected}
		<li><a href="/" class="active">Home</a></li>
	{else}
		<li><a href="/">Home</a></li>
	{/if}
	<li><a href="/make-webspace/">Web Sites</a></li>
	<li><a href="{$secure_service_url}/cgi-bin/logout?{$service_url}/">Logout</a></li>
</ul>
</div>
