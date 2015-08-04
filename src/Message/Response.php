<?php

namespace Omnipay\Gvp\Message;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

/**
 * Gvp Response
 *
 * (c) Yasin Kuyu
 * 2015, insya.com
 * http://www.github.com/yasinkuyu/omnipay-gvp
 */
class Response extends AbstractResponse implements RedirectResponseInterface {

    /**
     * Constructor
     *
     * @param  RequestInterface         $request
     * @param  string                   $data / response data
     * @throws InvalidResponseException
     */
    public function __construct(RequestInterface $request, $data) {
        $this->request = $request;
        try {
            $this->data = (array) simplexml_load_string($data);
        } catch (\Exception $ex) {
            throw new InvalidResponseException();
        }
    }

    /**
     * Whether or not response is successful
     * @return boolean
     */
    public function isSuccessful() {
        return (string) $this->data["Transaction"]->Response->Code === '00';
    }

    /**
     * Get is redirect
     * @return boolean
     */
    public function isRedirect() {
        return false; //todo
    }

    /**
     * Get a code describing the status of this response
     * @return string
     */
    public function getCode() {
        return $this->isSuccessful() ? $this->data["Transaction"]->Response->ReasonCode : parent::getCode();
    }

    /**
     * Get transaction reference
     *
     * @return string
     */
    public function getTransactionReference() {

        return $this->isSuccessful() ? $this->data["Transaction"]->Response->RetrefNum : '';
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage() {
        if ($this->isSuccessful()) {
            return $this->data["Transaction"]->Response->Message;
        }
    }
    
    /**
     * Get message
     *
     * @return string
     */
    public function getError() {
        return $this->data["Transaction"]->Response->ErrorMsg . " / " . $this->data["Transaction"]->Response->SysErrMsg;
    }
    
    /**
     * Get Redirect url
     *
     * @return string
     */
    public function getRedirectUrl() {
        if ($this->isRedirect()) {
            $data = array(
                'TransId' => $this->data["Transaction"]->RetrefNum
            );
            return $this->getRequest()->getEndpoint() . '/test/index?' . http_build_query($data);
        }
    }

    /**
     * Get Redirect method
     *
     * @return POST
     */
    public function getRedirectMethod() {
        return 'POST';
    }

    /**
     * Get Redirect url
     *
     * @return null
     */
    public function getRedirectData() {
        return null;
    }

}
