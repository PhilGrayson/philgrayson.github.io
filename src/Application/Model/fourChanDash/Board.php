<?php

namespace Application\Model\fourChanDash;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\Model\fourChanDash\Board
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
     * @var Application\Model\fourChanDash\BoardGroup
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
     */
    public function setName($name)
    {
        $this->name = $name;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
     * @param Application\Model\fourChanDash\BoardGroup $boardGroup
     */
    public function setBoardGroup(\Application\Model\fourChanDash\BoardGroup $boardGroup)
    {
        $this->boardGroup = $boardGroup;
    }

    /**
     * Get boardGroup
     *
     * @return Application\Model\fourChanDash\BoardGroup 
     */
    public function getBoardGroup()
    {
        return $this->boardGroup;
    }
}