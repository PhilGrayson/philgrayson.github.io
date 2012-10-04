<?php

namespace Application\Model\Blog;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\Model\Blog\Post
 */
class Post
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $title
     */
    private $title;

    /**
     * @var string $slug
     */
    private $slug;

    /**
     * @var \DateTime $date
     */
    private $date;

    /**
     * @var string $blurb
     */
    private $blurb;

    /**
     * @var string $contents
     */
    private $contents;

    /**
     * @var Application\Model\Blog\Category
     */
    private $category;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Post
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return Post
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }

    /**
     * Get slug
     *
     * @return string 
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Post
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set blurb
     *
     * @param string $blurb
     * @return Post
     */
    public function setBlurb($blurb)
    {
        $this->blurb = $blurb;
    
        return $this;
    }

    /**
     * Get blurb
     *
     * @return string 
     */
    public function getBlurb()
    {
        return $this->blurb;
    }

    /**
     * Set contents
     *
     * @param string $contents
     * @return Post
     */
    public function setContents($contents)
    {
        $this->contents = $contents;
    
        return $this;
    }

    /**
     * Get contents
     *
     * @return string 
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Set category
     *
     * @param Application\Model\Blog\Category $category
     * @return Post
     */
    public function setCategory(\Application\Model\Blog\Category $category = null)
    {
        $this->category = $category;
    
        return $this;
    }

    /**
     * Get category
     *
     * @return Application\Model\Blog\Category 
     */
    public function getCategory()
    {
        return $this->category;
    }
}
