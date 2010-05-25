/************************************************************************
 * This file is part of phpIPN.                                         *
 *                                                                      *
 * phpIPN is free software: you can redistribute it and/or modify       *
 * it under the terms of the GNU General Public License as published by *
 * the Free Software Foundation, either version 3 of the License, or    *
 * (at your option) any later version.                                  *
 *                                                                      *
 * phpIPN is distributed in the hope that it will be useful,            *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of       *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        *
 * GNU General Public License for more details.                         *
 *                                                                      *
 * You should have received a copy of the GNU General Public License    *
 * along with phpIPN.  If not, see <http://www.gnu.org/licenses/>.      *
 ************************************************************************/

DROP TABLE IF EXISTS `ipndata`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `ipndata` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `add_ts` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'Timestamp when row was added to DB',
  `pa_ticket_id` text COMMENT 'Ticket ID',
  `payment_date` text COMMENT 'Date/time/timezone of Paypal transaction',
  `mc_currency` text COMMENT 'Payment currency',
  `mc_gross` float default NULL COMMENT 'Total amount taken',
  `payment_status` text COMMENT 'Transaction Status',
  `payment_type` text COMMENT 'Payment type',
  `first_name` text COMMENT 'Customer first name',
  `last_name` text COMMENT 'Customer last name',
  `txn_type` text COMMENT 'Transaction Type',
  `txn_id` text NOT NULL COMMENT 'Paypal Transaction ID',
  `parent_txn_id` text,
  `address_name` text COMMENT 'Address name',
  `address_street` text COMMENT 'Address street',
  `address_city` text COMMENT 'Address city',
  `address_state` text COMMENT 'Address state',
  `address_country` text COMMENT 'Address country',
  `address_country_code` text COMMENT 'Address country code',
  `address_zip` text COMMENT 'Address zip/postcode',
  `address_status` text COMMENT 'Address status',
  `payer_email` text COMMENT 'Payer (from) e-mail address, address of customer',
  `payer_status` text COMMENT 'Payer status',
  `payer_id` text COMMENT 'Payer ID',
  `receiver_email` text COMMENT 'Recipient (to) e-mail address',
  `receiver_id` text COMMENT 'Receiver ID',
  `business` text COMMENT 'Business ID',
  `mc_fee` decimal(10,0) default NULL COMMENT 'Paypal fee',
  `num_cart_items` int(11) default NULL COMMENT 'Number of items in cart',
  `quantity1` text COMMENT 'Quantity',
  `item_name1` text COMMENT 'Name of item 1',
  `item_number1` text,
  `quantity2` text,
  `item_name2` text,
  `item_number2` text,
  `quantity3` text,
  `item_name3` text,
  `item_number3` text,
  `quantity4` text,
  `item_name4` text,
  `item_number4` text,
  `quantity5` text,
  `item_name5` text,
  `item_number5` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4731 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;

