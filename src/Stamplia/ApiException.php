<?php
/**
 * ApiException.php
 *
 * Copyright Kiwup
 */

namespace Stamplia;

class ApiException extends \Exception
{
    public $url;
    public $method;
    public $rawResponse;

    /**
     * @param string $message
     * @param int $code
     * @param string $url
     * @param string $method
     * @param string $rawResponse
     */
    public function __construct($message = "", $code, $url, $method, $rawResponse)
    {
        parent::__construct($message, $code);
        $this->url = $url;
        $this->method = $method;
        $this->rawResponse = $rawResponse;
    }

    public function __toString()
    {
        return strtoupper($this->method) . ' ' .$this->url. ' returned error '.$this->code. ': '.$this->getMessage();
    }
}
