<?php
namespace Commission;

use ShoppingCart\Modifiers\Cost;

class Order extends \LessonPrice\Order {
    
    /**
     * Gets the commission information for the order
     * @param int $order_id This should be the unique order id
     * @return array|boolean If any information exists will return an array else if nothing is return returns false
     */
    public function getCommissionInfo($order_id) {
        return $this->db->select($this->config->table_commissions, ['order_id' => $order_id]);
    }

    /**
     * Add commission for an order to the database
     * @param int $order_id This should be the order_id
     * @param int|decimal $amount This should be the amount of commission
     * @return boolean If successfully added will return true else returns false
     */
    public function addCommissionToOrder($order_id, $amount) {
        return $this->db->insert($this->config->table_commissions, ['order_id' => $order_id, 'amount' => $amount]);
    }
    
    /**
     * Updates the commission information for a given order in the database
     * @param int $order_id This should be the order id
     * @param array $information This should be an array of the information you are updating with ['field' => 'value']
     * @return boolean If successfully updated will return true else returns false
     */
    public function updateCommissionInfo($order_id, $information = []) {
        if(!empty($information)){
            return $this->db->update($this->config->table_commissions, $information, ['order_id' => $order_id]);
        }
        return false;
    }
    
    /**
     * Deletes commission information from the database
     * @param int $order_id This should be the order id
     * @return boolean If successfully deleted will return true else returns false
     */
    public function deleteCommisionFromOrder($order_id) {
        return $this->db->delete($this->config->table_commissions, ['order_id' => $order_id]);
    }
    
    /**
     * Adds the order information into the database
     * @param array $additional Additional fields to insert
     * @return boolean If the order is successfully inserted will return true else returns false
     */
    protected function createOrder($additional = []) {
        $added = parent::createOrder($additional);
        if(floatval($this->totals['commission']) != 0){
            $this->addCommissionToOrder($this->getBasket()['order_id'], $this->totals['commission']);
        }
        return $added;
    }
    
    /**
     * Updates the basket in the database
     * @param array $additional Additional where fields
     * @return boolean If the information is updated will return true else will return false
     */
    protected function updateBasket($additional = []) {
        $updated = parent::updateBasket($additional);
        $order_id = $this->getBasket()['order_id'];
        $commission = $this->getCommissionInfo($order_id);
        if($commission && floatval($this->totals['commission']) != 0){
            $this->updateCommissionForOrder($order_id, ['commission' => $this->totals['commission']]);
        }
        elseif($commission && floatval($this->totals['commission']) == 0){
            $this->deleteCommisionFromOrder($order_id);
        }
        elseif(floatval($this->totals['commission']) != 0){
            $this->addCommissionToOrder($order_id, $this->totals['commission']);
        }
        return $updated;
    }

    /**
     * Deletes the order from the database
     * @return boolean If the basket has successfully been deleted will return true else returns false
     */
    public function emptyBasket() {
        $this->deleteCommisionFromOrder($this->getBasket()['order_id']);
        return parent::emptyBasket();
    }
    
    /**
     * Update the totals for all items in the basket including delivery and tax
     */
    protected function updateTotals() {
        parent::updateTotals();
        $commission = 0;
        if(!empty($this->products) && is_array($this->products)){
            foreach($this->products as $product){
                $commission = $commission + ($this->product->getCommissionAmount($product['product_id']) * $product['quantity']);
            }
        }
        $this->totals['commission'] = Cost::priceUnits($commission, $this->decimals);
    }
}

