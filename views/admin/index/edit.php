<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

echo head(array('title' => __($element->name . ' | Element Administration')));

echo flash();
?>

<h2>Set the "<?php echo $element->name; ?>" element's input and display settings</h2>

<div>
  <?php echo $form; ?>
</div>

<?php echo foot(); ?>
