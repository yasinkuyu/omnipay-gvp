<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Authorize Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class AuthorizeRequest extends PurchaseRequest {

    public function getData() {

        $this->validate('orderid');

        $data['Type'] = $this->getType();
        $data['OrderId'] = $this->getOrderId();

        return $data;
    }

}
