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
}
