<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'plataforma')]
class Plataforma
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'IdPlataforma')]
    private $idPlataforma;

    #[ORM\Column(type:'string', name:'NombrePlataforma')]
    private $nombrePlataforma;

    #[ORM\ManyToMany(targetEntity:'Marca', mappedBy:'plataforma')]
    private $marcas;

    #[ORM\ManyToMany(targetEntity:"Videojuego", inversedBy:'plataformas')]
    #[ORM\JoinColumn(name:"IdPlataforma", referencedColumnName:"IdJuego")]
    private $videojuegos;

    public function __construct()
    {
        $this->marcas = new ArrayCollection();
        $this->videojuegos = new ArrayCollection();
    }

    public function getIdPlataforma(): ?int
    {
        return $this->idPlataforma;
    }

    public function getNombrePlataforma(): ?string
    {
        return $this->nombrePlataforma;
    }

    public function setNombrePlataforma(string $nombrePlataforma): self
    {
        $this->nombrePlataforma = $nombrePlataforma;

        return $this;
    }

    public function getMarcas(): Collection
    {
        return $this->marcas;
    }

    public function addMarca(Marca $marca): self
    {
        if (!$this->marcas->contains($marca)) {
            $this->marcas[] = $marca;
        }

        return $this;
    }

    public function removeMarca(Marca $marca): self
    {
        $this->marcas->removeElement($marca);

        return $this;
    }

    public function getVideojuegos(): Collection
    {
        return $this->videojuegos;
    }

    public function addVideojuego(Videojuego $videojuego): self
    {
        if (!$this->videojuegos->contains($videojuego)) {
            $this->videojuegos[] = $videojuego;
        }

        return $this;
    }

    public function removeVideojuego(Videojuego $videojuego): self
    {
        $this->videojuegos->removeElement($videojuego);

        return $this;
    }
}