<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Complete Capture Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class CaptureRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('orderid', 'amount');
        $currency = $this->getCurrency();

        $data['Type'] = 'PostAuth';
        $data['OrderId'] = $this->getOrderId();
        $data['Currency'] = $this->currencies[$currency];
        $data['Total'] = $this->getAmount();

        return $data;
    }

}
