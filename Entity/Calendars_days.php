<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Audit
 *
 * @ORM\Table(name="TC_CALENDARS_DAYS")
 * @ORM\Entity(repositoryClass="Arii\TimeBundle\Entity\CalendarsDaysRepository")
 */
class CalendarsDays
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
     * @ORM\ManyToOne(targetEntity="Arii\TimeBundle\Entity\Calendars")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $calendar;

    /**
     * @ORM\ManyToOne(targetEntity="Arii\TimeBundle\Entity\Days")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $day;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

}