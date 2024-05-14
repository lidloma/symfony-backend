<?php

namespace App\Entity;

use App\Repository\RecetaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: RecetaRepository::class)]
class Receta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $tiempo = null;

    #[ORM\Column(length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(length: 255)]
    private ?string $estado = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $fecha = null;

    #[ORM\ManyToOne(inversedBy: 'recetas')]
    private ?Usuario $usuario = null;

    /**
     * @var Collection<int, Ingrediente>
     */
    #[ORM\ManyToMany(targetEntity: Ingrediente::class, inversedBy: 'recetas')]
    private Collection $ingredientes;

    /**
     * @var Collection<int, Paso>
     */
    #[ORM\OneToMany(targetEntity: Paso::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $pasos;

    /**
     * @var Collection<int, Lista>
     */
    #[ORM\ManyToMany(targetEntity: Lista::class, inversedBy: 'recetas')]
    private Collection $listas;

    /**
     * @var Collection<int, Imagen>
     */
    #[ORM\OneToMany(targetEntity: Imagen::class, mappedBy: 'receta', orphanRemoval: true)]
    private Collection $imagenes;

    /**
     * @var Collection<int, Categoria>
     */
    #[ORM\ManyToMany(targetEntity: Categoria::class, inversedBy: 'recetas')]
    private Collection $categorias;

    /**
     * @var Collection<int, Comentario>
     */
    #[ORM\OneToMany(targetEntity: Comentario::class, mappedBy: 'receta')]
    private Collection $comentarios;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    public function __construct()
    {
        $this->ingredientes = new ArrayCollection();
        $this->pasos = new ArrayCollection();
        $this->listas = new ArrayCollection();
        $this->imagenes = new ArrayCollection();
        $this->categorias = new ArrayCollection();
        $this->comentarios = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTiempo(): ?int
    {
        return $this->tiempo;
    }

    public function setTiempo(int $tiempo): static
    {
        $this->tiempo = $tiempo;

        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): static
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->estado;
    }

    public function setEstado(string $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getFecha(): ?\DateTimeInterface
    {
        return $this->fecha;
    }

    public function setFecha(\DateTimeInterface $fecha): static
    {
        $this->fecha = $fecha;

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

    /**
     * @return Collection<int, Ingrediente>
     */
    public function getIngredientes(): Collection
    {
        return $this->ingredientes;
    }

    public function addIngrediente(Ingrediente $ingrediente): static
    {
        if (!$this->ingredientes->contains($ingrediente)) {
            $this->ingredientes->add($ingrediente);
        }

        return $this;
    }

    public function removeIngrediente(Ingrediente $ingrediente): static
    {
        $this->ingredientes->removeElement($ingrediente);

        return $this;
    }

    /**
     * @return Collection<int, Paso>
     */
    public function getPasos(): Collection
    {
        return $this->pasos;
    }

    public function addPaso(Paso $paso): static
    {
        if (!$this->pasos->contains($paso)) {
            $this->pasos->add($paso);
            $paso->setReceta($this);
        }

        return $this;
    }

    public function removePaso(Paso $paso): static
    {
        if ($this->pasos->removeElement($paso)) {
            // set the owning side to null (unless already changed)
            if ($paso->getReceta() === $this) {
                $paso->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Lista>
     */
    public function getListas(): Collection
    {
        return $this->listas;
    }

    public function addLista(Lista $lista): static
    {
        if (!$this->listas->contains($lista)) {
            $this->listas->add($lista);
        }

        return $this;
    }

    public function removeLista(Lista $lista): static
    {
        $this->listas->removeElement($lista);

        return $this;
    }

    /**
     * @return Collection<int, Imagen>
     */
    public function getImagenes(): Collection
    {
        return $this->imagenes;
    }

    public function addImagene(Imagen $imagene): static
    {
        if (!$this->imagenes->contains($imagene)) {
            $this->imagenes->add($imagene);
            $imagene->setReceta($this);
        }

        return $this;
    }

    public function removeImagene(Imagen $imagene): static
    {
        if ($this->imagenes->removeElement($imagene)) {
            // set the owning side to null (unless already changed)
            if ($imagene->getReceta() === $this) {
                $imagene->setReceta(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categoria>
     */
    public function getCategorias(): Collection
    {
        return $this->categorias;
    }

    public function addCategoria(Categoria $categoria): static
    {
        if (!$this->categorias->contains($categoria)) {
            $this->categorias->add($categoria);
        }

        return $this;
    }

    public function removeCategoria(Categoria $categoria): static
    {
        $this->categorias->removeElement($categoria);

        return $this;
    }

    /**
     * @return Collection<int, Comentario>
     */
    public function getComentarios(): Collection
    {
        return $this->comentarios;
    }

    public function addComentario(Comentario $comentario): static
    {
        if (!$this->comentarios->contains($comentario)) {
            $this->comentarios->add($comentario);
            $comentario->setReceta($this);
        }

        return $this;
    }

    public function removeComentario(Comentario $comentario): static
    {
        if ($this->comentarios->removeElement($comentario)) {
            // set the owning side to null (unless already changed)
            if ($comentario->getReceta() === $this) {
                $comentario->setReceta(null);
            }
        }

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }
}
