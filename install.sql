DROP TABLE IF EXISTS `PREFIX_luckycycle_pokes`;

CREATE TABLE `PREFIX_luckycycle_pokes` (
  `id_poke` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(255) NOT NULL,
  `html_data` text NOT NULL,
  `type` varchar(32) NOT NULL,
  `id_customer` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `created_at` datetime,
  `operation_id` varchar(32) NOT NULL,
  `total_played` decimal(17,2) NOT NULL DEFAULT '0.00',
  KEY `hash` (`hash`),
  KEY `id_order` (`id_order`),
  KEY `id_customer` (`id_customer`),
  KEY `created_at` (`created_at`),

  PRIMARY KEY (`id_poke`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5000 ;
