<?php
  namespace Application\Controller;

  use Symfony\Component\HttpFoundation\Response;

  class Blog extends Controller {
    function __construct($app) {
      parent::__construct($app);

      $this->model = new \Application\Model\Blog($app);
    }

    function index() {
      $posts = $this->model->getAll();

      // Place the posts into an array of arrays based on year month day      
      $sorted = array();
      if (count($posts) > 0) {
        foreach($posts as $post) {
          $year  = (int) $post['date']['year'];
          $month = (int) $post['date']['month'];
          $day   = (int) $post['date']['day'];
          $sorted[$year][$month][$day][] = $post;
        }

        // Arrange to be latest first
        krsort($sorted[$year][$month][$day]);
        krsort($sorted[$year][$month]);
        krsort($sorted[$year]);
      }

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
