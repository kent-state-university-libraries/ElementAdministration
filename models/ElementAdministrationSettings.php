<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

class ElementAdministrationSettings extends Omeka_Record_AbstractRecord
{
    public $id;
    public $collection_id;
    public $required;
    public $multiple;
    public $html;
    public $brief_display;
    public $form_label;
    public $hidden_form;
    public $hidden_public;
    public $public_label;
    public $default_value;
}
