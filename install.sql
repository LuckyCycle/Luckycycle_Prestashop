DROP TABLE IF EXISTS `PREFIX_luckycycle_cfg`;

CREATE TABLE IF NOT EXISTS `PREFIX_luckycycle_cfg` (
  `the_key` char(60) COLLATE utf8_bin NOT NULL DEFAULT '',
  `the_val` char(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`the_key`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8;

INSERT INTO `PREFIX_luckycycle_cfg` VALUES ('api_key', '');
INSERT INTO `PREFIX_luckycycle_cfg` VALUES ('operation_id', '');
INSERT INTO `PREFIX_luckycycle_cfg` VALUES ('active', '0');