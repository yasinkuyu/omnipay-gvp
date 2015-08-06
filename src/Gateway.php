<?php

namespace Omnipay\Gvp;

use Omnipay\Common\AbstractGateway;

/**
 * Gvp Gateway
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class Gateway extends AbstractGateway {

    public function getName() {
        return 'Gvp';
    }

    public function getDefaultParameters() {
        return array(
            'terminalId' => '',
            'merchandId' => '',
            'username' => 'PROVAUT',
            'password' => '123qweASD',
            'refundusername' => 'PROVRFN',
            'refundpassword' => '123qweASD',
            'installment' => '',
            'type' => 'preauth',
            'currency' => 'TRY',
            'testMode' => false
        );
    }

    public function authorize(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Gvp\Message\AuthorizeRequest', $parameters);
    }

    public function capture(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Gvp\Message\CaptureRequest', $parameters);
    }

    public function purchase(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Gvp\Message\PurchaseRequest', $parameters);
    }

    public function refund(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Gvp\Message\RefundRequest', $parameters);
    }

    public function void(array $parameters = array()) {
        return $this->createRequest('\Omnipay\Gvp\Message\VoidRequest', $parameters);
    }

    public function getMerchantId() {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value) {
        return $this->setParameter('merchantId', $value);
    }

    public function getTerminalId() {
        return $this->getParameter('terminalId');
    }

    public function setTerminalId($value) {
        return $this->setParameter('terminalId', $value);
    }

    public function getUserName() {
        return $this->getParameter('username');
    }

    public function setUserName($value) {
        return $this->setParameter('username', $value);
    }

    public function getPassword() {
        return $this->getParameter('password');
    }

    public function setPassword($value) {
        return $this->setParameter('password', $value);
    }

    public function getRefundUserName() {
        return $this->getParameter('refundusername');
    }

    public function setRefundUserName($value) {
        return $this->setParameter('refundusername', $value);
    }

    public function getRefundPassword() {
        return $this->getParameter('refundpassword');
    }

    public function setRefundPassword($value) {
        return $this->setParameter('refundpassword', $value);
    }

    public function getSecurekey() {
        return $this->getParameter('securekey');
    }

    public function setSecurekey($value) {
        return $this->setParameter('securekey', $value);
    }
    
    public function getInstallment() {
        return $this->getParameter('installment');
    }

    public function setInstallment($value) {
        return $this->setParameter('installment', $value);
    }

    public function getType() {
        return $this->getParameter('type');
    }

    public function setType($value) {
        return $this->setParameter('type', $value);
    }

    public function getOrderId() {
        return $this->getParameter('orderid');
    }

    public function setOrderId($value) {
        return $this->setParameter('orderid', $value);
    }

}
