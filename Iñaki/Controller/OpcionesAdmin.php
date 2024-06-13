<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;
use App\Entity\Marca;
use App\Entity\Plataforma;
use App\Entity\Videojuego;
use App\Entity\CodigoDescuento;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpcionesAdmin extends AbstractController
{
    
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/zona_admin', name: 'zonaAdmin')]
    public function zonaAdmin(){
        $usuario = $this->getUser();

        $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

        if ($usuarioAdmin) {
            return $this->render('zonaAdmin.html.twig');
        } else {
        return $this->redirectToRoute('inicio');
        }
    }
    
    #[Route("/subir_producto", name:"subir_producto")]
    public function subirProducto(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $nombre = $request->request->get('nombre');
        $plataforma = $request->request->get('plataforma');
        $precio = $request->request->get('precio');
        $fechaLanzamiento = new \DateTime($request->request->get('fechaLanzamiento'));
        $stock = $request->request->get('stock');


        $videojuego = new Videojuego();
        $videojuego->setNombreJuego($nombre);
        $videojuego->setPrecio($precio);
        $videojuego->setFechaLanzamiento($fechaLanzamiento);
        $videojuego->setStock($stock);

        $entityManager->persist($videojuego);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

    #[Route("/editar_producto", name:"editar_producto", methods:"POST")]
   public function editarProducto(Request $request): Response
   {
       $entityManager = $this->doctrine->getManager();

       $id = $request->request->get('id');
       $campo = $request->request->get('campo');
       $valor = $request->request->get('valor');

       $videojuego = $this->doctrine->getRepository(Videojuego::class)->find($id);

       if (!$videojuego) {
           return new Response('No se encontró el videojuego con ID: '.$id);
       }

       switch ($campo) {
           case 'nombreJuego':
               $videojuego->setNombreJuego($valor);
               break;
           case 'imagen':
               $videojuego->setImagen($valor);
               break;
           case 'precio':
               $videojuego->setPrecio($valor);
               break;
           case 'fechaLanzamiento':
               $fechaLanzamiento = new \DateTime($valor);
               $videojuego->setFechaLanzamiento($fechaLanzamiento);
               break;
           case 'stock':
               $videojuego->setStock($valor);
               break;
           default:
               return new Response('Campo no válido para editar');
       }

       $entityManager->flush();

       return $this->redirectToRoute('zonaAdmin');
   }

  #[Route("/borrar_producto", name:"borrar_producto", methods:"POST")]
  public function borrarProducto(Request $request): Response
   {
       $entityManager = $this->doctrine->getManager();

       $nombre = $request->request->get('nombre');
       $plataforma = $request->request->get('plataforma');

       $videojuego = $this->doctrine->getRepository(Videojuego::class)->findOneBy([
           'nombreJuego' => $nombre,

       ]);

       if (!$videojuego) {
           return new Response('No se encontró el videojuego con nombre: '.$nombre);
       }

       $entityManager->remove($videojuego);
       $entityManager->flush();

       return $this->redirectToRoute('zonaAdmin');
   }

     #[Route("/subir_marca", name:"subir_marca", methods:"POST")]
    public function subirMarca(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $nombreMarca = $request->request->get('nombreMarca');

        $marca = new Marca();
        $marca->setNombreMarca($nombreMarca);

        $entityManager->persist($marca);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/editar_marca", name:"editar_marca", methods:"POST")]
    public function editarMarca(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idMarca = $request->request->get('idMarca');
        $nombreMarca = $request->request->get('nombreMarca');

        $marca = $this->doctrine->getRepository(Marca::class)->find($idMarca);

        if (!$marca) {
            return new Response('No se encontró la marca con ID: '.$idMarca);
        }

        $marca->setNombreMarca($nombreMarca);

        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/borrar_marca", name:"borrar_marca", methods:"POST")]
    public function borrarMarca(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $nombreMarca = $request->request->get('nombreMarca');

        $marca = $this->doctrine->getRepository(Marca::class)->findOneBy([
            'nombreMarca' => $nombreMarca,
        ]);

        if (!$marca) {
            return new Response('No se encontró la marca con nombre: '.$nombreMarca);
        }

        $entityManager->remove($marca);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/subir_plataforma", name:"subir_plataforma", methods:"POST")]
    public function subirPlataforma(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $nombrePlataforma = $request->request->get('nombrePlataforma');

        $plataforma = new Plataforma();
        $plataforma->setNombrePlataforma($nombrePlataforma);

        $entityManager->persist($plataforma);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/editar_plataforma", name:"editar_plataforma", methods:"POST")] 
    public function editarPlataforma(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idPlataforma = $request->request->get('idPlataforma');
        $nombrePlataforma = $request->request->get('nombrePlataforma');

        $plataforma = $this->doctrine->getRepository(Plataforma::class)->find($idPlataforma);

        if (!$plataforma) {
            return new Response('No se encontró la plataforma con ID: '.$idPlataforma);
        }

        $plataforma->setNombrePlataforma($nombrePlataforma);

        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/borrar_plataforma", name:"borrar_plataforma", methods:"POST")]
    public function borrarPlataforma(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $nombrePlataforma = $request->request->get('nombrePlataforma');

        $plataforma = $this->doctrine->getRepository(Plataforma::class)->findOneBy([
            'nombrePlataforma' => $nombrePlataforma,
        ]);

        if (!$plataforma) {
            return new Response('No se encontró la plataforma con nombre: '.$nombrePlataforma);
        }

        $entityManager->remove($plataforma);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/cambiar_datos_usuario", name:"cambiar_datos_usuario", methods:"POST")]
    public function cambiarDatosUsuario(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idUsuario = $request->request->get('idUsuario');
        $campoUsuario = $request->request->get('campoUsuario');
        $valorUsuario = $request->request->get('valorUsuario');

        $usuario = $this->doctrine->getRepository(Usuario::class)->find($idUsuario);

        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuario);
        }

        switch ($campoUsuario) {
            case 'nombreUsuario':
                $usuario->setNombreUsuario($valorUsuario);
                break;
            case 'apellidoUsuario':
                $usuario->setApellidoUsuario($valorUsuario);
                break;
            case 'email':
                $usuario->setEmail($valorUsuario);
                break;
            case 'contraseña':
                $usuario->setPassword($valorUsuario);
                break;
            case 'direccion':
                $usuario->setDireccion($valorUsuario);
                break;
            default:
                return new Response('Campo de usuario no válido: '.$campoUsuario);
        }

        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/borrar_cuenta", name:"borrar_cuenta", methods:"POST")]    
    public function borrarCuenta(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idUsuarioBorrar = $request->request->get('idUsuarioBorrar');

        $usuario = $this->doctrine->getRepository(Usuario::class)->find($idUsuarioBorrar);

        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioBorrar);
        }

        $entityManager->remove($usuario);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/anadir_saldo", name:"anadir_saldo", methods:"POST")]
    public function anadirSaldo(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idUsuarioSaldo = $request->request->get('idUsuarioSaldo');
        $montoSaldo = $request->request->get('montoSaldo');

        $usuario = $this->doctrine->getRepository(Usuario::class)->find($idUsuarioSaldo);

        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioSaldo);
        }

        $saldoActual = $usuario->getSaldo();
        $nuevoSaldo = $saldoActual + $montoSaldo;
        $usuario->setSaldo($nuevoSaldo);

        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/quitar_saldo", name:"quitar_saldo", methods:"POST")]
    public function quitarSaldo(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $idUsuarioQuitarSaldo = $request->request->get('idUsuarioQuitarSaldo');
        $montoQuitarSaldo = $request->request->get('montoQuitarSaldo');

        $usuario = $this->doctrine->getRepository(Usuario::class)->find($idUsuarioQuitarSaldo);

        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioQuitarSaldo);
        }

        $saldoActual = $usuario->getSaldo();
        if ($saldoActual < $montoQuitarSaldo) {
            return new Response('El usuario no tiene suficiente saldo para quitar');
        }

        $nuevoSaldo = $saldoActual - $montoQuitarSaldo;
        $usuario->setSaldo($nuevoSaldo);

        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }

#[Route("/crear_codigo_descuento", name:"crear_codigo_descuento", methods:"POST")]
    public function crearCodigoDescuento(Request $request): Response
    {
        $entityManager = $this->doctrine->getManager();

        $codigo = $request->request->get('codigo');
        $descuento = $request->request->get('descuento');
        $fechaCaducidad = $request->request->get('fechaCaducidad');

        $codigoDescuento = new CodigoDescuento();
        $codigoDescuento->setCodigo($codigo);
        $codigoDescuento->setDescuento($descuento);
        $codigoDescuento->setFechaCaducidad(new \DateTime($fechaCaducidad));

        $entityManager->persist($codigoDescuento);
        $entityManager->flush();

        return $this->redirectToRoute('zonaAdmin');
    }
}
