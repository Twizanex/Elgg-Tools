<?php
/**
 * Errors format to display in system messages
 *
 * @uses $vars['error'] Main error message
 * @uses $vars['errors'] Array of error messages to display. Optional
 */
?>
<div><strong><?php echo $vars['error']; ?></div>
<?php foreach ($vars['errors'] as $error) { ?>
<div> - <?php echo $error; ?></div>
<?php } ?>
