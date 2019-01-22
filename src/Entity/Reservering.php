<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReserveringRepository")
 */
class Reservering
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tafel")
     */
    private $tafel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $User;

    /**
     * @ORM\Column(type="datetime")
     */
    private $datum;

    /**
     * @ORM\Column(type="integer")
     */
    private $aantalPersonen;

    public function __construct()
    {
        $this->tafel = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Tafel[]
     */
    public function getTafel(): Collection
    {
        return $this->tafel;
    }

    public function addTafel(Tafel $tafel): self
    {
        if (!$this->tafel->contains($tafel)) {
            $this->tafel[] = $tafel;
        }

        return $this;
    }

    public function removeTafel(Tafel $tafel): self
    {
        if ($this->tafel->contains($tafel)) {
            $this->tafel->removeElement($tafel);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getDatum(): ?\DateTimeInterface
    {
        return $this->datum;
    }

    public function setDatum(\DateTimeInterface $datum): self
    {
        $this->datum = $datum;

        return $this;
    }

    public function getAantalPersonen(): ?int
    {
        return $this->aantalPersonen;
    }

    public function setAantalPersonen(int $aantalPersonen): self
    {
        $this->aantalPersonen = $aantalPersonen;

        return $this;
    }
}
