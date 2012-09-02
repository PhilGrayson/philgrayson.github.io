<?php

namespace Server\Http;

abstract class AbstractHttp
{
  public $url;
  public $headers;

  public function __construct($url = '')
  {
    if (!empty($url)) {
      $this->init($url);
    }
  }

  public abstract function init($url);

  public function setUrl($url)
  {
    $this->url = $url;
  }

  public function setHeaders(array $headers)
  {
    $this->headers = $headers;
  }

  public function setUserAgent($userAgent)
  {
    $this->userAgent = $userAgent;
  }

  public function sendRequest()
  {
    return '';
    if (strlen($this->url) > 0) {
      $curl = \curl_init($this->url);

      if (is_array($this->headers)) {
        \curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
      }

      if (strlen($this->userAgent) > 0) {
        \curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
      }

      \curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

      return \curl_exec($curl);
    }

    return '';
  }
}
