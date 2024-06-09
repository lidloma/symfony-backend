<?php

namespace App\Entity;

use App\Repository\ComentarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ComentarioRepository::class)]
class Comentario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column(nullable: true)]
    private ?int $puntuacion = null;

    #[ORM\Column(nullable: true)]
    private ?string $complejidad = null;

    #[ORM\ManyToOne(inversedBy: 'comentarios')]
    private ?Usuario $usuario = null;

    #[ORM\ManyToOne(inversedBy: 'comentarios')]
    private ?Receta $receta = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'comentarios')]
    private ?self $comentario = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'comentario')]
    private Collection $comentarios;

    public function __construct()
    {
        $this->comentarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getPuntuacion(): ?int
    {
        return $this->puntuacion;
    }

    public function setPuntuacion(?int $puntuacion): ?static
    {
        $this->puntuacion = $puntuacion;

        return $this;
    }

    public function getComplejidad(): ?string
    {
        return $this->complejidad;
    }

    public function setComplejidad(?string $complejidad): static
    {
        $this->complejidad = $complejidad;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function getReceta(): ?Receta
    {
        return $this->receta;
    }

    public function setReceta(?Receta $receta): static
    {
        $this->receta = $receta;

        return $this;
    }

    public function getComentario(): ?self
    {
        return $this->comentario;
    }

    public function setComentario(?self $comentario): static
    {
        $this->comentario = $comentario;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getComentarios(): Collection
    {
        return $this->comentarios;
    }

    public function addComentario(self $comentario): static
    {
        if (!$this->comentarios->contains($comentario)) {
            $this->comentarios->add($comentario);
            $comentario->setComentario($this);
        }

        return $this;
    }

    public function removeComentario(self $comentario): static
    {
        if ($this->comentarios->removeElement($comentario)) {
            // set the owning side to null (unless already changed)
            if ($comentario->getComentario() === $this) {
                $comentario->setComentario(null);
            }
        }

        return $this;
    }
}
