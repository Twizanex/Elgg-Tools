<?php
/**
 * PLUGINNAME plugin friends page
 */

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner) {
	forward('PLUGINNAME/all');
}

elgg_push_breadcrumb($page_owner->name, "PLUGINNAME/owner/$page_owner->username");
elgg_push_breadcrumb(elgg_echo('friends'));

elgg_register_title_button();

$title = elgg_echo('PLUGINNAME:friends');

$content = list_user_friends_objects($page_owner->guid, 'SUBTYPE', 10, false);
if (!$content) {
	$content = elgg_echo('PLUGINNAME:none');
}

$params = array(
	'filter_context' => 'friends',
	'content' => $content,
	'title' => $title,
);

$body = elgg_view_layout('content', $params);

echo elgg_view_page($title, $body);
