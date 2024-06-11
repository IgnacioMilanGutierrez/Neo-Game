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
    private $NombreUsuario;

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

    #[ORM\Column(type:'string', name:'Foto')]
    private $foto;

    #[ORM\Column(type: 'string', name: 'Token', nullable: true)]
    private $token;

    #[ORM\Column(type: 'string', name: 'NuevoNombre', nullable: true)]
    private $nuevoNombre;

    #[ORM\Column(type: 'string', name: 'NuevoApellido', nullable: true)]
    private $nuevoApellido;

    #[ORM\Column(type: 'string', name: 'NuevaContrasena', nullable: true)]
    private $nuevaContrasena;

    #[ORM\Column(type: 'string', name: 'NuevoCorreo', nullable: true)]
    private $nuevoCorreo;

    #[ORM\Column(type: 'string', name: 'StripeCustomerId')]
    private $stripeCustomerId;

    public function getIdUsuario(): ?int
    {
        return $this->idUsuario;
    }

    public function getNombreUsuario(): ?string
    {
        return $this->NombreUsuario;
    }

    public function setNombreUsuario(string $nombreUsuario): self
    {
        $this->NombreUsuario = $nombreUsuario;

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

    public function getFoto(): ?string
    {
       return $this->foto;
    }

    public function setFoto(string $foto): self
    {
        $this->foto = $foto;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getNuevoNombre(): ?string
    {
        return $this->nuevoNombre;
    }

    public function setNuevoNombre(?string $nuevoNombre): self
    {
        $this->nuevoNombre = $nuevoNombre;

        return $this;
    }

    public function getNuevoApellido(): ?string
    {
        return $this->nuevoApellido;
    }

    public function setNuevoApellido(?string $nuevoApellido): self
    {
        $this->nuevoApellido = $nuevoApellido;

        return $this;
    }

    public function getNuevaContrasena(): ?string
    {
        return $this->nuevaContrasena;
    }

    public function setNuevaContrasena(?string $nuevaContrasena): self
    {
        $this->nuevaContrasena = $nuevaContrasena;

        return $this;
    }

    
    public function getNuevoCorreo(): ?string
    {
        return $this->nuevoCorreo;
    }

    public function setNuevoCorreo(?string $nuevoCorreo): self
    {
        $this->nuevoCorreo = $nuevoCorreo;

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

    public function getStripeCustomerId()
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId($stripeCustomerId)
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }
}
