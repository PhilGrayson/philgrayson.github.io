<?php
  namespace Application\Model;

  class chanGraph extends Model {
      public static $boards = array(
                      'Japanese Culture' => array(
                                                  'Anime & Manga'         => 'a',
                                                  'Anime/Cute'            => 'c',
                                                  'Anime/Wallpapers'      => 'w',
                                                  'Mecha'                 => 'm',
                                                  'Cosplay & EGL'         => 'cgl',
                                                  'Cute/Male'             => 'cm',
                                                  'Flash'                 => 'f',
                                                  'Transportation'        => 'n',
                                                  'Otaku Culture'         => 'jp',
                                                  'Pokémon'               => 'vp',
                                                  ),
                              'Interests' => array(
                                                  'Video Games'           => 'v',
                                                  'Video Game Generals'   => 'vg',
                                                  'Comics & Cartoons'     => 'co',
                                                  'Technology'            => 'g',
                                                  'Television & Film'     => 'tv',
                                                  'Weapons'               => 'k',
                                                  'Auto'                  => 'o',
                                                  'Animals & Nature'      => 'an',
                                                  'Traditional Games'     => 'tg',
                                                  'Sports'                => 'sp',
                                                  'Science & Math'        => 'sci',
                                                  'International'         => 'int',
                                                  ),
                               'Creative' => array(
                                                  'Oekaki'                => 'i',
                                                  'Papercraft & Origami'  => 'po',
                                                  'Photography'           => 'p',
                                                  'Food & Cooking'        => 'ck',
                                                  'Artwork/Critique'      => 'ic',
                                                  'Wallpapers/General'    => 'wg',
                                                  'Music'                 => 'mu',
                                                  'Fashion'               => 'fa',
                                                  'Toys'                  => 'toy',
                                                  '3DCG'                  => '3',
                                                  'Do-It-Yourself'        => 'diy',
                                                  ),
                            'Adult (18+)' => array(
                                                  'Sexy Beautiful Women'  => 's',
                                                  'Hardcore'              => 'hc',
                                                  'Hentai'                => 'h',
                                                  'Ecchi'                 => 'e',
                                                  'Yuri'                  => 'u',
                                                  'Hentai/Alternative'    => 'd',
                                                  'Yaoi'                  => 'y',
                                                  'Torrents'              => 't',
                                                  'High Resolution'       => 'hr',
                                                  'Animated GIF'          => 'gif',
                                                  ),
                                  'Other' => array(
                                                  'Travel'                => 'tv',
                                                  'Health & Fitness'      => 'fit',
                                                  'Paranormal'            => 'x',
                                                  'Literature'            => 'lit',
                                                  'Advice'                => 'adv',
                                                  'Pony'                  => 'mlp',
                                                  ),
                            'Misc. (18+)' => array(
                                                  'Random'                => 'b',
                                                  'Request'               => 'r',
                                                  'ROBOT9001'             => 'r9k',
                                                  'Politically Incorrect' => 'pol',
                                                  'Social'                => 'soc',
                                                  ),
      );

      public static function isValid($board) {
        foreach(chanGraph::$boards as $category => $boards) {
          if (in_array($board, array_values($boards))) {
            return true;
          }
        }
        return false;
      }

      public function getPostCount(array $boards) {
        $valid = array();
        foreach ($boards as $board) {
          if (chanGraph::isValid($board)) {
            $valid[] = $board;
          }
        }

        $result = array();
        if (count($valid) > 0) {
            $query = 'SELECT MAX(number) as number, '.
                            'date '.
                       'FROM posts '.
                      'WHERE board = :board';

            $stmt = $this->app['db']->prepare($query);

            foreach ($valid as $board) {
              $stmt->execute(array('board' => $board));
              $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
              $result[$board] = $data[0];
          }
        }

        return $result;
      }

      public function getPosts(array $boards, 
                             \DateTime $from = null,
                               \DateTime $to = null) {

        $valid = array();
        foreach ($boards as $board) {
          if (chanGraph::isValid($board)) {
            $valid[] = $board;
          }
        }

        if (!($from && $to)) {
          $from = new \DateTime('1 day ago');
          $to   = new \DateTime();
        } else if ($from > $to) {
          $tmp  = $to;
          $to   = $from;
          $from = $tmp;
        }

        $delta = $from->diff($to);
        if ($delta->y > 0) {
          // Group by month
          $date_format = '%Y-%m';
        } else if ($delta->m > 0) {
          // Group by Days
          $date_format = '%Y-%m-%d';
        } else if ($delta->d > 0) {
          // Group by Half days
          // TBC
          $date_format = '%Y-%m-%d';
        } else {
          // Group by hour
          $date_format = '%Y-%m-%d %H';
        }

        $query = 'SELECT SUM(number) AS number, board, date '.
                   'FROM posts '.
                  'WHERE board IN (:boards) '.
                    "AND date >= :from ".
                    "AND date <= :to ".
               'GROUP BY board, DATE_FORMAT(date, :date_format) '.
               'ORDER BY board, date ASC';

        $params = array('boards' => $valid,
                          'from' => $from,
                            'to' => $to,
                   'date_format' => $date_format);

        $types = array('boards' => \Doctrine\DBAL\Connection::PARAM_STR_ARRAY,
                       'from'   => \Doctrine\DBAL\Types\Type::DATETIME,
                       'to'     => \Doctrine\DBAL\Types\Type::DATETIME,
                  'date_format' => \Doctrine\DBAL\Types\Type::STRING);
        
        return $this->app['dbs']['4changraph']->executeQuery($query, $params, $types)
                                              ->fetchAll(\PDO::FETCH_ASSOC);
      }
    };
