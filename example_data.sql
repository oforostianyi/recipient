-- ----------------------------
-- Table structure for mcc_mnc
-- ----------------------------
DROP TABLE IF EXISTS `mcc_mnc`;
CREATE TABLE `mcc_mnc` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`cc` varchar(4) COLLATE utf8_unicode_ci DEFAULT '',
`ndc` varchar(3) COLLATE utf8_unicode_ci DEFAULT '',
`subc` varchar(6) COLLATE utf8_unicode_ci DEFAULT '',
`length` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
`mcc` varchar(3) COLLATE utf8_unicode_ci DEFAULT '',
`mnc` varchar(3) COLLATE utf8_unicode_ci DEFAULT '00',
`cc2` varchar(2) COLLATE utf8_unicode_ci DEFAULT NULL,
`country` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
`operator` varchar(50) COLLATE utf8_unicode_ci DEFAULT '',
PRIMARY KEY (`id`) USING BTREE,
UNIQUE KEY `cc` (`cc`,`ndc`,`subc`) USING BTREE,
KEY `mcc` (`mcc`) USING BTREE
) AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


INSERT INTO `mcc_mnc` (`cc`, `ndc`, `subc`, `length`, `mcc`, `mnc`, `cc2`, `country`, `operator`) VALUES
('380', '50', '', '9', '255', '01', 'UA', 'Ukraine', 'vodafone'),
('380', '63', '', '9', '255', '06', 'UA', 'Ukraine', 'lifecell'),
('380', '63', '601', '9', '255', '06', 'UA', 'Ukraine', 'lifecell'),
('380', '73', '', '9', '255', '06', 'UA', 'Ukraine', 'lifecell'),
('380', '93', '', '9', '255', '06', 'UA', 'Ukraine', 'lifecell'),
('380', '66', '', '9', '255', '01', 'UA', 'Ukraine', 'vodafone'),
('380', '67', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar'),
('380', '68', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar'),
('380', '71', '', '9', '255', '00', 'UA', 'Ukraine', 'default'),
('380', '39', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar mnc 02'),
('380', '72', '', '9', '255', '00', 'UA', 'Ukraine', 'default'),
('380', '91', '', '9', '255', '07', 'UA', 'Ukraine', 'trimob'),
('380', '92', '', '9', '255', '21', 'UA', 'Ukraine', 'peoplenet'),
('380', '94', '', '9', '255', '04', 'UA', 'Ukraine', 'intertelecom'),
('380', '95', '', '9', '255', '01', 'UA', 'Ukraine', 'vodafone'),
('380', '99', '', '9', '255', '01', 'UA', 'Ukraine', 'vodafone'),
('380', '96', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar'),
('380', '97', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar'),
('380', '98', '', '9', '255', '03', 'UA', 'Ukraine', 'kyivstar');