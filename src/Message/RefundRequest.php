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

    protected $actionType = 'refund';

}
