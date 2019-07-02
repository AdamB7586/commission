<?php
namespace Commission\Tests;

use PHPUnit\Framework\TestCase;
use DBAL\Database;
use Configuration\Config;
use Commission\Commission;

class ProductTest extends TestCase{
    protected $db;
    protected $commission;
    
    protected function setUp(): void {
        $this->db = new Database($GLOBALS['hostname'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['database']);
        if(!$this->db->isConnected()){
            $this->markTestSkipped(
                'No local database connection is available'
            );
        }
        if(!$this->db->selectAll('store_config')){
            $this->db->query(file_get_contents(dirname(__DIR__).'/vendor/adamb/shopping-cart/database/database_mysql.sql'));
            $this->db->query(file_get_contents(dirname(__DIR__).'/vendor/adamb/lesson-price/database/database_mysql.sql'));
            $this->db->query(file_get_contents(dirname(__DIR__).'/database/database_mysql.sql'));
            $this->db->query(file_get_contents(dirname(__DIR__).'/vendor/adamb/shopping-cart/tests/sample_data/data.sql'));
        }
        $this->commission = new Commission($this->db, new Config($this->db, 'store_config'));
    }
    
    protected function tearDown(): void {
        $this->db = null;
        $this->commission = null;
    }
    
    public function testExample() {
        $this->markTestIncomplete();
    }
}