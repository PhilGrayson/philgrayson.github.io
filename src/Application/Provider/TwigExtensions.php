<?php
namespace Application\Provider;

class TwigExtensions extends \Twig_Extension
{

  public function getName()
  {
    return 'PhilGrayson Twig Extensions';
  }

  public function getFilters()
  {
    return array(
      'markdown' => new \Twig_Filter_Method($this, 'markdown')
    );
  }

  public function markdown($string)
  {
    $markdown = new \dflydev\markdown\MarkdownParser;
    return $markdown->transformMarkdown($string);
  }
}
