<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'marca')]
class Marca
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'IdMarca')]
    private $idMarca;

    #[ORM\Column(type:'string', name:'NombreMarca')]
    private $nombreMarca;

    #[ORM\ManyToMany(targetEntity:"Plataforma", inversedBy:'marcas')]
    #[ORM\JoinColumn(name:"IdMarca", referencedColumnName:"IdPlataforma")]
    private $plataformas;

    // Constructor y otros métodos

    public function __construct()
    {
        $this->plataformas = new ArrayCollection();
    }

    // Getters y Setters

    public function getIdMarca(): ?int
    {
        return $this->idMarca;
    }

    public function getNombreMarca(): ?string
    {
        return $this->nombreMarca;
    }

    public function setNombreMarca(string $nombreMarca): self
    {
        $this->nombreMarca = $nombreMarca;

        return $this;
    }

    public function getPlataformas(): Collection
    {
        return $this->plataformas;
    }

    public function addPlataforma(Plataforma $plataforma): self
    {
        if (!$this->plataformas->contains($plataforma)) {
            $this->plataformas[] = $plataforma;
        }

        return $this;
    }

    public function removePlataforma(Plataforma $plataforma): self
    {
        $this->plataformas->removeElement($plataforma);

        return $this;
    }
}
