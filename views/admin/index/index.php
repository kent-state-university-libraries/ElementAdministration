<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

echo head($head);
echo flash();

?>

For each metadata element in the system, you can set:
<ul>
  <li>
    <strong>Form Label</strong> -
    <span>Override the label shown in the "Add/Edit an Item" forms.</span>
  </li>
  <li>
    <strong>Default Value</strong> -
    <span>Set a default value for this element in the "Add an Item" form.</span>
  </li>
  <li>
    <strong>Required</strong> -
    <span>Make this element required in the "Add/Edit an Item" forms</span>
  </li>
  <li>
    <strong>Allow HTML</strong> -
    <span>Control whether to display the "HTML" checkbox for this element</span>
  </li>
  <li>
    <strong>Allow Multiple Values</strong> -
    <span>Control whether to display the "Add Input" button for this element</span>
  </li>
  <li>
    <strong>Public Label</strong> -
    <span>Override the label shown to the public for this element.</span>
  </li><!--
  <li>
    <strong>Brief Display</strong> -
    <span>Show this element in the "Brief Display" view of on your public item view.
      If no elements are selected for "Brief Display", all elements will be shown.
      If one or more are selected, a button will appear for this Element Set allowing users to view "Brief" or "Full" record displays
    </span>
  </li>-->
</ul>

<table>
  <tbody>
    <?php while ($element = $elements->fetchObject()): ?>
        <?php if ($element->set_name !== $current_element_set): ?>
            <tr>
              <th>
                <?php echo $element->set_name; ?>
                <?php $current_element_set = $element->set_name; ?>
              </th>
            </tr>
        <?php endif; ?>
        <tr>
          <td>
            <a href="<?php echo url("element-administration/index/edit/id/{$element->id}");?>"><?php echo $element->name; ?></a>
          </td>
        </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<?php echo foot(); ?>
