<?php

namespace App\Entity;

use App\Repository\CommentaryStoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentaryStoryRepository::class)
 */
class CommentaryStory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_at;

    /**
     * @ORM\Column(type="boolean")
     */
    private $trash;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commentaryStories")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id_user", nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Inspiration::class, inversedBy="commentaryStories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $story;

    public function __construct()
    {
        $this->trash = false;
        $this->created_at = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

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

    public function getTrash(): ?bool
    {
        return $this->trash;
    }

    public function setTrash(bool $trash): self
    {
        $this->trash = $trash;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getStory(): ?Inspiration
    {
        return $this->story;
    }

    public function setStory(?Inspiration $story): self
    {
        $this->story = $story;

        return $this;
    }
}
