CREATE TABLE IF NOT EXISTS `store_commissions` (
  `order_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `paid_date` datetime DEFAULT NULL,
  PRIMARY KEY (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `store_product_commision` (
  `product_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) UNSIGNED DEFAULT NULL,
  `percent` smallint(3) UNSIGNED DEFAULT NULL,
  `active` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  UNIQUE KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `store_config` (`setting`, `value`) VALUES
('table_commissions', 'store_commissions'),
('table_product_commissions', 'store_product_commission');


ALTER TABLE `store_commissions`
  ADD CONSTRAINT `store_commissions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `store_product_commision`
  ADD CONSTRAINT `store_product_commision_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `store_products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;