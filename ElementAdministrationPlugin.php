<?php

/**
 *
 * @todo hookup Brief Display
 * @todo integrate with SimpleVocab
 *
 * @author Joe Corall <jcorall@kent.edu>
 */

if (!defined('ELEMENT_ADMINISTRATION_DIR')) {
  define('ELEMENT_ADMINISTRATION_DIR', dirname(__FILE__));
}

class ElementAdministrationPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'before_save_item',
        'define_acl',
        'initialize',
        'install',
        'uninstall'
    );

    protected $_filters = array(
        'admin_navigation_main',
        'display_elements'
    );

    private $_settings = array();

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Create the table.
        $db = $this->_db;
        $sql = "CREATE TABLE IF NOT EXISTS `$db->ElementAdministrationSettings` (
          `id` int(10) unsigned NOT NULL,
          `required` tinyint(1) NOT NULL DEFAULT 0,
          `multiple` tinyint(1) NOT NULL DEFAULT 1,
          `html` tinyint(1) NOT NULL DEFAULT 1,
          `brief_display` tinyint(1) NOT NULL DEFAULT 0,
          `form_label` VARCHAR(255) DEFAULT NULL,
          `public_label` VARCHAR(255) DEFAULT NULL,
          `default_value` MEDIUMTEXT COLLATE utf8_unicode_ci DEFAULT '',
          PRIMARY KEY (`id`),
          KEY `brief_display` (`html`),
          KEY `html` (`html`),
          KEY `multiple` (`multiple`),
          KEY `required` (`required`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
        $db->query($sql);

        $this->_installOptions();
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {
        // Drop the table.
        $db = $this->_db;
        $sql = "DROP TABLE IF EXISTS `$db->ElementAdministrationSettings`";
        $db->query($sql);

        $this->_uninstallOptions();
    }

    public function hookInitialize() {
        $db = get_db();
        $sql = "SELECT e.id, IF(length(item_type.name), item_type.name, set.name) AS set_name, e.name, s.*
            FROM {$db->prefix}elements e
            INNER JOIN {$db->prefix}element_administration_settings s ON s.id = e.id
            INNER JOIN {$db->prefix}element_sets `set` ON set.id = e.element_set_id
            LEFT JOIN {$db->prefix}item_types_elements type_element ON type_element.element_id = e.id
            LEFT JOIN {$db->prefix}item_types item_type ON item_type.id = type_element.item_type_id";
        $result = $db->query($sql);
        // add the "required" input and form filters for all required metadata tags
        while ($setting = $result->fetchObject()) {
            $this->_settings[($setting->id)] = $setting;
            add_filter(array('ElementForm', 'Item', $setting->set_name, $setting->name), array($this, 'addAdminFormSettings'));
            add_filter(array('ElementInput', 'Item', $setting->set_name, $setting->name), array($this, 'addAdminInputSettings'));
        }
    }

    public function filterAdminNavigationMain($nav) {
        $nav[] = array(
            'label' => __('Element Administration'),
            'uri' => url('element-administration'),
            'resource' => 'ElementAdministration_Index',
            'privilege' => 'index'
        );
        return $nav;
    }

    public function filterDisplayElements($elementSets)
    {
        $_elementSets = array();
        foreach ($elementSets as $set => $elements) {
            $_elementSets[$set] = array();
            foreach ($elements as $key => $element) {
                if (isset($this->_settings[($element->id)]->public_label) && strlen($this->_settings[($element->id)]->public_label) > 0) {
                    $key = $this->_settings[($element->id)]->public_label;
                }
                $_elementSets[$set][$key] = $element;
            }
        }

        return $_elementSets;
    }

    public function hookBeforeSaveItem($args)
    {
        $item = $args['record'];
        foreach ($this->_required_elements as $elementSetName => $elements) {
            foreach ($elements as $elementId => $elementName) {
                if (strlen($item->Elements[$id][0]['text']) === 0) {
                    $item->addError($label, "$label is required");
                }
            }
        }
    }

    public function hookDefineAcl($args)
    {
        // Restrict access to super and admin users.
        $args['acl']->addResource('ElementAdministration_Index');
    }

    public function addAdminInputSettings($components, $args)
    {
        $setting = $this->_settings[($args['element']->id)];
        $attributes = array(
            'rows' => 3,
            'cols' => 50,
            'required' => 'required'
        );

        if (empty($args['record']->id) && strlen($setting->default_value)) {
            $args['value'] = $setting->default_value;
        }

        $components['input'] = get_view()->formTextArea($args['input_name_stem'] . '[text]', $args['value'], $attributes);

        if (isset($setting->html) && $setting->html == 0) {
            $components['html_checkbox'] = FALSE;
        }

        return $components;
    }

    public function addAdminFormSettings($components, $args)
    {
        $setting = $this->_settings[($args['element']->id)];

        $label = isset($setting->form_label) && strlen($setting->form_label) ? $setting->form_label : $components['label'];
        $components['label'] = '<label> ' . strip_tags($label);
        if (!empty($setting->required)) {
            $components['label'] .= ' *';
        }
        $components['label'] .= '</label>';


        if (isset($setting->multiple) && $setting->multiple == 0) {
            $components['add_input'] = '';
        }

        return $components;
    }
}
