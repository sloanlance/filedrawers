{include file="header.tpl"}
{include file="banner.tpl"}

<div id="error">
<h1>Javascript Version Error</h1>

<h2>Your browser is not officially supported.</h2>

<p>
This application uses javascript extensively for core
functionality. Unfortunately, most web browsers interpret javascript
in their own, unique way. We do not have the resources to support every
combination of browser version and platform. However, there are many
options to choose from.  We currently support the following browsers:
</p>

<ul>
    <li>Camino 0.8+ (Mac)</li>
    <li>Internet Explorer (IE) 6+ (Windows)</li>
    <li>Firefox 1.0+ (Windows, Mac, Linux)</li>
    <li>Safari 1.3+ (Mac)</li>
    <li>Netscape 7.2+ (Windows, Mac, Linux)</li>
    <li>Mozilla 1.7.11+ (Windows, Mac, Linux)</li>
    <li>OmniWeb 5.1.1+ (Mac)</li>
    <li>Opera 8+ (Windows, Linux)</li>
</ul>
</div>

<div id="lone_content">
<h4>Your browser ID:</h4>

<p>{$browser_id}</p>
</div>
