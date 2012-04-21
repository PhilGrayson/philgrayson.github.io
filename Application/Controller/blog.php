<?php
	namespace Application\Controller;

	use Symfony\Component\HttpFoundation\Response;

	class blog extends Controller {
		function __construct($app) {
			parent::__construct($app);

			$this->model = new \Application\Model\blog($app);
		}

		function index() {
			$posts = $this->model->getAll();

			// Place the posts into an array of arrays based on year month day			
			$sorted = array();
			if (count($posts) > 0) {
				foreach($posts as $post) {
					$year  = $post['date']['year'];
					$month = $post['date']['month'];
					$day   = $post['date']['day'];
					$sorted[$year][$month][$day][] = $post;
				}

				// Reverse the arrays to the latest post is first
				foreach($sorted as $year => $yearPosts) {
					foreach($yearPosts as $month => $monthPosts) {
						foreach ($monthPosts as $day => $dayPosts) {
							// Reverse days
							$dayPosts = array_reverse($dayPosts, true);
							$sorted[$year][$month][$day] = $dayPosts;
						}
						// Reverse months
						$monthPosts =  array_reverse($monthPosts, true);
						$sorted[$year][$month] = $monthPosts;
					}
				}
			}

			// Reverse years	
			$sorted = array_reverse($sorted, true);

			return $this->app['twig']->render('content/blog/index.twig', array('title' => 'Phil Grayson blog', 'posts' => $sorted));
		}

		function show($year, $month, $name) {
			$post = $this->model->get($year, $month, $name);

			if (is_array($post)) {
				return $this->app['twig']->render('content/blog/post.twig', $post);
			}
		
			$vars = array('title' => 'Cannot find content');	
			return $this->app['twig']->render('content/blog/404.twig', $vars);
		}
	}
