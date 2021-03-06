<?php
$config = array(
	'top_menu'          => array(
		'home',
		'suggest',
		'rate',
		'addressing',
		'progress',
		'catalogue',
		'quality_control',
		'conspectus'),

	'date_format'       => 'Y-m-d H:i:s',

	'short_date_format' => 'd.m.Y',

	'title_length'      => 35);

// Application version info
$config['version'] = '2.35';
$config['build'] = 1;

/**
 * Enable debug mode. Display errors and profiler info.
 * Error messages can be set by variable $this->template->debug
 */
$config['debug_mode'] = TRUE;
// This prevents accidental change on production in test mode
$config['production_db_name'] = 'wadmin';
$config['administrator_email'] = 'brokes@webarchiv.cz';

// URL for external systems
$config['ticket_url'] = 'https://github.com/WebArchivCZ/WA-Admin/issues/new';
$config['wayback_url'] = 'http://har.webarchiv.cz:8080/AP1/query?type=urlquery&amp;Submit=Take+Me+Back&amp;url=';

// Screenshot variables
//  url to screenshot dir on server
$config['url_path_screenshots'] = "/media/screenshots/";
//  absolute path
$config['screenshots_dir'] = "D:\\xampplite\\htdocs\\wadmin\\media\\screenshots\\";

//  prefix for full screenshots
$config['full_screenshot_prefix'] = 'big_';
//  prefix for thumbnails
$config['thumbnail_screenshot_prefix'] = 'small_';
