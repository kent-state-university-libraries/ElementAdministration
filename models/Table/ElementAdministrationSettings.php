<?php

/**
 * @package ElementAdministration
 * @author Joe Corall <jcorall@kent.edu>
 */

class Table_ElementAdministrationSettings extends Omeka_Db_Table
{
    public function findByElementId($id)
    {
        $select = $this->getSelect()->where('id = ?', $id);
        return $this->fetchObject($select);
    }
}
