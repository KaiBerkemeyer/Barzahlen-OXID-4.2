<?php
/**
 * Barzahlen Payment Module (OXID eShop)
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/
 *
 * @copyright   Copyright (c) 2012 Zerebro Internet GmbH (http://www.barzahlen.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-3.0  GNU General Public License, version 3 (GPL-3.0)
 */

require_once dirname(__FILE__) . '/../api/loader.php';

class barzahlen_payment_gateway extends barzahlen_payment_gateway_parent {

  protected $_sLastError = "barzahlen";
  const LOGFILE = "barzahlen.log";

  /**
   * Executes payment, returns true on success.
   *
   * @param double $dAmount Goods amount
   * @param object &$oOrder User ordering object
   *
   * @return bool
   */
  public function executePayment($dAmount, &$oOrder) {

    if ($oOrder->oxorder__oxpaymenttype->value != 'oxidbarzahlen') {
      return parent::executePayment($dAmount, $oOrder);
    }

    $api = $this->_getBarzahlenApi($oOrder);

    $customerEmail = $oOrder->oxorder__oxbillemail->rawValue;
    $orderId = $oOrder->oxorder__oxordernr->value;
    $amount = $oOrder->oxorder__oxtotalordersum->value;
    $currency = $oOrder->oxorder__oxcurrency->rawValue;
    $payment = new Barzahlen_Request_Payment($customerEmail, $orderId, $amount, $currency);

    try {
      $api->handleRequest($payment);
    }
    catch (Exception $e) {
      oxUtils::getInstance()->writeToLog(date('c') . " Transaction/Create failed: " . $e . "\r\r", self::LOGFILE);
    }

    if($payment->isValid()) {
      oxSession::setVar('barzahlenPaymentSlipLink', (string)$payment->getPaymentSlipLink());
      oxSession::setVar('barzahlenExpirationNotice', (string)$payment->getExpirationNotice());
      oxSession::setVar('barzahlenInfotextOne', (string)$payment->getInfotext1());
      oxSession::setVar('barzahlenInfotextTwo', (string)$payment->getInfotext2());
      $oOrder->oxorder__bztransaction = new oxField((int)$payment->getTransactionId());
      $oOrder->oxorder__bzstate = new oxField('pending');
      $oOrder->save();
      return true;
    }
    else {
      return false;
    }
  }

  /**
   * Prepares a Barzahlen API object for the payment request.
   *
   * @param object $oOrder User ordering object
   * @return Barzahlen_Api
   */
  protected function _getBarzahlenApi($oOrder) {

    $oxConfig = oxConfig::getInstance();
    $bzConfig = $oxConfig->getShopConfVar('barzahlen_config');

    $api = new Barzahlen_Api($bzConfig['shop_id'], $bzConfig['payment_key'], $bzConfig['sandbox']);
    $api->setDebug($bzConfig['debug'], self::LOGFILE);
    $api->setLanguage($this->_getOrderLanguage($oOrder));
    return $api;
  }

  /**
   * Gets the order language code.
   *
   * @param object $oOrder User ordering object
   * @return string
   */
  protected function _getOrderLanguage($oOrder) {

    $oxConfig = oxConfig::getInstance();
    $lgConfig = $oxConfig->getShopConfVar('aLanguageParams');

    return array_search($oOrder->getOrderLanguage(), $lgConfig);
  }
}
?>