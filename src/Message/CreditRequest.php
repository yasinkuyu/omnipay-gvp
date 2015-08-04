<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class CreditRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('amount');
        $currency = $this->getCurrency();

        $data['Type'] = 'Credit';
        $data['OrderId'] = $this->getOrderId();
        $data['Currency'] = $this->currencies[$currency];
        $data['Total'] = $this->getAmount();
        $data['Number'] = $this->getCard()->getNumber();
        $data['Number'] = $this->getCard()->getNumber();
        $data['Expires'] = $this->getCard()->getExpiryDate('my');
        $data["Cvv2Val"] = $this->getCard()->getCvv();

        return $data;
    }

}
