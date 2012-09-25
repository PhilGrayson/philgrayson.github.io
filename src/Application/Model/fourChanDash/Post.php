<?php

namespace Application\Model\fourChanDash;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\Model\fourChanDash\Post
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
     * @var datetime $timestamp
     */
    private $timestamp;

    /**
     * @var Application\Model\fourChanDash\Board
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
     */
    public function setCount($count)
    {
        $this->count = $count;
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
     * @param datetime $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * Get timestamp
     *
     * @return datetime 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set board
     *
     * @param Application\Model\fourChanDash\Board $board
     */
    public function setBoard(\Application\Model\fourChanDash\Board $board)
    {
        $this->board = $board;
    }

    /**
     * Get board
     *
     * @return Application\Model\fourChanDash\Board 
     */
    public function getBoard()
    {
        return $this->board;
    }
}