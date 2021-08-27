<?php

namespace App\Entity;

use App\Repository\InspirationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InspirationRepository::class)
 */
class Inspiration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=false, unique=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $statut;
    
    /**
     * @var string
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $trash;

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
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\OneToMany(targetEntity=CommentaryStory::class, mappedBy="story", orphanRemoval=true)
     */
    private $commentaryStories;

    public function __construct()
    {
        $this->trash = false;
        $this->commentaryStories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
    
    public function getTrash(): ?string
    {
        return $this->trash;
    }

    public function setTrash(string $trashValue): self
    {
        $this->trash = $trashValue;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection|CommentaryStory[]
     */
    public function getCommentaryStories(): Collection
    {
        return $this->commentaryStories;
    }

    public function addCommentaryStory(CommentaryStory $commentaryStory): self
    {
        if (!$this->commentaryStories->contains($commentaryStory)) {
            $this->commentaryStories[] = $commentaryStory;
            $commentaryStory->setStory($this);
        }

        return $this;
    }

    public function removeCommentaryStory(CommentaryStory $commentaryStory): self
    {
        if ($this->commentaryStories->removeElement($commentaryStory)) {
            // set the owning side to null (unless already changed)
            if ($commentaryStory->getStory() === $this) {
                $commentaryStory->setStory(null);
            }
        }

        return $this;
    }
}
