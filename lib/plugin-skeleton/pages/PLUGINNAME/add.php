<?php
/**
 * @TODO DELTE THIS FILE SINCE EDIT IS NOW USED FOR BOTH
 * Add PLUGINNAME page
 */

$page_owner = elgg_get_page_owner_entity();

$title = elgg_echo('PLUGINNAME:add');
elgg_push_breadcrumb($title);

$entity = new ENTITY_CLASS();
$form = new Form($entity);
$content .= $form->display();

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);