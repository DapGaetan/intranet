<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'Se compte exist déjà.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $last_name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $job = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?UserProfile $profile = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Department $department = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $tickets;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\ManyToMany(targetEntity: Ticket::class, mappedBy: 'assigned_to')]
    private Collection $assigned_tickets;

    /**
     * @var Collection<int, Leave>
     */
    #[ORM\OneToMany(targetEntity: Leave::class, mappedBy: 'user')]
    private Collection $leaves;

    /**
     * @var Collection<int, Document>
     */
    #[ORM\OneToMany(targetEntity: Document::class, mappedBy: 'created_by')]
    private Collection $documents;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'created_by')]
    private Collection $events;

    /**
     * @var Collection<int, Newsletter>
     */
    #[ORM\OneToMany(targetEntity: Newsletter::class, mappedBy: 'created_by')]
    private Collection $newsletters;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'created_by')]
    private Collection $projects;

    /**
     * @var Collection<int, Task>
     */
    #[ORM\ManyToMany(targetEntity: Task::class, mappedBy: 'assigned_to')]
    private Collection $tasks;

    /**
     * @var Collection<int, Training>
     */
    #[ORM\OneToMany(targetEntity: Training::class, mappedBy: 'created_by')]
    private Collection $trainings;

    /**
     * @var Collection<int, Registration>
     */
    #[ORM\OneToMany(targetEntity: Registration::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $registrations;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'created_by')]
    private Collection $topics;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'created_by')]
    private Collection $posts;

    /**
     * @var Collection<int, VehicleReservation>
     */
    #[ORM\OneToMany(targetEntity: VehicleReservation::class, mappedBy: 'user')]
    private Collection $vehicleReservations;

    /**
     * @var Collection<int, RoomReservation>
     */
    #[ORM\ManyToMany(targetEntity: RoomReservation::class, mappedBy: 'assigned_to')]
    private Collection $roomReservations;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->assigned_tickets = new ArrayCollection();
        $this->leaves = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->newsletters = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->trainings = new ArrayCollection();
        $this->registrations = new ArrayCollection();
        $this->topics = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->vehicleReservations = new ArrayCollection();
        $this->roomReservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(string $job): static
    {
        $this->job = $job;

        return $this;
    }

    public function getProfile(): ?UserProfile
    {
        return $this->profile;
    }

    public function setProfile(UserProfile $profile): static
    {
        // set the owning side of the relation if necessary
        if ($profile->getUser() !== $this) {
            $profile->setUser($this);
        }

        $this->profile = $profile;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setUser($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getUser() === $this) {
                $ticket->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assigned_tickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): static
    {
        if (!$this->assigned_tickets->contains($assignedTicket)) {
            $this->assigned_tickets->add($assignedTicket);
            $assignedTicket->addAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): static
    {
        if ($this->assigned_tickets->removeElement($assignedTicket)) {
            $assignedTicket->removeAssignedTo($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Leave>
     */
    public function getLeaves(): Collection
    {
        return $this->leaves;
    }

    public function addLeaf(Leave $leaf): static
    {
        if (!$this->leaves->contains($leaf)) {
            $this->leaves->add($leaf);
            $leaf->setUser($this);
        }

        return $this;
    }

    public function removeLeaf(Leave $leaf): static
    {
        if ($this->leaves->removeElement($leaf)) {
            // set the owning side to null (unless already changed)
            if ($leaf->getUser() === $this) {
                $leaf->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setCreatedBy($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCreatedBy() === $this) {
                $document->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setCreatedBy($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCreatedBy() === $this) {
                $event->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Newsletter>
     */
    public function getNewsletters(): Collection
    {
        return $this->newsletters;
    }

    public function addNewsletter(Newsletter $newsletter): static
    {
        if (!$this->newsletters->contains($newsletter)) {
            $this->newsletters->add($newsletter);
            $newsletter->setCreatedBy($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): static
    {
        if ($this->newsletters->removeElement($newsletter)) {
            // set the owning side to null (unless already changed)
            if ($newsletter->getCreatedBy() === $this) {
                $newsletter->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->setCreatedBy($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            // set the owning side to null (unless already changed)
            if ($project->getCreatedBy() === $this) {
                $project->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->addAssignedTo($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            $task->removeAssignedTo($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Training>
     */
    public function getTrainings(): Collection
    {
        return $this->trainings;
    }

    public function addTraining(Training $training): static
    {
        if (!$this->trainings->contains($training)) {
            $this->trainings->add($training);
            $training->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTraining(Training $training): static
    {
        if ($this->trainings->removeElement($training)) {
            // set the owning side to null (unless already changed)
            if ($training->getCreatedBy() === $this) {
                $training->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Registration>
     */
    public function getRegistrations(): Collection
    {
        return $this->registrations;
    }

    public function addRegistration(Registration $registration): static
    {
        if (!$this->registrations->contains($registration)) {
            $this->registrations->add($registration);
            $registration->setUser($this);
        }

        return $this;
    }

    public function removeRegistration(Registration $registration): static
    {
        if ($this->registrations->removeElement($registration)) {
            // set the owning side to null (unless already changed)
            if ($registration->getUser() === $this) {
                $registration->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): static
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setCreatedBy($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): static
    {
        if ($this->topics->removeElement($topic)) {
            // set the owning side to null (unless already changed)
            if ($topic->getCreatedBy() === $this) {
                $topic->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setCreatedBy($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCreatedBy() === $this) {
                $post->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VehicleReservation>
     */
    public function getVehicleReservations(): Collection
    {
        return $this->vehicleReservations;
    }

    public function addVehicleReservation(VehicleReservation $vehicleReservation): static
    {
        if (!$this->vehicleReservations->contains($vehicleReservation)) {
            $this->vehicleReservations->add($vehicleReservation);
            $vehicleReservation->setUser($this);
        }

        return $this;
    }

    public function removeVehicleReservation(VehicleReservation $vehicleReservation): static
    {
        if ($this->vehicleReservations->removeElement($vehicleReservation)) {
            // set the owning side to null (unless already changed)
            if ($vehicleReservation->getUser() === $this) {
                $vehicleReservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomReservation>
     */
    public function getRoomReservations(): Collection
    {
        return $this->roomReservations;
    }

    public function addRoomReservation(RoomReservation $roomReservation): static
    {
        if (!$this->roomReservations->contains($roomReservation)) {
            $this->roomReservations->add($roomReservation);
            $roomReservation->addAssignedTo($this);
        }

        return $this;
    }

    public function removeRoomReservation(RoomReservation $roomReservation): static
    {
        if ($this->roomReservations->removeElement($roomReservation)) {
            $roomReservation->removeAssignedTo($this);
        }

        return $this;
    }
}
