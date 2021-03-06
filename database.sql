DROP TABLE IF EXISTS `intervals`;
CREATE TABLE `intervals` (
  `id` bigint(20) NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `price` float(10,2) NOT NULL,
  `mon` tinyint(1) DEFAULT NULL,
  `tue` tinyint(1) DEFAULT NULL,
  `wed` tinyint(1) DEFAULT NULL,
  `thu` tinyint(1) DEFAULT NULL,
  `fri` tinyint(1) DEFAULT NULL,
  `sat` tinyint(1) DEFAULT NULL,
  `sun` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `intervals`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `intervals`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;