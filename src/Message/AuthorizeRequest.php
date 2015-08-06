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
    
    protected $actionType = 'postauth';

}
