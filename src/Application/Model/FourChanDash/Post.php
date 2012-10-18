<?php

namespace Application\Model\FourChanDash;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\Model\FourChanDash\Post
 */
class Post
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $count
     */
    private $count;

    /**
     * @var \DateTime $timestamp
     */
    private $timestamp;

    /**
     * @var Application\Model\FourChanDash\Board
     */
    private $board;


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
     * Set count
     *
     * @param integer $count
     * @return Post
     */
    public function setCount($count)
    {
        $this->count = $count;
    
        return $this;
    }

    /**
     * Get count
     *
     * @return integer 
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set timestamp
     *
     * @param \DateTime $timestamp
     * @return Post
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    
        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set board
     *
     * @param Application\Model\FourChanDash\Board $board
     * @return Post
     */
    public function setBoard(\Application\Model\FourChanDash\Board $board = null)
    {
        $this->board = $board;
    
        return $this;
    }

    /**
     * Get board
     *
     * @return Application\Model\FourChanDash\Board 
     */
    public function getBoard()
    {
        return $this->board;
    }
}
