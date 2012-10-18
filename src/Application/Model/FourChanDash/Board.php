<?php

namespace Application\Model\FourChanDash;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\Model\FourChanDash\Board
 */
class Board
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $description
     */
    private $description;

    /**
     * @var Application\Model\FourChanDash\BoardGroup
     */
    private $boardGroup;


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
     * Set name
     *
     * @param string $name
     * @return Board
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Board
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set boardGroup
     *
     * @param Application\Model\FourChanDash\BoardGroup $boardGroup
     * @return Board
     */
    public function setBoardGroup(\Application\Model\FourChanDash\BoardGroup $boardGroup = null)
    {
        $this->boardGroup = $boardGroup;
    
        return $this;
    }

    /**
     * Get boardGroup
     *
     * @return Application\Model\FourChanDash\BoardGroup 
     */
    public function getBoardGroup()
    {
        return $this->boardGroup;
    }
}
