<?php
/**
 * Group PLUGINNAME module
 */

$group = elgg_get_page_owner_entity();

$all_link = elgg_view('output/url', array(
	'href' => "PLUGINNAME/group/$group->guid/all",
	'text' => elgg_echo('link:view:all'),
	'is_trusted' => true,
));

elgg_push_context('widgets');
$options = array(
	'type' => 'object',
	'subtype' => 'SUBTYPE',
	'container_guid' => elgg_get_page_owner_guid(),
	'limit' => 6,
	'full_view' => false,
	'pagination' => false,
);
$content = elgg_list_entities_from_metadata($options);
elgg_pop_context();

if (!$content) {
	$content = '<p>' . elgg_echo('PLUGINNAME:none') . '</p>';
}

$new_link = elgg_view('output/url', array(
	'href' => "PLUGINNAME/add/$group->guid",
	'text' => elgg_echo('PLUGINNAME:write'),
	'is_trusted' => true,
));

echo elgg_view('groups/profile/module', array(
	'title' => elgg_echo('PLUGINNAME:group'),
	'content' => $content,
	'all_link' => $all_link,
	'add_link' => $new_link,
));
