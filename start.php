<?php
/**
 * Tools plugin
 */

elgg_register_event_handler('init', 'system', 'tools_init');

/**
 * Tools init
 */
function tools_init() {
    
    // include functions
	$root = dirname(__FILE__);
	elgg_register_library('tools:functions', "$root/lib/functions.php");
    
    // load functions
    elgg_load_library('tools:functions');

	// register URL handlers
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', 'tools_object_icon_url_override');
    
    // register page handlers
	elgg_register_page_handler('icon', 'tools_page_handler');
    elgg_register_page_handler('files', 'tools_page_handler');
    
    // search hooks
}

/**
 * Dispatches Tools pages.
 * URLs take the form of
 *  Icons:       icon
 *
 * Title is ignored
 *
 * @param array $page The parameters to the page, as an array
 * @param string $handler The name of the handler type
 * 
 * @return bool 
 */
function tools_page_handler($page, $handler) {
    
	switch ($handler) {
		case 'icon':
            
            // set icon params
            if (isset($page[0])) {
                set_input('guid', $page[0]);
            }
            if (isset($page[1])) {
                set_input('size', $page[1]);
            }
            
            // include icon
            $plugin_dir = elgg_get_plugins_path();
            include("$plugin_dir/tools/icon.php");
            
			break;
            
        case 'files':
            
            // set icon params
            if (isset($page[0])) {
                set_input('guid', $page[0]);
            }
            if (isset($page[1])) {
                set_input('fieldname', $page[1]);
            }
            if (isset($page[2])) {
                set_input('size', $page[2]);
            }
            
            // include file
            $plugin_dir = elgg_get_plugins_path();
            include("$plugin_dir/tools/file.php");
            
			break;
            
		default:
			return false;
	}
    
	return true;
}

/**
 * Override the default entity icon for objects
 * 
 * @param string $hook Hook name
 * @param string $type Hook type
 * @param mixed $returnvalue An initial return value
 * @param mixed $params Additional parameters
 * 
 * @return mixed|null The return value
 */
function tools_object_icon_url_override($hook, $type, $returnvalue, $params) {
	
    // get params
	$object = $params['entity'];
	$size = $params['size'];
    
    // get and return image URL
	$url = get_file_url($object, 'icon', $size);
    if (!$url) {
        $url = "_graphics/icons/default/{$size}.png";
    }
    return $url;
}
