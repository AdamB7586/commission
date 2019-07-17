<?php
namespace Commission;

use ShoppingCart\Modifiers\Cost;
use DBAL\Modifiers\Modifier;

class Product extends \LessonPrice\Product {
    
    /**
     * Gets the amount of commission for the give product ID
     * @param int $product_id This should be the unique product ID
     * @return int|decimal WIll return the commission amount
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
     * @param int|NULL $percent This should be the percent of commission if not a fixed amount or NULL
     * @param int $active If the commission is active set to 1 else set to 0
     * @return boolean If added successfully set to 
     */
    public function setCommissionInfo($product_id, $amount = NULL, $percent = NULL, $active = 1) {
        if(!$this->getCommissionInfo($product_id) && (is_null(Modifier::setNullOnEmpty(Cost::priceUnits($amount, $this->decimals))) || is_null(Modifier::setNullOnEmpty($percent)))) {
            return $this->db->insert($this->config->table_product_commissions, ['product_id' => $product_id, 'amount' => Modifier::setNullOnEmpty(Cost::priceUnits($amount, $this->decimals)), 'percent' => Modifier::setNullOnEmpty($percent), 'active' => Modifier::setZeroOnEmpty($active)]);
        }
        return false;
    }
    
    /**
     * Updates the commission information in the database
     * @param int $product_id This should be the product if of the product you are updating the commission for
     * @param array $commissionInfo This should be the commission information as an array
     * @return boolean If successfully updated will return true else returns false
     */
    public function updateCommisssionInfo($product_id, $commissionInfo = []) {
        $commissionInfo['amount'] = Modifier::setNullOnEmpty($commissionInfo['amount']);
        $commissionInfo['percent'] = Modifier::setNullOnEmpty($commissionInfo['percent']);
        $commissionInfo['active'] = Modifier::setZeroOnEmpty($commissionInfo['active']);
        return $this->db->update($this->config->table_product_commissions, $commissionInfo, ['product_id' => $product_id]);
    }
    
    /**
     * Add a product to the database
     * @param string $name This should be the name of the product that you are adding
     * @param string $code Give the product a unique code of SKU to identify it
     * @param string $description Add a description to the product what is shown in the store
     * @param int|float $price This should be the RRP or price that you are charging for this product
     * @param int|array $category The category ID where this product is located
     * @param int $tax_id The Tax ID of the Tax band that is item has
     * @param int $active If the product should be set as active set to 1 else set to 0
     * @param array|false $image This should be the image to be associated with the product
     * @param array $additionalInfo Any additional information should be included as array items
     * @return boolean If the product is added successfully will return true else will return false
     */
    public function addProduct($name, $code, $description, $price, $category, $tax_id, $active = 1, $image = false, $additionalInfo = []) {
        if(isset($additionalInfo['commission'])){
            $commission = $additionalInfo['commission'];
            unset($additionalInfo['commission']);
        }
        $added = parent::addProduct($name, $code, $description, $price, $category, $tax_id, $active, $image, $additionalInfo);
        if(isset($commission)){
            $this->setCommissionInfo($this->db->lastInsertID(), Modifier::setNullOnEmpty($commission['amount']), Modifier::setNullOnEmpty($commission['percent']));
        }
        return $added;
    }
    
    /**
     * Edit a product in the database
     * @param type $product_id This should be the unique product ID you are updating
     * @param array|false $image This should be the image to be associated with the product
     * @param array $additionalInfo Any additional information you are updating should be set as an array here
     * @return boolean If the information has successfully been updated will return true else returns false
     */
    public function editProduct($product_id, $image = false, $additionalInfo = []) {
        if(isset($additionalInfo['commission'])){
            $commission = $additionalInfo['commission'];
            unset($additionalInfo['commission']);
        }
        $updated = parent::editProduct($product_id, $image, $additionalInfo);
        if(isset($commission)){
            $this->updateCommisssionInfo($this->db->lastInsertID(), $commission);
        }
        return $updated;
    }
}
