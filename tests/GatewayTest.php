<?php

namespace Omnipay\Gvp\Tests;

use Omnipay\Gvp\Gateway;
use Omnipay\Gvp\Message\AbstractRequest;
use Omnipay\Tests\GatewayTestCase;
use Symfony\Component\HttpFoundation\Request;

class GatewayTest extends GatewayTestCase
{
    /** @var Gateway */
    protected $gateway;

    /** Garanti'nin herkese açık test hesabı */
    private const CREDS = ['merchantId' => '7000679', 'terminalId' => '30691297', 'password' => '123qweASD/', 'storeKey' => '12345678', 'testMode' => true];

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->initialize(self::CREDS);
    }

    public function testPurchaseBuildsSignedHostedPaymentForm(): void
    {
        $r = $this->gateway->purchase([
            'amount' => '149.90', 'currency' => 'TRY', 'transactionId' => 'SIP-1', 'returnUrl' => 'https://shop.test/r',
            'installment' => 3, 'clientIp' => '1.2.3.4', 'card' => ['email' => 'a@b.test'], 'companyName' => 'Magaza', 'refreshTime' => 3,
        ])->send();

        $this->assertTrue($r->isRedirect());
        $this->assertSame('POST', $r->getRedirectMethod());
        $this->assertSame(AbstractRequest::TEST_GATE, $r->getRedirectUrl());
        $d = $r->getRedirectData();
        $this->assertSame(['3D_OOS_PAY', 'TEST', '512', '14990', '949', '3', 'sales', 'PROVAUT'],
            [$d['secure3dsecuritylevel'], $d['mode'], $d['apiversion'], $d['txnamount'], $d['txncurrencycode'], $d['txninstallmentcount'], $d['txntype'], $d['terminalprovuserid']]);
        $this->assertSame('https://shop.test/r', $d['errorurl']);
        $this->assertArrayNotHasKey('cardnumber', $d);
        $expected = strtoupper(hash('sha512', '30691297SIP-114990949https://shop.test/rhttps://shop.test/rsales312345678'
            . strtoupper(sha1('123qweASD/030691297'))));
        $this->assertSame($expected, $d['secure3dhash']);
    }

    public function testSinglePaymentHasEmptyInstallment(): void
    {
        $d = $this->gateway->purchase(['amount' => '1.00', 'transactionId' => 'X', 'returnUrl' => 'https://shop.test/r', 'installment' => 1])->send()->getRedirectData();
        $this->assertSame('', $d['txninstallmentcount']);
    }

    public function testLiveEndpoint(): void
    {
        $this->gateway->setTestMode(false);
        $r = $this->gateway->purchase(['amount' => '1.00', 'transactionId' => 'X', 'returnUrl' => 'https://shop.test/r'])->send();
        $this->assertSame(AbstractRequest::LIVE_GATE, $r->getRedirectUrl());
        $this->assertSame('PROD', $r->getRedirectData()['mode']);
    }

    /** Garanti test ortamından alınmış gerçek onaylı dönüş (2026-11-21) */
    private function realReturn(): string
    {
        return trim(file_get_contents(__DIR__ . '/Mock/ReturnApproved.txt'));
    }

    private function returning(string $body, array $creds = self::CREDS): void
    {
        $this->gateway = new Gateway($this->getHttpClient(), Request::create('/r', 'POST', [], [], [], [], $body));
        $this->gateway->initialize($creds);
    }

    public function testRealApprovedReturnIsVerified(): void
    {
        $this->returning($this->realReturn());
        $r = $this->gateway->completePurchase(['transactionId' => 'GVP1795289401', 'amount' => '1.00'])->send();

        $this->assertTrue($r->isVerified());
        $this->assertTrue($r->isSuccessful());
        $this->assertSame('completed', $r->getTransactionStatus());
        $this->assertSame('632509243004', $r->getTransactionReference());
        $this->assertSame('304919', $r->getAuthCode());
        $this->assertSame('42822090****8015', $r->getMaskedCard());
        $this->assertSame('1.00', $r->getAmount());
        $this->assertSame(1, $r->getInstallment());
    }

    public function testTamperedReturnIsRejected(): void
    {
        $this->returning(str_replace('procreturncode=00', 'procreturncode=05', $this->realReturn()));
        $this->assertFalse($this->gateway->completePurchase()->send()->isVerified());
    }

    public function testWrongStoreKeyIsRejected(): void
    {
        $this->returning($this->realReturn(), ['storeKey' => 'BASKA'] + self::CREDS);
        $this->assertFalse($this->gateway->completePurchase()->send()->isVerified());
    }

    public function testOtherOrderOrAmountIsRejected(): void
    {
        $this->returning($this->realReturn());
        $this->assertFalse($this->gateway->completePurchase(['transactionId' => 'BASKA'])->send()->isVerified());
        $this->returning($this->realReturn());
        $this->assertFalse($this->gateway->completePurchase(['transactionId' => 'GVP1795289401', 'amount' => '2.00'])->send()->isVerified());
    }

    public function testUnsignedResultFieldsAreRejected(): void
    {
        // sonuç kodunu imza dışına çıkaran bir hashparams kabul edilmez
        $fields = ['clientid' => '30691297', 'oid' => 'X', 'response' => 'Approved', 'procreturncode' => '00', 'mdstatus' => '1', 'hashparams' => 'clientid:oid:'];
        $fields['hash'] = base64_encode(sha1('30691297X12345678', true));
        $this->assertFalse($this->gateway->completePurchase(['fields' => $fields])->send()->isVerified());
    }

    public function testDeclinedReturn(): void
    {
        $f = ['clientid' => '30691297', 'oid' => 'X', 'authcode' => '', 'procreturncode' => '51', 'response' => 'Declined', 'mdstatus' => '1',
            'errmsg' => 'Yetersiz bakiye', 'hashparams' => 'clientid:oid:authcode:procreturncode:response:mdstatus:'];
        $f['hash'] = base64_encode(sha1('30691297X51Declined1' . '12345678', true));
        $r = $this->gateway->completePurchase(['fields' => $f])->send();
        $this->assertTrue($r->isVerified());
        $this->assertFalse($r->isSuccessful());
        $this->assertSame('Yetersiz bakiye', $r->getMessage());
        $this->assertSame('failed', $r->getTransactionStatus());
    }

    public function test3DFailureMessage(): void
    {
        $f = ['clientid' => '30691297', 'oid' => 'X', 'procreturncode' => '99', 'response' => 'Error', 'mdstatus' => '0',
            'mderrormessage' => 'Not authenticated', 'hashparams' => 'clientid:oid:procreturncode:response:mdstatus:'];
        $f['hash'] = base64_encode(sha1('30691297X99Error0' . '12345678', true));
        $this->assertSame('Not authenticated', $this->gateway->completePurchase(['fields' => $f])->send()->getMessage());
    }

    public function testInquiry(): void
    {
        $this->setMockHttpResponse('InquirySuccess.txt');
        $r = $this->gateway->fetchTransaction(['transactionId' => 'GVP1795289401'])->send();
        $this->assertTrue($r->isSuccessful());
        $this->assertSame('APPROVED', $r->getOrderInquiry()['Status']);

        parse_str((string) $this->getMockClient()->getLastRequest()->getBody(), $post);
        $this->assertStringContainsString('<Type>orderinq</Type>', $post['data']);
        $this->assertStringContainsString('<ProvUserID>PROVAUT</ProvUserID>', $post['data']);
    }

    public function testVoidUsesRefundUserAndReference(): void
    {
        $this->setMockHttpResponse('VoidDeclined.txt');
        $r = $this->gateway->void(['transactionId' => 'GVP1795289401', 'transactionReference' => '632509243004'])->send();
        $this->assertTrue($r->isReadable());
        $this->assertFalse($r->isSuccessful());
        $this->assertSame('İşleminizi gerçekleştiremiyoruz.Tekrar deneyiniz', $r->getMessage());

        parse_str((string) $this->getMockClient()->getLastRequest()->getBody(), $post);
        $x = $post['data'];
        $this->assertStringContainsString('<ProvUserID>PROVRFN</ProvUserID>', $x);
        $this->assertStringContainsString('<Type>void</Type>', $x);
        $this->assertStringContainsString('<OriginalRetrefNum>632509243004</OriginalRetrefNum>', $x);
        $this->assertStringContainsString('<Amount>100</Amount>', $x);
    }

    public function testRefundAmountInKurus(): void
    {
        $this->setMockHttpResponse('VoidDeclined.txt');
        $this->gateway->refund(['transactionId' => 'X', 'transactionReference' => 'R', 'amount' => '12.34'])->send();
        parse_str((string) $this->getMockClient()->getLastRequest()->getBody(), $post);
        $this->assertStringContainsString('<Amount>1234</Amount>', $post['data']);
        $this->assertStringContainsString('<Type>refund</Type>', $post['data']);
    }
}
