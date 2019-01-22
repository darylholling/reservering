<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TafelRepository")
 */
class Tafel
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxPersonen;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaxPersonen(): ?int
    {
        return $this->maxPersonen;
    }

    public function setMaxPersonen(int $maxPersonen): self
    {
        $this->maxPersonen = $maxPersonen;

        return $this;
    }

    public function __toString()
    {
     return 'Tafel: ' . $this->getId() . ' ' . $this->getMaxPersonen();
    }
}
