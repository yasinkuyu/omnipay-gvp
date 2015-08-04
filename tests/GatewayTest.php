<?php namespace Omnipay\Gvp;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;

/**
 * Gvp Gateway Test
 * 
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class GatewayTest extends GatewayTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'merchantId' => 'XXXXXXXXX',
            'terminalId' => '0XXXXXXXX',
            'provUserId' => '123',
            'provPass' => '123',
            'userId' => 'XXXXXX',
            'amount' => 10.00,
            'currency' => 'TRY',
            'returnUrl' => 'http://sanalmagaza.org/return',
            'card' => new CreditCard(array(
                'number'        => '5406675406675403',
                'expiryMonth'   => '12',
                'expiryYear'    => '2015',
                'cvv'           => '000',
                'email'         => 'yasinkuyu@gmail.com',
                'firstname'     => 'Yasin',
                'lastname'      => 'Kuyu'
            )),
        );
    }

    public function testPurchase()
    {
        $this->setMockHttpResponse('PurchaseSuccess.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\Gvp\Message\Response', $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('130215141054377801316798', $response->getTransactionReference());
    }

    public function testPurchaseError()
    {
        $this->setMockHttpResponse('PurchaseFailure.txt');

        $response = $this->gateway->purchase($this->options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame('Input variable errors', $response->getMessage());
    }
}
