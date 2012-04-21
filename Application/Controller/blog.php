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

			return $this->app['twig']->render('content/blog/index.twig', array('title' => 'Phil Grayson blog', 'posts' => $posts));
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
