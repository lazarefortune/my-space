<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @UniqueEntity(fields={"email"}, message="Il y a déjà un compte avec cet email")
 * @UniqueEntity(fields={"login"}, message="Il y a déjà un compte avec cet identifiant")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idUser;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="text", length=65535, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="login", type="text", length=65535, nullable=true)
     */
    private $login;

    /**
     * @var string|null
     *
     * @ORM\Column(name="password", type="text", length=65535, nullable=false)
     */
    private $password;
    
    /**
     // * @var json
     *
     * @ORM\Column(name="roles", type="json", nullable=false)
     */
    private $roles = [];

   /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $reset_token;

    /**
     * @ORM\OneToMany(targetEntity=CommentaryStory::class, mappedBy="user", orphanRemoval=true)
     */
    private $commentaryStories;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $secondEmail;

    /**
     * @ORM\OneToMany(targetEntity=MailSend::class, mappedBy="author", orphanRemoval=true)
     */
    private $mailSends;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $activation_token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $last_login;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $agree_rgpd;

    /**
     * @ORM\ManyToOne(targetEntity=Promotion::class, inversedBy="users")
     */
    private $promo;

    /**
     * @ORM\ManyToOne(targetEntity=School::class, inversedBy="students")
     */
    private $school;

    /**
     * @ORM\OneToMany(targetEntity=Course::class, mappedBy="user", orphanRemoval=true)
     */
    private $courses;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notifAccount;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $notifAppStory;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $profile_picture;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_deleted;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commentaryStories = new ArrayCollection();
        $this->mailSends = new ArrayCollection();
        $this->roles = ['ROLE_MEMBER'];
        $this->courses = new ArrayCollection();
    }

    public function getIdUser(): ?int
    {
        return $this->idUser;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }
    
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    // public function getRoles(): ?array
    // {
    //     return $this->roles;
    // }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }
    
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function setResetToken(?string $token): self
    {
        $this->reset_token = $token;
        return $this;
    }    

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $commentaryStory->setUser($this);
        }

        return $this;
    }

    public function removeCommentaryStory(CommentaryStory $commentaryStory): self
    {
        if ($this->commentaryStories->removeElement($commentaryStory)) {
            // set the owning side to null (unless already changed)
            if ($commentaryStory->getUser() === $this) {
                $commentaryStory->setUser(null);
            }
        }

        return $this;
    }

    public function getSecondEmail(): ?string
    {
        return $this->secondEmail;
    }

    public function setSecondEmail(?string $secondEmail): self
    {
        $this->secondEmail = $secondEmail;

        return $this;
    }

    /**
     * @return Collection|MailSend[]
     */
    public function getMailSends(): Collection
    {
        return $this->mailSends;
    }

    public function addMailSend(MailSend $mailSend): self
    {
        if (!$this->mailSends->contains($mailSend)) {
            $this->mailSends[] = $mailSend;
            $mailSend->setAuthor($this);
        }

        return $this;
    }

    public function removeMailSend(MailSend $mailSend): self
    {
        if ($this->mailSends->removeElement($mailSend)) {
            // set the owning side to null (unless already changed)
            if ($mailSend->getAuthor() === $this) {
                $mailSend->setAuthor(null);
            }
        }

        return $this;
    }

    public function getActivationToken(): ?string
    {
        return $this->activation_token;
    }

    public function setActivationToken(?string $activation_token): self
    {
        $this->activation_token = $activation_token;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTimeInterface $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }

    public function getAgreeRgpd(): ?bool
    {
        return $this->agree_rgpd;
    }

    public function setAgreeRgpd(?bool $agree_rgpd): self
    {
        $this->agree_rgpd = $agree_rgpd;

        return $this;
    }

    public function getPromo(): ?Promotion
    {
        return $this->promo;
    }

    public function setPromo(?Promotion $promo): self
    {
        $this->promo = $promo;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): self
    {
        $this->school = $school;

        return $this;
    }

    /**
     * @return Collection|Course[]
     */
    public function getCourses(): Collection
    {
        return $this->courses;
    }

    public function addCourse(Course $course): self
    {
        if (!$this->courses->contains($course)) {
            $this->courses[] = $course;
            $course->setUser($this);
        }

        return $this;
    }

    public function removeCourse(Course $course): self
    {
        if ($this->courses->removeElement($course)) {
            // set the owning side to null (unless already changed)
            if ($course->getUser() === $this) {
                $course->setUser(null);
            }
        }

        return $this;
    }

    public function getNotifAccount(): ?bool
    {
        return $this->notifAccount;
    }

    public function setNotifAccount(?bool $notifAccount): self
    {
        $this->notifAccount = $notifAccount;

        return $this;
    }

    public function getNotifAppStory(): ?bool
    {
        return $this->notifAppStory;
    }

    public function setNotifAppStory(?bool $notifAppStory): self
    {
        $this->notifAppStory = $notifAppStory;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profile_picture;
    }

    public function setProfilePicture(?string $profile_picture): self
    {
        $this->profile_picture = $profile_picture;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->is_deleted;
    }

    public function setIsDeleted(?bool $is_deleted): self
    {
        $this->is_deleted = $is_deleted;

        return $this;
    }

}
