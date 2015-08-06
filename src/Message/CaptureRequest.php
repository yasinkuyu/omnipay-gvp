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

    protected $actionType = 'sales';

}
