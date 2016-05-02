<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

echo head(array('title' => __($element->name . ' | Element Administration')));

echo flash();
?>

<ul id="section-nav" class="navigation tabs">
  <?php foreach ($collections as $id => $collection): ?>
    <li<?php if (!$id) { ?> class="active"<?php }?>><a href="#<?php echo $id; ?>"><?php echo $collection; ?></a></li>
  <?php endforeach; ?>
</ul>
 <h2>Set the "<?php echo $element->name; ?>" element's input and display settings</h2>
<section id="collection-settings" class="seven columns alpha">
<div>
      <?php echo $form; ?>

</div>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" class="big green button" name="submit" value="<?php echo __('Save Changes'); ?>">
    </div>
    <script type="text/javascript">
      (function($) {
        $(document).ready(function() {
          $('#section-nav a').click(function() {
            $('#section-nav li').removeClass('active');
            $(this).parent().addClass('active');
            $('#collection-settings div[id^="collection_"]').parent().hide();
            $('#collection-settings div[id^="collection_' + $(this).attr('href').substr(1) + '"]').parent().show();
          });

          $('input[type="submit"]').on('click', function() {
            $('input[type="submit"]').attr('disabled','disabled');
            $('#collection-settings form').each(function() {
              $(this).submit();
            });
            $('input[type="submit"]').attr('disabled','');
          });
          $('#section-nav .active a').click();
        });

      })(jQuery)
    </script>
</section>
<?php echo foot(); ?>
