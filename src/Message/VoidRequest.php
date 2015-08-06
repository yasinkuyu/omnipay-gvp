<?php

namespace Omnipay\Gvp\Message;

/**
 * Gvp Void Request
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class VoidRequest extends PurchaseRequest {

    protected $actionType = 'void';

}
