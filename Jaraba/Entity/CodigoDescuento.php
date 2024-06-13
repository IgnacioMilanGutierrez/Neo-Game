<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'codigo_descuento')]
class CodigoDescuento
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'IdCodigo')]
    private $idCodigo;

    #[ORM\Column(type:'string', name:'Codigo')]
    private $codigo;

    #[ORM\Column(type: 'integer', name: 'Descuento')]
    private $descuento;

    #[ORM\Column(type: 'datetime', name: 'FechaCaducidad')]
    private $fechaCaducidad;

    // Getters y Setters para cada propiedad

    public function getIdCodigo(): ?int
    {
        return $this->idCodigo;
    }

    public function getCodigo(): ?string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        $this->codigo = $codigo;

        return $this;
    }

    public function getDescuento(): ?int
    {
        return $this->descuento;
    }

    public function setDescuento(int $descuento): self
    {
        $this->descuento = $descuento;

        return $this;
    }

    public function getFechaCaducidad(): ?\DateTimeInterface
    {
        return $this->fechaCaducidad;
    }

    public function setFechaCaducidad(\DateTimeInterface $fechaCaducidad): self
    {
        $this->fechaCaducidad = $fechaCaducidad;

        return $this;
    }
}
