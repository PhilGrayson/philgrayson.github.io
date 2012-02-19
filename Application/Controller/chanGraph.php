<?php
	namespace Application\Controller;

	class chanGraph extends Controller {
		function index() {
			$model  = new \Application\Model\chanGraph($this->app);
			$boards = \Application\Model\chanGraph::$boards;
			$vars   = array ('title' => 'Misc Tools', 'boards' => $boards);
			return $this->app['twig']->render('content/4chan-graph.twig', $vars);
		}
	}
