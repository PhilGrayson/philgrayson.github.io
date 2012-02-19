<?php
	namespace Application\Controller;

	class Controller {
		public function __construct(\Silex\Application $app) {
			$this->app = $app;
		}
	}
