<?php
/**
 * Default form field template for input
 * Displays a input field formatted with a label and description
 *
 * @uses $vars['input']         Input field
 * @uses $vars['label']         Field Label
 * @uses $vars['description']   Field description. Optional
 */
?>
<div>
    <label><?php echo elgg_echo($vars['label']); ?></label><br />
    <?php echo $vars['input']; ?>
    <?php if (isset($vars['description'])) { ?>
        <span class="description"><?php echo elgg_echo($vars['description']); ?></span>
    <?php } ?>
</div>