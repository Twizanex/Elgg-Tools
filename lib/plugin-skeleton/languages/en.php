<?php
/**
 * Default English language file.
 */

$english = array(
	'PLUGINNAME' => 'PLURAL_TEXT',
    'item:object:SUBTYPE' => 'PLURAL_TEXT',
    'PLUGINNAME:SINGULAR' => 'SINGULAR_TEXT',
	'PLUGINNAME:PLURAL' => 'PLURAL_TEXT',

	'PLUGINNAME:title:user' => '%s\'s PLURAL_TEXT_LOWER',
	'PLUGINNAME:title:all' => 'All site PLURAL_TEXT_LOWER',
	'PLUGINNAME:title:friends' => 'Friends\' PLURAL_TEXT_LOWER',

	'PLUGINNAME:group' => 'Group SINGULAR_TEXT_LOWER',
	'PLUGINNAME:group:enable' => 'Enable group SINGULAR_TEXT_LOWER',
	'PLUGINNAME:write' => 'Write a SINGULAR_TEXT_LOWER',

	// Editing
	'PLUGINNAME:add' => 'Add SINGULAR_TEXT_LOWER',
	'PLUGINNAME:edit' => 'Edit SINGULAR_TEXT_LOWER',

	// messages
	'PLUGINNAME:message:saved' => 'SINGULAR_TEXT saved.',
	'PLUGINNAME:error:cannot_save' => 'Cannot save SINGULAR_TEXT_LOWER.',
	'PLUGINNAME:message:deleted' => 'SINGULAR_TEXT deleted.',
	'PLUGINNAME:error:cannot_delete' => 'Cannot delete SINGULAR_TEXT_LOWER.',
	'PLUGINNAME:none' => 'No SINGULAR_TEXT_LOWER',
	'PLUGINNAME:error:missing:title' => 'Please enter a SINGULAR_TEXT_LOWER title!',
	'PLUGINNAME:error:missing:description' => 'Please enter the body of your SINGULAR_TEXT_LOWER!',
	'PLUGINNAME:error:cannot_edit' => 'This SINGULAR_TEXT_LOWER may not exist or you may not have permissions to edit it.',

	// river
	'river:create:object:SUBTYPE' => '%s saved SINGULAR_TEXT_LOWER %s',
	'river:comment:object:SUBTYPE' => '%s commented on the SINGULAR_TEXT_LOWER %s',

	// notifications
	'PLUGINNAME:notification:new:subject' => 'A new SINGULAR_TEXT_LOWER',
	'PLUGINNAME:notification:new:body' =>
'
%s made a new SINGULAR_TEXT_LOWER.

%s
%s

View and comment on the new SINGULAR_TEXT_LOWER:
%s
',

	// widget
	'PLUGINNAME:widget:description' => 'Display your latest SINGULAR_TEXT_LOWER',
	'PLUGINNAME:more' => 'More SINGULAR_TEXT_LOWER',
	'PLUGINNAME:numbertodisplay' => 'Number of SINGULAR_TEXT_LOWER to display',
	'PLUGINNAME:noavailible' => 'No SINGULAR_TEXT_LOWER'
);

add_translation('en', $english);
