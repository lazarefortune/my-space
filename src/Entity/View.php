<?php

namespace App\Entity;

use App\Entity\Inspiration;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ViewRepository;

/**
 * @ORM\Entity(repositoryClass=ViewRepository::class)
 * @ORM\Table(name="views")
 */
class View
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id_user")
     * })
     */
    private $idUser;

    /**
     * @var \Inspiration
     *
     * @ORM\ManyToOne(targetEntity="Inspiration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_story", referencedColumnName="id")
     * })
     */
    private $idStory;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUser()
    {
        return $this->idUser;
    }

    public function setIdUser(?User $idUser): self
    {
        $this->idUser = $idUser;

        return $this;
    }

    public function getIdStory()
    {
        return $this->idStory;
    }

    public function setIdStory(?Inspiration $idStory): self
    {
        $this->idStory = $idStory;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }
}
