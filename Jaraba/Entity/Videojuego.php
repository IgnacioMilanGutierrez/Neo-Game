<?php

namespace App\Entity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'videojuego')]
class Videojuego
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'IdJuego')]
    private $idJuego;

    #[ORM\Column(type:'string', name:'NombreJuego')]
    private $nombreJuego;

    #[ORM\Column(type:'string', name:'Imagen')]
    private $imagen;

    #[ORM\ManyToMany(targetEntity:'Plataforma', mappedBy:'videojuego')]
    private $plataforma;

    #[ORM\Column(type:'float', name:'Precio')]
    private $precio;

    #[ORM\Column(type:'date', name:'FechaLanzamiento')]
    private $fechaLanzamiento;

    #[ORM\Column(type: 'integer', name: 'Stock')]
    private $stock;

    public function __construct()
    {
        $this->plataforma = new ArrayCollection();
        
    }

    public function getIdJuego(): ?int
    {
        return $this->idJuego;
    }

    public function getNombreJuego(): ?string
    {
        return $this->nombreJuego;
    }

    public function setNombreJuego(string $nombreJuego): self
    {
        $this->nombreJuego = $nombreJuego;

        return $this;
    }

    public function getImagen(): ?string
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): self
    {
        $this->imagen = $imagen;

        return $this;
    }

    public function getPlataformas(): Collection
    {
        return $this->plataforma;
    }

    public function addPlataforma(Plataforma $plataforma): self
    {
        if (!$this->plataforma->contains($plataforma)) {
            $this->plataforma[] = $plataforma;
        }

        return $this;
    }

    public function removePlataforma(Plataforma $plataforma): self
    {
        $this->plataforma->removeElement($plataforma);

        return $this;
    }

    public function getPrecio(): ?float
    {
        return $this->precio;
    }

    public function setPrecio(float $precio): self
    {
        $this->precio = $precio;

        return $this;
    }

    public function getFechaLanzamiento(): ?\DateTimeInterface
    {
        return $this->fechaLanzamiento;
    }

    public function setFechaLanzamiento(\DateTimeInterface $fechaLanzamiento): self
    {
        $this->fechaLanzamiento = $fechaLanzamiento;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }
}