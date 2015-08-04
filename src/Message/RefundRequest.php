<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Purchase Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class RefundRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('orderid', 'amount');
        $currency = $this->getCurrency();

        $data['Type'] = 'Credit';
        $data['OrderId'] = $this->getOrderId();
        $data['Currency'] = $this->currencies[$currency];
        $data['Total'] = $this->getAmount();

        return $data;
    }

}
