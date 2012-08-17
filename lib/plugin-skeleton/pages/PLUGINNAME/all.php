<?php
/**
 * PLUGINNAME plugin everyone page
 */

elgg_pop_breadcrumb();
elgg_push_breadcrumb(elgg_echo('PLUGINNAME'));

elgg_register_title_button();

$content = elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'SUBTYPE',
	'limit' => 10,
	'full_view' => false,
	'view_toggle_type' => false
));

if (!$content) {
	$content = elgg_echo('PLUGINNAME:none');
}

$title = elgg_echo('PLUGINNAME:title:all');

$body = elgg_view_layout('content', array(
	'filter_context' => 'all',
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('PLUGINNAME/sidebar'),
));

echo elgg_view_page($title, $body);