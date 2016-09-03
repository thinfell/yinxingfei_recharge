<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$sql = <<<EOF

DROP TABLE IF EXISTS cdb_a_yinxingfei_recharge_order;
CREATE TABLE cdb_a_yinxingfei_recharge_order (
  `id` char(32) NOT NULL,
  `uid` int(8) DEFAULT NULL,
  `start_time` char(11) DEFAULT NULL,
  `finish_time` char(11) DEFAULT NULL,
  `optional` text,
  `state` tinyint(4) DEFAULT '1',
  PRIMARY KEY (`id`)
) TYPE=MyISAM;

EOF;

runquery($sql);
$finish = TRUE;

?>