<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CalendarsDays
 */
class CalendarsDays
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Arii\TimeBundle\Entity\Calendars
     */
    private $calendar;

    /**
     * @var \Arii\TimeBundle\Entity\Days
     */
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

    /**
     * Set calendar
     *
     * @param \Arii\TimeBundle\Entity\Calendars $calendar
     * @return CalendarsDays
     */
    public function setCalendar(\Arii\TimeBundle\Entity\Calendars $calendar = null)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Get calendar
     *
     * @return \Arii\TimeBundle\Entity\Calendars 
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set day
     *
     * @param \Arii\TimeBundle\Entity\Days $day
     * @return CalendarsDays
     */
    public function setDay(\Arii\TimeBundle\Entity\Days $day = null)
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get day
     *
     * @return \Arii\TimeBundle\Entity\Days 
     */
    public function getDay()
    {
        return $this->day;
    }
}
