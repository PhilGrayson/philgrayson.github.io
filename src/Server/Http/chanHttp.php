<?php

namespace Server\Http;

class chanHttp extends AbstractHttp
{
  public function init($url)
  {
    $this->setUrl($url);

    $userAgent = <<<EOD
Who: philgrayson.com/fourchandash
Info: philgrayson.com/fourchandash/crawler
Email: phil@philgrayson.com
EOD;

    $this->setUserAgent(str_replace('\n', ' ', $userAgent));
  }


  public function sendRequest()
  {
    // Rate limit requests to 4chan.org
    $sem = sem_get(27182);
    sem_acquire($sem);
    sleep(1);
    $str = parent::sendRequest();

    sem_release($sem);
    return $str;
  }
}
