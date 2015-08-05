<?php

/**
 * @package ElementAdministration
 * @author jcorall@kent.edu
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

      $id = $this->createElement('hidden', 'id');
      $id->setValue($this->_element->id);

      $form_label = $this->createElement('text', 'form_label');
      $form_label->setLabel('Form Label');
      $form_label->setAttrib('placeholder', $this->_element->name);
      $form_label->setDescription("This will be the label for this element in the Item add/edit form. Leave blank to keep the system's default.");

      $default_value = $this->createElement('textarea', 'default_value');
      $default_value->setLabel('Default Value');
      $default_value->setAttribs(array('rows' => 3, 'cols' => 50));
      $default_value->setDescription("This will be the default value for this element in the Item add/edit form.");

      $required = $this->createElement('checkbox', 'required');
      $required->setLabel('Required');
      $required->setDescription("Checking this box will force this element to be required in the Item add/edit form.");

      $html = $this->createElement('checkbox', 'html');
      $html->setLabel('Allow HTML Input');
      $html->setDescription("Check this box to allow users to enter HTML for this element.");

      $multiple = $this->createElement('checkbox', 'multiple');
      $multiple->setLabel('Allow Multiple Values');
      $multiple->setDescription("Check this box to allow users to add more than one value for this element.");

      $public_label = $this->createElement('text', 'public_label');
      $public_label->setLabel('Public Label');
      $public_label->setAttrib('placeholder', $this->_element->name);
      $public_label->setDescription("This will be the label for the public display of this element. Leave blank to keep the system's default.");

      $brief = $this->createElement('hidden', 'brief_display'); //@todo $brief = $this->createElement('checkbox', 'brief_display');

      $brief->setLabel('Brief Display');
      $brief->setDescription("This will be the label for this element in the public display. Leave blank to keep the system's default.");

      $submit = new Zend_Form_Element_Submit('Save');

      // see if any administration settings already stored in the db for this element
      $settings = $this->_getElementAdministrationSettings($this->_element->id);
      $_elements = array(
          'id',
          'form_label',
          'default_value',
          'required',
          'html',
          'multiple',
          'public_label',
          'brief',
          'submit'
      );
      $elements = array();
      foreach ($_elements as $element) {
          if (isset($settings->{$element})) {
            $$element->setValue($settings->{$element});
          }
          // if visiting an element for the first time in this plugin
          // set the default value to be Omeka's default
          else {
            switch ($element) {
              case 'required':
              case 'html':
              case 'multiple':
                $$element->setValue(1);
                break;
            }
          }
          $elements[] = $$element;
      }

      $this->addElements($elements);
  }

  protected function _getElementAdministrationSettings($element_id) {
      return get_db()
          ->getTable('ElementAdministrationSettings')
          ->findByElementId($element_id);
  }
}
