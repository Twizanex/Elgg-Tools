<?php
/**
 * Add/Edit PLUGINNAME page
 */

// get input
$new = get_input('new');
$guid = get_input('guid');

// get entity
if ($new) {
    $entity = new ENTITY_CLASS();
}
else {
    $entity = get_entity($guid);
    if (!elgg_instanceof($entity, 'object', 'SUBTYPE') || !$entity->canEdit()) {
        register_error(elgg_echo('PLUGINNAME:error:cannot_edit'));
        forward(REFERRER);
    }
}

// set title and breadcrumb
if ($new) {
    $title = elgg_echo('PLUGINNAME:add');
}
else {
    $title = elgg_echo('PLUGINNAME:edit');
}
elgg_push_breadcrumb($title);

// get form content
$form = new Form($entity);
$content = $form->display();

$body = elgg_view_layout('content', array(
	'filter' => '',
	'content' => $content,
	'title' => $title,
));

echo elgg_view_page($title, $body);