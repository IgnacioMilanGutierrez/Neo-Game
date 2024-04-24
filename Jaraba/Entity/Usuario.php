<?php

namespace App\Entity;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'usuario')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer', name: 'IdUsuario')]
    private $idUsuario;

    #[ORM\Column(type:'string', name:'NombreUsuario')]
    private $nombreUsuario;

    #[ORM\Column(type:'string', name:'ApellidoUsuario')]
    private $apellidoUsuario;

    #[ORM\Column(type:'string', name:'Email', unique: true)]
    private $email;

    #[ORM\Column(type:'string', name:'Contraseña')]
    private $contraseña;

    #[ORM\Column(type:'string', name:'Direccion')]
    private $direccion;

    #[ORM\Column(type:'float', name:'Saldo')]
    private $saldo;

    #[ORM\Column(type:'integer', name:'Valoracion')]
    private $valoracion;

    #[ORM\Column(type:'boolean', name:'UsuarioAdmin')]
    private $admin;

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->nombreUsuario = $nombreUsuario;

        return $this;
    }

    public function getApellidoUsuario(): ?string
    {
        return $this->apellidoUsuario;
    }

    public function setApellidoUsuario(string $apellidoUsuario): self
    {
        $this->apellidoUsuario = $apellidoUsuario;

        return $this;
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

    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): self
    {
        $this->contraseña = $contraseña;

        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(string $direccion): self
    {
        $this->direccion = $direccion;

        return $this;
    }

    public function getSaldo(): ?float
    {
        return $this->saldo;
    }

    public function setSaldo(float $saldo): self
    {
        $this->saldo = $saldo;

        return $this;
    }

    public function getValoracion(): ?int
    {
        return $this->valoracion;
    }

    public function setValoracion(int $valoracion): self
    {
        $this->valoracion = $valoracion;

        return $this;
    }

    public function getUsuarioAdmin(): bool
    {
       return $this->admin;
    }

    public function setUsuarioAdmin(bool $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->admin ? ['ROLE_USER', 'ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->contraseña;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getSalt(): ?string
    {
        // No es necesario usar sal en contraseñas con bcrypt
        return null;
    }

    public function eraseCredentials(): void
    {
        // No es necesario hacer nada aquí, pero el método debe ser implementado
    }
}