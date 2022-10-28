<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Attributes as OA;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_user_detail",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups="getClients", excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 *
 *  * @Hateoas\Relation(
 *      "create",
 *      href = @Hateoas\Route(
 *          "app_create_user",
 *          parameters = { "id" = "expr(object.getId())" },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getClients", excludeIf = "expr(not is_granted('ROLE_ADMIN'))")
 * )
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers", "getClients", "detailUser"])]
    #[OA\Property(description: 'Id number of the user', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(["getUsers", "getClients", "createUser", "detailUser"])]
    #[OA\Property(description: 'Email address of the user', type: 'string', maxLength: 180, nullable: false)]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(["getUsers", "getClients", "createUser", "detailUser"])]
    #[OA\Property(description: 'User role', type: 'string', maxLength: 255, nullable: true)]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(["createUser"])]
    #[OA\Property(description: 'Encrypted password of the user', type: 'string', maxLength: 255, nullable: false)]
    private ?string $password = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[Groups(["createUser", "detailUser"])]
    #[OA\Property(description: 'Id number of the client')]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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
     * Méthode getUsername qui permet de retourner le champ qui est utilisé pour l'authentification.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }
}
