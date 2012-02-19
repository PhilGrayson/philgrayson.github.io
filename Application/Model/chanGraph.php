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

			public function getPostCount($board) {
				if (Board::isValid($board)) {
					$query = 'SELECT number, date '.
					           'FROM posts '.
										'WHERE board = :board '.
										'ORDER BY date '.
										'DESC LIMIT 1';
					return $this->app['db']->fetchAssoc($query, array('board' => $board));
				}

				return false;
			}
		};
