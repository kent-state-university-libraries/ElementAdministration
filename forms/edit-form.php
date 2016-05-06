<?php

/**
 * @package ElementAdministration
 * @author Joe Corall jcorall@kent.edu
 */

class ElementAdministration_EditForm extends Omeka_Form
{
  private $_element = NULL;

  public function init()
  {
      parent::init();
      $this->setMethod('post');
      $this->setAction(url('element-administration/index/save'));
  }

  public function setElement($element) {
      $this->_element = $element;
  }

  public function create() {
      try {
          $this->_registerElements();
      }
      catch (Exception $e) {
        throw $e;
      }
  }

  private function _registerElements()
  {
      $db = get_db();
      $sql = "SELECT c.id, t.text from collections c
        INNER JOIN element_texts t ON t.record_id = c.id AND t.record_type = ?
        INNER JOIN elements e ON e.name = ? AND e.id = t.element_id
        INNER JOIN element_sets s ON s.name = 'Dublin Core' AND s.id = e.element_set_id
        WHERE c.id NOT IN(2,5)";
      $collections = $db->query($sql, array('Collection', 'Title'))->fetchAll();
      $collections[] = array('id' => 0);
      foreach ($collections as $collection) {
          $collection_id = $collection['id'];

          $id = $this->createElement('hidden', 'id');
          $id->setValue($this->_element->id);

          $form_label = $this->createElement('text', 'collection_' . $collection_id . '_form_label');
          $form_label->setLabel('Form Label');
          $form_label->setAttrib('placeholder', $this->_element->name);
          $form_label->setDescription("This will be the label for this element in the Item add/edit form. Leave blank to keep the system's default.");
          if ($collection_id == 0) {
              $description = $this->createElement('textarea', 'collection_' . $collection_id . '_description');
              $description->setLabel('Description');
              $description->setAttribs(array('rows' => 3, 'cols' => 50));
              $description->setValue($this->_element->description);
              $description->setDescription("This will be the description for this element in the Item add/edit form.");
          }

          $hidden_form = $this->createElement('checkbox', 'collection_' . $collection_id . '_hidden_form');
          $hidden_form->setLabel('Hidden on Item Edit Form');
          $hidden_form->setDescription("Check this box to hide this element on the item edit form.");

          $default_value = $this->createElement('textarea', 'collection_' . $collection_id . '_default_value');
          $default_value->setLabel('Default Value');
          $default_value->setAttribs(array('rows' => 3, 'cols' => 50));
          $default_value->setDescription("This will be the default value for this element in the Item add/edit form.");

          $required = $this->createElement('checkbox', 'collection_' . $collection_id . '_required');
          $required->setLabel('Required');
          $required->setDescription("Checking this box will force this element to be required in the Item add/edit form.");

          $html = $this->createElement('checkbox', 'collection_' . $collection_id . '_html');
          $html->setLabel('Allow HTML Input');
          $html->setDescription("Check this box to allow users to enter HTML for this element.");

          $multiple = $this->createElement('checkbox', 'collection_' . $collection_id . '_multiple');
          $multiple->setLabel('Allow Multiple Values');
          $multiple->setDescription("Check this box to allow users to add more than one value for this element.");

          $public_label = $this->createElement('text', 'collection_' . $collection_id . '_public_label');
          $public_label->setLabel('Public Label');
          $public_label->setAttrib('placeholder', $this->_element->name);
          $public_label->setDescription("This will be the label for the public display of this element. Leave blank to keep the system's default.");

          $hidden_public = $this->createElement('checkbox', 'collection_' . $collection_id . '_hidden_public');
          $hidden_public->setLabel('Hidden on Public Display');
          $hidden_public->setDescription("Check this box to hide this element from the public.");

          $brief_display = $this->createElement('checkbox', 'collection_' . $collection_id . '_brief_display');
          $brief_display->setLabel('Brief Display');
          $brief_display->setDescription("Allow this element to show in the brief display on the public item view.");

          // see if any administration settings already stored in the db for this element
          $settings = $this->_getElementAdministrationSettings($this->_element->id, $collection_id);
          if ($collection_id != 0) {
             $default_settings = $this->_getElementAdministrationSettings($this->_element->id, 0);
          }
          $_elements = array(
              'id',
              'form_label',
              'description',
              'hidden_form',
              'default_value',
              'required',
              'html',
              'multiple',
              'public_label',
              'hidden_public',
              'brief_display',
          );
          $elements = array();
          foreach ($_elements as $element) {
              if (isset($settings->{$element})) {
                  $$element->setValue($settings->{$element});
              }
              elseif ($collection_id != 0 && isset($default_settings->{$element})) {
                $$element->setValue($default_settings->{$element});
              }
              // if visiting an element for the first time in this plugin
              // set the default value to be Omeka's default
              else {
                switch ($element) {
                  case 'html':
                  case 'multiple':
                    $$element->setValue(1);
                    break;
                }
              }
              if (isset($$element)) {
                  $elements[] = $$element;
              }
          }
          $this->addElements($elements);
      }
  }

  protected function _getElementAdministrationSettings($element_id, $collection_id = 0) {
      return get_db()
          ->getTable('ElementAdministrationSettings')
          ->findByElementId($element_id, $collection_id);
  }
}
