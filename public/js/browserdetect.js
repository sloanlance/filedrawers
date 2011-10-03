var supported = false;

if (YAHOO.env.ua.ie     >= 8)   supported = true;
if (YAHOO.env.ua.gecko  >= 4)   supported = true;
if (YAHOO.env.ua.webkit >= 500) supported = true;

if (!supported) {
    window.location = 'notSupported';
}
