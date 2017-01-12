<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Audit
 *
 * @ORM\Table(name="TC_CALENDARS")
 * @ORM\Entity(repositoryClass="Arii\TimeBundle\Entity\CalendarsRepository")
 */
class Calendars
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
     * @ORM\Column(name="name", type="string", length=128)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="rule", type="string", length=255)
     */
    private $rule;

    /**
     * @ORM\ManyToOne(targetEntity="Arii\CoreBundle\Entity\Region")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="we", type="string", length=6)
     */
    private $we;
    
    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=32)
     */
    private $reference;

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
     * @return Calendars
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
     * Set rule
     *
     * @param string $rule
     * @return Calendars
     */
    public function setRule($rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get rule
     *
     * @return string 
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Set we
     *
     * @param string $we
     * @return Calendars
     */
    public function setWe($we)
    {
        $this->we = $we;

        return $this;
    }

    /**
     * Get we
     *
     * @return string 
     */
    public function getWe()
    {
        return $this->we;
    }

    /**
     * Set reference
     *
     * @param string $reference
     * @return Calendars
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return string 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set zone
     *
     * @param \Arii\TimeBundle\Entity\Zones $zone
     * @return Calendars
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
