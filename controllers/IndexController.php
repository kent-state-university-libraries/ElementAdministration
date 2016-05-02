<?php

/**
 * @package ElementAdministration
 * @author jcorall@kent.edu
 */

class ElementAdministration_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $db = get_db();
        $sql = "SELECT element.id, IF(length(item_type.name), item_type.name, set.name) AS set_name,
          element.name, s.id AS has_settings, IF(LENGTH(s.form_label), s.form_label, s.public_label) AS overridden_label
            FROM {$db->prefix}elements element
            INNER JOIN {$db->prefix}element_sets `set` ON set.id = element.element_set_id
            LEFT JOIN {$db->prefix}item_types_elements type_element ON type_element.element_id = element.id
            LEFT JOIN {$db->prefix}item_types item_type ON item_type.id = type_element.item_type_id
            LEFT JOIN `$db->ElementAdministrationSettings` s ON s.id = element.id AND s.collection_id = 0
            ORDER BY element.element_set_id, set_name, ISNULL(element.order), element.order";

        $this->view->elements = $db->query($sql);
        $this->view->current_element_set = NULL;
        $this->view->head = array(
            'title' => __('Element Administration')
        );
    }

    public function editAction() {
        $id = $this->_getParam('id');
        $db = get_db();
        $this->view->element = $this->_getElement($id);

        $flashMessenger = $this->_helper->FlashMessenger;
        include_once ELEMENT_ADMINISTRATION_DIR . '/forms/edit-form.php';
        try {
        $sql = "SELECT c.id, t.text from collections c
          INNER JOIN element_texts t ON t.record_id = c.id AND t.record_type = ?
          INNER JOIN elements e ON e.name = ? AND e.id = t.element_id
          INNER JOIN element_sets s ON s.name = 'Dublin Core' AND s.id = e.element_set_id
          WHERE c.id NOT IN (5)";
        $collections = $db->query($sql, array('Collection', 'Title'))->fetchAll();
        $this->view->collections = array(0 => 'All Collections');
        foreach ($collections as $collection) {
            $collection_id = $collection['id'];
            $this->view->collections[$collection_id] =  $collection['text'];
        }
            $this->view->form = new ElementAdministration_EditForm();
            $this->view->form->setElement($this->view->element);
            $this->view->form->create();
    }

        catch(Exception $e) {
            $flashMessenger->addMessage('Error rendering edit form ' . $e, 'error');
        }
    }

    public function saveAction()
    {
        $params = $this->_getAllParams();
        unset($params['admin'], $params['module'], $params['controller'], $params['action'], $params['Save']);

        $collections = array();
        $id = NULL;
        foreach ($params as $name => $value) {
            if ($name == 'id') {
                $id = $value;
                continue;
            }
            $parts = explode('_', $name);
            $collection_id = $parts[1];
            echo $collection_id,'<br>';
            if (!isset($collections[$collection_id])) {
                $collections[$collection_id] = new ElementAdministrationSettings();
                $collections[$collection_id]->collection_id = $collection_id;
            }
            unset($parts[0], $parts[1]);
            $key = implode('_', $parts);
            if (strlen($key) == 0) {
                // @todo log this
                continue;
            }
            if (in_array($key, array('description', 'order'))) {
                $$key = $value;
            }
            else {
              $collections[$collection_id]->{$key} = $value;
            }
        }

        foreach (array('description', 'order') as $var) {
            if (isset($$var)) {
                get_db()->query('UPDATE elements SET ' . $var . ' = ? WHERE id = ?', array($$var, $id));
            }
        }

        foreach ($collections as $setting) {
            $setting->id = $id;
            $setting->save();
        }

        $this->_helper->flashMessenger(__('Successfully saved configuration'),
            'success');

        $this->_helper->redirector('index', 'index');
    }

    protected function _getElement($id) {
        return get_db()
            ->getTable('Element')
            ->find($id);
    }
}
