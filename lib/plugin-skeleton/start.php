<?php
/**
 * PLUGINNAME plugin
 */

elgg_register_event_handler('init', 'system', 'PLUGINNAME_init');

/**
 * Init PLUGINNAME plugin.
 */
function PLUGINNAME_init() {

	// add a site navigation item
	$item = new ElggMenuItem('PLUGINNAME', elgg_echo('PLUGINNAME:PLURAL'), 'PLUGINNAME/all');
	elgg_register_menu_item('site', $item);

    // OPTIONAL
	// add to the main css
	//elgg_extend_view('css/elgg', 'PLUGINNAME/css');

	// routing of urls
	elgg_register_page_handler('SUBTYPE', 'PLUGINNAME_page_handler');

	// override the default url to view entity
	elgg_register_entity_url_handler('object', 'SUBTYPE', 'PLUGINNAME_url_handler');

	// notifications
	register_notification_object('object', 'SUBTYPE', elgg_echo('PLUGINNAME:notification:new:subject'));
	elgg_register_plugin_hook_handler('notify:entity:message', 'object', 'PLUGINNAME_notify_message');

	// add entity link to menu block
	elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'PLUGINNAME_owner_block_menu');

	// Register for search.
	elgg_register_entity_type('object', 'SUBTYPE');

	// Add group option
	add_group_tool_option('SUBTYPE', elgg_echo('PLUGINNAME:group:enable'), true);
	elgg_extend_view('groups/tool_latest', 'PLUGINNAME/group_module');

	// add a widget
	elgg_register_widget_type('SUBTYPE', elgg_echo('PLUGINNAME'), elgg_echo('PLUGINNAME:widget:description'));

	// register actions
	$action_path = elgg_get_plugins_path() . 'PLUGINNAME/actions/default';
	elgg_register_action('PLUGINNAME/save', "$action_path/save.php");
	elgg_register_action('PLUGINNAME/delete', "$action_path/delete.php");

	// ecml
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'PLUGINNAME_ecml_views_hook');
}

/**
 * Dispatches Tools pages.
 * URLs take the form of
 *  View all:       PLUGINNAME/all
 *  View User's:    PLUGINNAME/owner/<username>
 *  View Friends':  PLUGINNAME/friends/<username>
 *  View entity:    PLUGINNAME/view/<guid>/<title>
 *  New entity:     PLUGINNAME/add/<guid>
 *  Edit entity:    PLUGINNAME/edit/<guid>/<revision>
 *  Group View all: PLUGINNAME/group/<guid>/all
 *
 * Title is ignored
 *
 * @param array $page The parameters to the page, as an array
 * @param string $handler The name of the handler type
 * 
 * @return bool 
 */
function PLUGINNAME_page_handler($page, $handler) {

	// push view all page breadcrumb
	elgg_push_breadcrumb(elgg_echo('PLUGINNAME:PLURAL'), "PLUGINNAME/all");

    // if no page set, default to view all page
	if (!isset($page[0])) {
		$page[0] = 'all';
	}
    
    // set base path of pages
    $pages = dirname(__FILE__) . '/pages/PLUGINNAME';

    // handle page
	switch ($page[0]) {
		case "all":
			include "$pages/all.php";
			break;

		case "owner":
			include "$pages/owner.php";
			break;

		case "friends":
			include "$pages/friends.php";
			break;

		case "view":
			set_input('guid', $page[1]);
			include "$pages/view.php";
			break;

		case "add":
			gatekeeper();
            set_input('new', true);
			include "$pages/edit.php";
			break;

		case "edit":
			gatekeeper();
			set_input('guid', $page[1]);
			include "$pages/edit.php";
			break;

		case 'group':
			group_gatekeeper();
			include "$pages/owner.php";
			break;

		default:
			return false;
	}
    
    elgg_pop_context();
	return true;
}

/**
 * Format and return the URL for PLUGINNAME
 *
 * @param ElggEntity $entity PLUGINNAME entity
 * @return string URL of entity
 */
function PLUGINNAME_url_handler($entity) {
	if (!$entity->getOwnerEntity()) {
		// default to a standard view if no owner.
		return FALSE;
	}

	$friendly_title = elgg_get_friendly_title($entity->title);

	return "PLUGINNAME/view/{$entity->guid}/$friendly_title";
}

/**
 * Add a menu item to an ownerblock
 * 
 * @param string $hook Hook name
 * @param string $type Hook type
 * @param mixed $returnvalue Current array for block menu
 * @param mixed $params Parameters including entity
 * 
 * @return array Array for block menu
 */
function PLUGINNAME_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		$url = "PLUGINNAME/owner/{$params['entity']->username}";
		$item = new ElggMenuItem('PLUGINNAME', elgg_echo('PLUGINNAME'), $url);
		$return[] = $item;
	} else {
        $url = "PLUGINNAME/group/{$params['entity']->guid}/all";
        $item = new ElggMenuItem('PLUGINNAME', elgg_echo('PLUGINNAME:group'), $url);
        $return[] = $item;
	}

	return $return;
}

/**
 * Set the notification message body
 * 
 * @param string $hook Hook name
 * @param string $type Hook type
 * @param mixed $returnvalue The current message body
 * @param mixed $params Parameters about the posted entity
 * 
 * @return string Notification message
 */
function PLUGINNAME_notify_message($hook, $type, $message, $params) {
	$entity = $params['entity'];
	if (elgg_instanceof($entity, 'object', 'SUBTYPE')) {
		$title = $entity->title;
        $descr = elgg_get_excerpt($entity->description);
		$owner = $entity->getOwnerEntity();
		return elgg_echo('PLUGINNAME:notification:new:body', array(
			$owner->name,
			$title,
			$descr,
			$entity->getURL()
		));
	}
	return null;
}

/**
 * Register with ECML
 * 
 * @param string $hook Hook name
 * @param string $type Hook type
 * @param mixed $returnvalue An initial return value
 * @param mixed $params Additional parameters
 * 
 * @return string Notification message
 */
function PLUGINNAME_ecml_views_hook($hook, $entity_type, $return_value, $params) {
    
	$return_value['object/SUBTYPE'] = elgg_echo('PLUGINNAME:PLURAL');

	return $return_value;
}
