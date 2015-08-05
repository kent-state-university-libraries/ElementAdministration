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
        $sql = "SELECT element.id, IF(length(item_type.name), item_type.name, set.name) AS set_name, element.name
            FROM {$db->prefix}elements element
            INNER JOIN {$db->prefix}element_sets `set` ON set.id = element.element_set_id
            LEFT JOIN {$db->prefix}item_types_elements type_element ON type_element.element_id = element.id
            LEFT JOIN {$db->prefix}item_types item_type ON item_type.id = type_element.item_type_id
            ORDER BY element.element_set_id, set_name, ISNULL(element.order), element.order";

        $this->view->elements = $db->query($sql);
        $this->view->current_element_set = NULL;
        $this->view->head = array(
            'title' => __('Element Administration')
        );
    }

    public function editAction() {
        $id = $this->_getParam('id');
        $this->view->element = $this->_getElement($id);

        $flashMessenger = $this->_helper->FlashMessenger;
        include_once ELEMENT_ADMINISTRATION_DIR . '/forms/edit-form.php';
        try {
            $form = new ElementAdministration_EditForm();
            $form->setElement($this->view->element);
            $form->create();
        }
        catch(Exception $e) {
            $flashMessenger->addMessage('Error rendering edit form ' . $e, 'error');
        }

        $this->view->form = $form;
    }

    public function saveAction()
    {
        $params = $this->_getAllParams();
        unset($params['admin']);
        unset($params['module']);
        unset($params['controller']);
        unset($params['action']);
        unset($params['Save']);

        $setting = new ElementAdministrationSettings();
        foreach ($params as $key => $value) {
            $setting->{$key} = $value;
        }
        $setting->save();

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
