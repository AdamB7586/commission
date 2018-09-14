<?php
namespace Commission;

use ShoppingCart\Modifiers\Cost;
use ShoppingCart\Modifiers\Validator;

class Product extends \LessonPrice\Product {
    
    /**
     * Gets the amount of commision for the give product ID
     * @param int $product_id This should be the unique product ID
     * @return int|decimal WIll return the commision amount
     */
    public function getCommissionAmount($product_id) {
        $amounts = $this->getCommissionInfo($product_id);
        if(is_array($amounts)){
            if(!empty($amounts['amount'])){return Cost::priceUnits($amounts['amount'], $this->decimals);}
            elseif(!empty($amounts['percent'])){
                return Cost::priceUnits((($this->getProductPrice($product_id) / 100) * $amounts['percent']), $this->decimals);
            }
        }
        return Cost::priceUnits(0, $this->decimals);
    }
    
    /**
     * Returns the commission information for a given product ID
     * @param int $product_id This should be the product ID
     * @param boolean $active If you only want to get information for active commissions set to true else set to false for any
     * @return array|false Returns an array in the information exists for the given query else return false
     */
    public function getCommissionInfo($product_id, $active = true) {
        if(is_numeric($product_id)) {
            $where = [];
            $where['product_id'] = $product_id;
            if($active === true){
                $where['active'] = 1;
            }
            return $this->db->select($this->config->table_product_commissions, $where);
        }
        return false;
    }
    
    /**
     * Sets a new commission amount information for a given product
     * @param int $product_id This should be the unique product ID
     * @param int|decimal|NULL $amount This should be a fixed amount if not a percent or NULL
     * @param int|NULL $percent This should be the percent of commision if not a fixed amount or NULL
     * @param int $active If the commission is active set to 1 else set to 0
     * @return boolean If added successfully set to 
     */
    public function setCommissionInfo($product_id, $amount = NULL, $percent = NULL, $active = 1) {
        if(!$this->getCommissionInfo($product_id) && (is_null(Validator::setNullOnEmpty(Cost::priceUnits($amount, $this->decimals))) || is_null(Validator::setNullOnEmpty($percent)))) {
            return $this->db->insert($this->config->table_product_commissions, ['product_id' => $product_id, 'amount' => Validator::setNullOnEmpty(Cost::priceUnits($amount, $this->decimals)), 'percent' => Validator::setNullOnEmpty($percent), 'active' => Validator::setZeroOnEmpty($active)]);
        }
        return false;
    }
    
    /**
     * Updates the commision information in the database
     * @param int $product_id This should be the product if of the product you are updating the commision for
     * @param array $commissionInfo This should be the commission information as an array
     * @return boolean If successfully updated will return true else returns false
     */
    public function updateCommisssionInfo($product_id, $commissionInfo = []) {
        $commissionInfo['amount'] = Validator::setNullOnEmpty($commissionInfo['amount']);
        $commissionInfo['percent'] = Validator::setNullOnEmpty($commissionInfo['percent']);
        $commissionInfo['active'] = Validator::setZeroOnEmpty($commissionInfo['active']);
        return $this->db->update($this->config->table_product_commissions, $commissionInfo, ['product_id' => $product_id]);
    }
}
