<?php
namespace Application\Event\Events\FourChanDash;

class DownloadBoard implements \Application\Event\EventInterface
{
  public function getTopic()
  {
    return 'BoardRequest';
  }

  public function getFunction()
  {
    return function(\Silex\Application $app, $data)
    {
      $app['monolog']['FourChanDash']->addInfo(__CLASS__ . ' handling BoardRequest' . "\n" . print_r($data, 1));
      if (!isset($data['board'])) {
        $app['monolog']['FourChanDash']->addError('Missing data key \'board\'');
        return;
      }

      if (!isset($data['dry-run'])) {
        $data['dry-run'] = false;
      }

      $board     = $data['board'];
      $boardRepo = $app['db.orm.em']['FourChanDash']->getRepository(
        'Application\Model\FourChanDash\Board'
      );


      if (!$boardRepo->findOneByName($board) instanceOf \Application\Model\FourChanDash\Board) {
        $app['monolog']['FourChanDash']->addError("'$board' is not a valid board");
        return;
      }

      $url = "https://api.4chan.org/$board/0.json";
      $http = new \Library\Http\chanHttp($url);

      $response = $http->sendRequest();

      $app['event']->publish(
        'BoardLoad',
        array(
          'board'    => $board,
          'contents' => $response,
          'dry-run'  => $data['dry-run']
        )
      );
    };
  }
}
