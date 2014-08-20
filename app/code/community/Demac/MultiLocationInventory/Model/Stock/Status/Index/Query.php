<?php
class Demac_MultiLocationInventory_Model_Stock_Status_Index_Query
{
    private $fields = array();
    private $from = '';
    private $fromAs = '';
    private $joins = array();
    private $where = '';
    private $group = '';

    /**
     * Add field to select.
     *
     * @param $fieldAs
     * @param $field
     */
    public function addField($fieldAs, $field) {
        array_push(
            $this->fields,
            array(
                $fieldAs => $field
            )
        );
    }

    /**
     * Set from table for select.
     *
     * @param      $from
     * @param bool $fromAs
     */
    public function setFrom($from, $fromAs = false) {
        if($fromAs === false) {
            $fromAs = $from;
        }
        $this->from = $from;
        $this->fromAs = $fromAs;
    }

    /**
     * Add join to select.
     *
     * @param $type
     * @param $tableName
     * @param $tableAs
     * @param $on
     */
    public function addJoin($type, $tableName, $tableAs, $on) {
        array_push(
            $this->joins,
            array(
                'type' => $type,
                'tableName' => $tableName,
                'tableAs' => $tableAs,
                'on' => $on
            )
        );
    }

    /**
     * Remove join from select.
     *
     * @param $tableAs
     */
    public function removeJoin($tableAs) {
        foreach($this->joins as $key => $joinArr) {
            if($joinArr['tableAs'] == $tableAs) {
                unset($this->joins[$key]);
                //re-key after unset.
                $this->joins = array_values($this->joins);
                break;
            }
        }
    }

    /**
     * Set where condition for select.
     *
     * @param $where
     */
    public function setWhere($where) {
        $this->where = $where;
    }

    /**
     * Set group filter for select.
     *
     * @param $group
     */
    public function setGroup($group) {
        $this->group = $group;
    }

    /**
     * Convert query object to query string.
     *
     * @return string
     */
    public function __toString() {
        return $this->buildSelect() . ' ' . $this->buildFrom() . ' ' . $this->buildJoins() . ' ' . $this->buildWhere() . ' ' . $this->buildGroup();
    }

    /**
     * Build SELECT portion of the query.
     *
     * @return string
     */
    private function buildSelect() {
        $query = 'SELECT ';
        foreach($this->fields as $fieldAs => $fieldSelect) {
            $query .= $fieldSelect . ' as ' . $fieldAs . ',';
        }
        return rtrim($query, ',');
    }

    /**
     * Build FROM portion of the query.
     *
     * @return string
     */
    private function buildFrom() {
        return 'FROM ' . $this->from . ' as ' . $this->fromAs;
    }

    /**
     * Build JOIN portion of the query.
     *
     * @return string
     */
    private function buildJoins() {
        $query = '';
        foreach($this->joins as $joinArr) {
            $query .= $joinArr['type'] . ' ' . $joinArr['tableName'] . ' as ' . $joinArr['tableAs'];
            if(isset($joinArr['on'])) {
                $query .= ' ON ' . $joinArr['on'];
            }
            $query .= ' ';
        }
        return trim($query);
    }

    /**
     * Build WHERE portion of the query.
     *
     * @return string
     */
    private function buildWhere() {
        return 'WHERE ' . $this->where;
    }

    /**
     * Build GROUP portion of the query.
     *
     * @return string
     */
    private function buildGroup() {
        return 'GROUP BY ' . $this->group;
    }
}