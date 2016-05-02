<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

class Table_ElementAdministrationSettings extends Omeka_Db_Table
{
    public function findByElementId($id, $collection_id = 0)
    {
        $select = $this->getSelect()->where('id = ?', $id)->where('collection_id = ?', $collection_id);
        return $this->fetchObject($select);
    }
}
