<?php

/**
 *
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
        'display_elements',
    );

    private $_settings = array();

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        // Create the table.
        $db = $this->_db;
        $sql = "
        CREATE TABLE `$db->ElementAdministrationSettings` (
            `id` int(10) unsigned NOT NULL,
            `collection_id` int(10) unsigned DEFAULT '0',
            `required` tinyint(1) NOT NULL DEFAULT '0',
            `multiple` tinyint(1) NOT NULL DEFAULT '1',
            `html` tinyint(1) NOT NULL DEFAULT '1',
            `brief_display` tinyint(1) NOT NULL DEFAULT '0',
            `hidden_form` tinyint(1) NOT NULL DEFAULT '0',
            `hidden_public` tinyint(1) NOT NULL DEFAULT '0',
            `form_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `public_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `default_value` mediumtext COLLATE utf8_unicode_ci,
            UNIQUE KEY `id` (`id`,`collection_id`),
            KEY `brief_display` (`html`),
            KEY `html` (`html`),
            KEY `multiple` (`multiple`),
            KEY `required` (`required`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
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
        // get all the settings in the system
        $sql = "SELECT e.id, IF(length(item_type.name), 'Item Type Metadata', set.name) AS set_name, e.name, e.description, e.order, s.*
            FROM `$db->Elements` e
            INNER JOIN {$db->prefix}element_administration_settings s ON s.id = e.id
            INNER JOIN `$db->ElementSets` `set` ON set.id = e.element_set_id
            LEFT JOIN {$db->prefix}item_types_elements type_element ON type_element.element_id = e.id
            LEFT JOIN {$db->prefix}item_types item_type ON item_type.id = type_element.item_type_id";
        $result = $db->query($sql);

        // add a per-element filter for every element that has a setting defined by this plugin
        while ($setting = $result->fetchObject()) {
            $this->_settings[($setting->id)][($setting->collection_id)] = $setting;
            add_filter(array('ElementForm', 'Item', $setting->set_name, $setting->name), array($this, 'addAdminFormSettings'));
            add_filter(array('ElementInput', 'Item', $setting->set_name, $setting->name), array($this, 'addAdminInputSettings'));
        }

        // if this is the admin edit form
        // add some javascript so client-side events (e.g. selecting a collection)
        // trigger the correct settings from the plugin
        if (strpos($_SERVER['REQUEST_URI'], '/admin/items/') !== FALSE) {
            queue_js_string('if (!Omeka) {var Omeka = {};}Omeka.element_administration = ' . json_encode($this->_settings) . ';');
            queue_js_url(url('plugins/ElementAdministration/views/admin/javascripts/items.js'));
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
        $item = get_current_record('item', false);
        if (!$item && strpos(current_url(), 'admin/items/add') !== FALSE) {
            return;
        }

        if (empty($item->collection_id)) {
            if (!is_object($item)) {
                $item = new stdClass();
            }
            $item->collection_id = 0;
        }
        foreach ($elementSets as $set => $elements) {
            $_elementSets[$set] = array();
            foreach ($elements as $key => $element) {
                if (isset($this->_settings[($element->id)][$item->collection_id]->public_label)
                    && strlen($this->_settings[($element->id)][$item->collection_id]->public_label) > 0) {
                    $key = $this->_settings[($element->id)][$item->collection_id]->public_label;
                }
                if (empty($this->_settings[($element->id)][$item->collection_id]->hidden_public)) {
                    $element->hide = empty($this->_settings[($element->id)][$item->collection_id]->brief_display);
                    $_elementSets[$set][$key] = $element;
                }
            }
        }

        return $_elementSets;
    }

    public function hookDefineAcl($args)
    {
        // Restrict access to super and admin users.
        $args['acl']->addResource('ElementAdministration_Index');
    }

    public function addAdminInputSettings($components, $args)
    {
        // get the collection ID for this item
        $collection_id = isset($args['record']->collection_id) ? $args['record']->collection_id : 0;
        // if no collection ID set, see if its set in the URL
        $collection_id = isset($_GET['collection_id']) ? $_GET['collection_id'] : $collection_id;

        // get this elements setting
        $setting = isset($this->_settings[($args['element']->id)][$collection_id]) ? $this->_settings[($args['element']->id)][$collection_id] : new stdClass();

        // if this element is hidden, no need to do anyt processing
        // it'll get hidden in addAdminFormSettings()
        if (!empty($setting->hidden_form)) {
          return $components;
        }

        $attributes = array(
            'rows' => 3,
            'cols' => 50,
        );

        // set required if needed
        if (!empty($setting->required)) {
            $attributes['required'] = 'required';
        }
        // set default value if there is one
        if (empty($args['record']->id) && strlen($setting->default_value)) {
            $args['value'] = $setting->default_value;
        }

        $components['input'] = get_view()->formTextArea($args['input_name_stem'] . '[text]', $args['value'], $attributes);

        // remove the html checkbox if that option is set
        if (isset($setting->html) && $setting->html == 0) {
            $components['html_checkbox'] = FALSE;
        }

        return $components;
    }

    public function addAdminFormSettings($components, $args)
    {
        // get the collection ID for this item
        $collection_id = isset($args['record']->collection_id) ? $args['record']->collection_id : 0;
        // if no collection ID set, see if its set in the URL
        $collection_id = isset($_GET['collection_id']) ? $_GET['collection_id'] : $collection_id;

        // get this elements setting
        $setting = isset($this->_settings[($args['element']->id)][$collection_id]) ? $this->_settings[($args['element']->id)][$collection_id] : new stdClass();

        // if this element is set to be hidden on the admin from, hide it
        if (!empty($setting->hidden_form)) {
            foreach ($components as $key => $input) {
                $components[$key] = '';
            }
            $components['input'] = "<style>#element-{$setting->id} {display:none}</style>";
        }
        else {
            // override the label if needed
            $label = isset($setting->form_label) && strlen($setting->form_label) ? $setting->form_label : $components['label'];
            $components['label'] = '<label> ' . strip_tags($label);
            if (!empty($setting->required)) {
                $components['label'] .= ' <span class="required"></span>';
            }
            $components['label'] .= '</label>';


            // remove the "Add Input" button if that option is set
            if (isset($setting->multiple) && $setting->multiple == 0) {
                $components['add_input'] = '';
            }
        }

        return $components;
    }
}
