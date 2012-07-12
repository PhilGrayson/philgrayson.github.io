<?php

namespace Server\Http;

class chanHttp extends AbstractHttp
{
  public function init($url)
  {
    $this->setUrl($url);

    $userAgent = <<<EOD
Who: philgrayson.com/chandash
Info: philgrayson.com/chandash/crawler
Email: phil@philgrayson.com
EOD;

    $this->setUserAgent(str_replace('\n', '', $userAgent));
  }


  public function sendRequest()
  {
    // Rate limit requests to 4chan.org
    $sem = sem_get(27182);
    sem_acquire($sem);

    // This process has permission to send the request
    // Wait 2 second out of courtesy
    sleep(2);
    $str = parent::sendRequest();

    sem_release($sem);

    return $str;
  }
}
