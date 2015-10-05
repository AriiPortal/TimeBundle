<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Audit
 *
 * @ORM\Table(name="TC_REFERENCES")
 * @ORM\Entity(repositoryClass="Arii\TimeBundle\Entity\ReferencesRepository")
 */
class References
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=20, unique=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="year_from", type="smallint" )
     */
    private $year_from;

    /**
     * @var integer
     *
     * @ORM\Column(name="year_to", type="smallint" )
     */
    private $year_to;

    /**
     * @ORM\ManyToOne(targetEntity="Arii\TimeBundle\Entity\Zones")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $zone;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=512)
     */
    private $comment;
    
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
     * Set logtime
     *
     * @param \DateTime $logtime
     * @return Audit
     */
    public function setLogtime($logtime)
    {
        $this->logtime = $logtime;
    
        return $this;
    }

    /**
     * Get logtime
     *
     * @return \DateTime 
     */
    public function getLogtime()
    {
        return $this->logtime;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return Audit
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return Audit
     */
    public function setAction($action)
    {
        $this->action = $action;
    
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Audit
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return Audit
     */
    public function setModule($module)
    {
        $this->module = $module;
    
        return $this;
    }

    /**
     * Get module
     *
     * @return string 
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set user
     *
     * @param \Arii\UserBundle\Entity\User $user
     * @return Audit
     */
    public function setUser(\Arii\UserBundle\Entity\User $user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Arii\UserBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Audit
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return References
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
     * Set year_from
     *
     * @param integer $yearFrom
     * @return References
     */
    public function setYearFrom($yearFrom)
    {
        $this->year_from = $yearFrom;

        return $this;
    }

    /**
     * Get year_from
     *
     * @return integer 
     */
    public function getYearFrom()
    {
        return $this->year_from;
    }

    /**
     * Set year_to
     *
     * @param integer $yearTo
     * @return References
     */
    public function setYearTo($yearTo)
    {
        $this->year_to = $yearTo;

        return $this;
    }

    /**
     * Get year_to
     *
     * @return integer 
     */
    public function getYearTo()
    {
        return $this->year_to;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return References
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
     * Set comment
     *
     * @param string $comment
     * @return References
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set zone
     *
     * @param \Arii\TimeBundle\Entity\Zones $zone
     * @return References
     */
    public function setZone(\Arii\TimeBundle\Entity\Zones $zone = null)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone
     *
     * @return \Arii\TimeBundle\Entity\Zones 
     */
    public function getZone()
    {
        return $this->zone;
    }
}
