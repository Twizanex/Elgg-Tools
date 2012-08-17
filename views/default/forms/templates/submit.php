<?php
/**
 * Form submit
 * Displays a submit button on form layout
 *
 * @uses $vars['value'] Button text
 */
?>
<?php echo elgg_view('input/submit', array('name' => 'submit', 'value' => $vars['value'])); ?>