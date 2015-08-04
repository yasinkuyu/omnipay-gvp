<?php

namespace Omnipay\Gvp\Message;

use DOMDocument;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Gvp Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class PurchaseRequest extends AbstractRequest {

    protected $endpoint = '';
    protected $endpoints = [
        'test' => 'https://sanalposprov.garanti.com.tr/VPServlet',
        'purchase' => 'https://sanalposprov.garanti.com.tr/VPServlet'
    ];
   
    protected $currencies = [
        'TRY' => 949,
        'YTL' => 949,
        'TRL' => 949,
        'TL' => 949,
        'USD' => 840,
        'EUR' => 978,
        'GBP' => 826,
        'JPY' => 392
    ];

    public function getData() {

        $this->validate('amount', 'card');
        $this->getCard()->validate();
        $currency = $this->getCurrency();

        $data['Terminal']['ProvUserID'] = $this->getOrderId();
        $data['Terminal']['HashData'] = $this->getOrderId();
        $data['Terminal']['UserID'] = $this->getUserName();
        $data['Terminal']['ID'] = $this->getTerminalId();
        $data['Terminal']['MerchantID'] = $this->getMerchantId();
        
        $data['Transaction']['Type'] = $this->getType();
        $data['Transaction']['CurrencyCode'] = $this->currencies[$currency];
        $data['Transaction']['InstallmentCnt'] = $this->getInstallment();
        $data['Transaction']['Amount'] = $this->getAmount();
        $data['Transaction']['CardholderPresentCode'] = "";
        
        $data['Card']['Number'] = $this->getCard()->getNumber();
        $data['Card']['Expires'] = $this->getCard()->getExpiryDate('my');
        $data['Card']["Cvv2Val"] = $this->getCard()->getCvv();
        
        $data['Customer']["IPAddress"] = $this->getClientIp();
        $data['Customer']['Email'] = $this->getCard()->getEmail();

        return $data;
    }

    public function sendData($data) {

        // API info
        $data['Version'] = "v0.01";
        $data['Mode'] = $this->getTestMode() ? 'TEST' : 'PROD';
        $data['Name'] = $this->getUserName();
        $data['MerchantId']['Password'] = $this->getPassword();

        // Build api post url
        $this->endpoint = $this->getTestMode() == TRUE ? $this->endpoints["test"] :  $this->endpoints["purchase"];

        $document = new DOMDocument('1.0', 'UTF-8');
        $root = $document->createElement('GVPSRequest');

        // Each array element 
        foreach ($data as $id => $value) {
            $root->appendChild($document->createElement($id, $value));
        }

        $document->appendChild($root);

        

        // Set money points (maxi puan)
        $extra = $document->createElement('Extra');
        if (!empty($this->getMoneyPoints())) {
            $extra->appendChild($document->createElement('MAXIPUAN', $this->getMoneyPoints()));
            $root->appendChild($extra);
        }

        // Get money points (maxi puan)
        if (!empty($this->getMoneyPoints())) {
            $extra->appendChild($document->createElement('MAXIPUANSORGU', 'MAXIPUANSORGU'));
            $root->appendChild($extra);
        }

        $billTo = $document->createElement('BillTo');
        foreach ($dataBill as $id => $value) {
            $billTo->appendChild($document->createElement($id, $value));
        }
        $root->appendChild($billTo);

        // Post to Gvp
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded'
        );

        // Register the payment
        $this->httpClient->setConfig(array(
            'curl.options' => array(
                'CURLOPT_SSL_VERIFYHOST' => 2,
                'CURLOPT_SSLVERSION' => 0,
                'CURLOPT_SSL_VERIFYPEER' => 0,
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_POST' => 1
            )
        ));

        $httpResponse = $this->httpClient->post($this->endpoint, $headers, $document->saveXML())->send();

        return $this->response = new Response($this, $httpResponse->getBody());
    }

    public function getBank() {
        return $this->getParameter('bank');
    }

    public function setBank($value) {
        return $this->setParameter('bank', $value);
    }

    public function getUserName() {
        return $this->getParameter('username');
    }

    public function setUserName($value) {
        return $this->setParameter('username', $value);
    }

    public function getTerminalId() {
        return $this->getParameter('terminalId');
    }

    public function setTerminalId($value) {
        return $this->setParameter('terminalId', $value);
    }

    public function getPassword() {
        return $this->getParameter('password');
    }

    public function setPassword($value) {
        return $this->setParameter('password', $value);
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
