<?php
/**
 * View for PLUGINNAME objects
 */

$full = elgg_extract('full_view', $vars, FALSE);
$entity = elgg_extract('entity', $vars, FALSE);

if (!elgg_instanceof($entity, 'object', 'SUBTYPE')) {
	return TRUE;
}

$owner = $entity->getOwnerEntity();
$categories = elgg_view('output/categories', $vars);
$excerpt = elgg_get_excerpt($entity->description);

// if entity has no icon use owner
//$icon = elgg_view_entity_icon($owner, 'tiny');

// if entity has icon use entity itself
$icon = elgg_view_entity_icon($entity, 'tiny');

$owner_link = elgg_view('output/url', array(
	'href' => "PLUGINNAME/owner/$owner->username",
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($entity->time_created);

// comments
$comments_count = $entity->countComments();
//only display if there are commments
if ($comments_count != 0) {
    $text = elgg_echo("comments") . " ($comments_count)";
    $comments_link = elgg_view('output/url', array(
        'href' => $entity->getURL() . '#PLUGINNAME-comments',
        'text' => $text,
        'is_trusted' => true,
    ));
} else {
    $comments_link = '';
}

$metadata = elgg_view_menu('entity', array(
	'entity' => $vars['entity'],
	'handler' => 'PLUGINNAME',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

$subtitle = "$author_text $date $comments_link $categories";

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

if ($full) {

	$body = elgg_view('output/longtext', array(
		'value' => $entity->description,
		'class' => 'PLUGINNAME-post',
	));

	$params = array(
		'entity' => $entity,
		'title' => false,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
	);
	$params = $params + $vars;
	$summary = elgg_view('object/elements/summary', $params);

	echo elgg_view('object/elements/full', array(
		'summary' => $summary,
		'icon' => $icon,
		'body' => $body,
	));

} else {
	// brief view

	$params = array(
		'entity' => $entity,
		'metadata' => $metadata,
		'subtitle' => $subtitle,
		'content' => $excerpt,
	);
	$params = $params + $vars;
	$list_body = elgg_view('object/elements/summary', $params);

	echo elgg_view_image_block($icon, $list_body);
}
