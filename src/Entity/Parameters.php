<?php

namespace App\Entity;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ParametersRepository;

/**
 * @ORM\Entity(repositoryClass=ParametersRepository::class)
 */
class Parameters
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $viewCounter;

    /**
     * @ORM\Column(type="boolean")
     */
    private $emailNotifications;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_user", referencedColumnName="id_user")
     * })
     */
    private $idUser;

    public function __construct()
    {
        $this->setViewCounter( true );
        $this->setEmailNotifications( true );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getViewCounter(): ?bool
    {
        return $this->viewCounter;
    }

    public function setViewCounter(bool $viewCounter): self
    {
        $this->viewCounter = $viewCounter;

        return $this;
    }

    public function getEmailNotifications(): ?bool
    {
        return $this->emailNotifications;
    }

    public function setEmailNotifications(bool $emailNotifications): self
    {
        $this->emailNotifications = $emailNotifications;

        return $this;
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
}
