<?php

// Include your Nagios server IP below
// It is safe to keep 127.0.0.1
$allowed_ips = array(
	'127.0.0.1',
);

// If your Wordpress installation is behind a Proxy like Nginx use 'HTTP_X_FORWARDED_FOR'
if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$remote_ip = $_SERVER['REMOTE_ADDR'];
}

// Check if the requesting server is allowed
if (! in_array($remote_ip, $allowed_ips))
{
	echo "CRITICAL#IP $remote_ip not allowed.";
	exit;
}

require_once('wp-load.php');

global $wp_version;
$core_updates = FALSE;
$plugin_updates = FALSE;

wp_version_check();
wp_update_plugins();
wp_update_themes();

if (function_exists('get_transient'))
{
	$core = get_transient('update_core');
	$plugins = get_transient('update_plugins');
	$themes = get_transient('update_themes');

	if ($core == FALSE)
	{
		$core = get_site_transient('update_core');
		$plugins = get_site_transient('update_plugins');		
		$themes = get_site_transient('update_themes');
	}
}
else
{
	$core = get_site_transient('update_core');
	$plugins = get_site_transient('update_plugins');
	$themes = get_site_transient('update_themes');
}

$status = 'OK';
$text = [];
// Parse the plugin check and generate a list of plugins needing updates.
if ($plugins->response ?? FALSE) {
	$plugin_text = 'Plugin update(s) available: (';
	foreach ($plugins->response as $plugin) {
		$plugin_text .= "$plugin->slug: $plugin->new_version; ";
	}

	$plugin_text = substr($plugin_text, 0, -2) . ')';
	$text[] = $plugin_text;
	$status = 'WARNING';
}

// Parse the theme check and generate a list of themes needing updates.
if ($themes->response ?? FALSE) {
	$theme_text = 'Theme update(s) available: (';
	foreach ($themes->response as $theme) {
		$theme_text .= "{$theme['theme']}: {$theme['new_version']}; ";
	}

	$theme_text = substr($theme_text, 0, -2) . ')';
	$text[] = $theme_text;
	$status = 'WARNING';
}

// Parse the core check last, since a CRITICAL status overrides a WARNING.
foreach ($core->updates as $core_update)
{
	if ($core_update->current != $wp_version)
	{
	        $text[] = 'Core update available';
		$status = 'CRITICAL';
	}
}

echo $status . '#' . implode($text, '; ');
