<?php

namespace Arii\TimeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Audit
 *
 * @ORM\Table(name="TC_REFERENCES_RULES")
 * @ORM\Entity(repositoryClass="Arii\TimeBundle\Entity\ReferencesRulesRepository")
 */
class ReferencesRules
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
     * @ORM\ManyToOne(targetEntity="Arii\TimeBundle\Entity\References")
     * @ORM\JoinColumn(nullable=true)
     **/
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="Arii\TimeBundle\Entity\Rules")
     * @ORM\JoinColumn(nullable=true)
     **/
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

}