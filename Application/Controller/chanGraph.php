<?php
	namespace Application\Controller;

	use Symfony\Component\HttpFoundation\Response;

	class chanGraph extends Controller {
		function __construct($app) {
			parent::__construct($app);
		
			$this->model  = new \Application\Model\chanGraph($this->app);
		}

		function index() {
			$boards = \Application\Model\chanGraph::$boards;
			$vars   = array ('title' => 'Misc Tools', 'boards' => $boards);
			return $this->app['twig']->render('content/4chan-graph.twig', $vars);
		}

		function jsonResponder() {
			$boards = $this->app['request']->get('boards');
			$boards = str_getcsv($boards);
			
			$from   = new \DateTime($this->app['request']->get('from'));
			$to     = new \DateTime($this->app['request']->get('to'));

			if (empty($boards)) {
				// Get all boards
				$boards = array();
				foreach(\Application\Model\chanGraph::$boards as $category => $list) {
					$boards = array_merge($boards, array_values($list));
				}
			}

			if (!($from && $to)) {
				// Set a default time range
				$from = new \DateTime('1 day ago');
				$to   = new \DateTime();
			}

			$counts  = $this->model->getPostCount($boards);
			$posts   = $this->model->getPosts($boards, $from, $to);
			$content = array();
			
			// Build the response
			if (count($counts) > 0 && count($posts) > 0) {
				foreach ($counts as $board => $count) {
						if (!empty($count['number'])) {
							$content['boards'][$board]['total'] = $count;
						}
				}

				foreach($posts as $post) {
					if (!empty($post)) {
						$content['boards'][$post['board']]['posts'][] = array('count' => $post['number'],
						                                                      'date' => $post['date']);
					}
				}
			}
			
			return new Response(json_encode($content), 200, array('Content-Type' => 'application/json'));
		}
	}
