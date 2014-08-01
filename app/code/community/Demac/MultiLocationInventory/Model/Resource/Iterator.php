<?php

/**
 * Class Demac_MultiLocationInventory_Model_Resource_Iterator
 */
class Demac_MultiLocationInventory_Model_Resource_Iterator extends Mage_Core_Model_Resource_Iterator
{
    /**
     * Accepts the same arguments as Mage_Core_Model_Resource_Iterator->walk().
     * Behavior only differs when a callback returns false which causes this iterator to stop.
     *
     * @param       $query
     * @param array $callbacks
     * @param array $args
     * @param null  $adapter
     *
     * @return $this
     */
    public function walk($query, array $callbacks, array $args = array(), $adapter = null)
    {
        $stmt        = $this->_getStatement($query, $adapter);
        $args['idx'] = 0;
        while ($row = $stmt->fetch()) {
            $args['row'] = $row;
            foreach ($callbacks as $callback) {
                $result = call_user_func($callback, $args);
                if($result === false) {
                    break 2;
                }
                if(!empty($result)) {
                    $args = array_merge($args, $result);
                }
            }
            $args['idx']++;
        }

        return $this;
    }
}