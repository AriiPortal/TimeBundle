<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReferencesRules
 */
class ReferencesRules
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Arii\TimeBundle\Entity\References
     */
    private $reference;

    /**
     * @var \Arii\TimeBundle\Entity\Rules
     */
    private $rule;


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
     * Set reference
     *
     * @param \Arii\TimeBundle\Entity\References $reference
     * @return ReferencesRules
     */
    public function setReference(\Arii\TimeBundle\Entity\References $reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return \Arii\TimeBundle\Entity\References 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set rule
     *
     * @param \Arii\TimeBundle\Entity\Rules $rule
     * @return ReferencesRules
     */
    public function setRule(\Arii\TimeBundle\Entity\Rules $rule = null)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get rule
     *
     * @return \Arii\TimeBundle\Entity\Rules 
     */
    public function getRule()
    {
        return $this->rule;
    }
}
