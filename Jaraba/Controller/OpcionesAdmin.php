<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OpcionesAdmin extends AbstractController
{
    #[Route("/subir_producto", name:"subir_producto")]
    public function subirProducto(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $nombre = $request->request->get('nombre');
        $imagen = $request->request->get('imagen');
        $plataforma = $request->request->get('plataforma');
        $precio = $request->request->get('precio');
        $fechaLanzamiento = new \DateTime($request->request->get('fechaLanzamiento'));
        $stock = $request->request->get('stock');

        // Crear una nueva instancia de Videojuego
        $videojuego = new Videojuego();
        $videojuego->setNombreJuego($nombre);
        $videojuego->setImagen($imagen);
        // Aquí deberías manejar la relación con la plataforma según tu lógica
        // $videojuego->setPlataformas($plataforma);
        $videojuego->setPrecio($precio);
        $videojuego->setFechaLanzamiento($fechaLanzamiento);
        $videojuego->setStock($stock);

        // Persistir en la base de datos
        $entityManager->persist($videojuego);
        $entityManager->flush();

        return new Response('Producto subido correctamente');
    }

    #[Route("/editar_producto", name:"editar_producto", methods:"POST")]
   public function editarProducto(Request $request): Response
   {
       $entityManager = $this->getDoctrine()->getManager();

       // Recoger datos del formulario
       $id = $request->request->get('id');
       $campo = $request->request->get('campo');
       $valor = $request->request->get('valor');

       // Obtener el videojuego a editar
       $videojuego = $this->getDoctrine()->getRepository(Videojuego::class)->find($id);

       // Verificar si el videojuego existe
       if (!$videojuego) {
           return new Response('No se encontró el videojuego con ID: '.$id);
       }

       // Actualizar el campo correspondiente
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

       // Persistir en la base de datos
       $entityManager->flush();

       return new Response('Producto editado correctamente');
   }

  #[Route("/borrar_producto", name:"borrar_producto", methods:"POST")]
  public function borrarProducto(Request $request): Response
   {
       $entityManager = $this->getDoctrine()->getManager();

       // Recoger datos del formulario
       $nombre = $request->request->get('nombre');
       $plataforma = $request->request->get('plataforma');

       // Buscar el videojuego a borrar
       $videojuego = $this->getDoctrine()->getRepository(Videojuego::class)->findOneBy([
           'nombreJuego' => $nombre,
           // Aquí deberías adaptar la búsqueda según tus necesidades
           // 'plataforma' => $plataforma,
       ]);

       // Verificar si el videojuego existe
       if (!$videojuego) {
           return new Response('No se encontró el videojuego con nombre: '.$nombre);
       }

       // Eliminar el videojuego de la base de datos
       $entityManager->remove($videojuego);
       $entityManager->flush();

       return new Response('Producto borrado correctamente');
   }

     #[Route("/subir_marca", name:"subir_marca", methods:"POST")]
    public function subirMarca(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $nombreMarca = $request->request->get('nombreMarca');

        // Crear una nueva instancia de Marca
        $marca = new Marca();
        $marca->setNombreMarca($nombreMarca);

        // Persistir en la base de datos
        $entityManager->persist($marca);
        $entityManager->flush();

        return new Response('Marca subida correctamente');
    }

#[Route("/editar_marca", name:"editar_marca", methods:"POST")]
    public function editarMarca(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idMarca = $request->request->get('idMarca');
        $nombreMarca = $request->request->get('nombreMarca');

        // Obtener la marca a editar
        $marca = $this->getDoctrine()->getRepository(Marca::class)->find($idMarca);

        // Verificar si la marca existe
        if (!$marca) {
            return new Response('No se encontró la marca con ID: '.$idMarca);
        }

        // Actualizar el nombre de la marca
        $marca->setNombreMarca($nombreMarca);

        // Persistir en la base de datos
        $entityManager->flush();

        return new Response('Marca editada correctamente');
    }

#[Route("/borrar_marca", name:"borrar_marca", methods:"POST")]
    public function borrarMarca(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $nombreMarca = $request->request->get('nombreMarca');

        // Buscar la marca a borrar
        $marca = $this->getDoctrine()->getRepository(Marca::class)->findOneBy([
            'nombreMarca' => $nombreMarca,
        ]);

        // Verificar si la marca existe
        if (!$marca) {
            return new Response('No se encontró la marca con nombre: '.$nombreMarca);
        }

        // Eliminar la marca de la base de datos
        $entityManager->remove($marca);
        $entityManager->flush();

        return new Response('Marca borrada correctamente');
    }

#[Route("/subir_plataforma", name:"subir_plataforma", methods:"POST")]
    public function subirPlataforma(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $nombrePlataforma = $request->request->get('nombrePlataforma');

        // Crear una nueva instancia de Plataforma
        $plataforma = new Plataforma();
        $plataforma->setNombrePlataforma($nombrePlataforma);

        // Persistir en la base de datos
        $entityManager->persist($plataforma);
        $entityManager->flush();

        return new Response('Plataforma subida correctamente');
    }

#[Route("/editar_plataforma", name:"editar_plataforma", methods:"POST")] 
    public function editarPlataforma(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idPlataforma = $request->request->get('idPlataforma');
        $nombrePlataforma = $request->request->get('nombrePlataforma');

        // Obtener la plataforma a editar
        $plataforma = $this->getDoctrine()->getRepository(Plataforma::class)->find($idPlataforma);

        // Verificar si la plataforma existe
        if (!$plataforma) {
            return new Response('No se encontró la plataforma con ID: '.$idPlataforma);
        }

        // Actualizar el nombre de la plataforma
        $plataforma->setNombrePlataforma($nombrePlataforma);

        // Persistir en la base de datos
        $entityManager->flush();

        return new Response('Plataforma editada correctamente');
    }

#[Route("/borrar_plataforma", name:"borrar_plataforma", methods:"POST")]
    public function borrarPlataforma(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $nombrePlataforma = $request->request->get('nombrePlataforma');

        // Buscar la plataforma a borrar
        $plataforma = $this->getDoctrine()->getRepository(Plataforma::class)->findOneBy([
            'nombrePlataforma' => $nombrePlataforma,
        ]);

        // Verificar si la plataforma existe
        if (!$plataforma) {
            return new Response('No se encontró la plataforma con nombre: '.$nombrePlataforma);
        }

        // Eliminar la plataforma de la base de datos
        $entityManager->remove($plataforma);
        $entityManager->flush();

        return new Response('Plataforma borrada correctamente');
    }

#[Route("/cambiar_datos_usuario", name:"cambiar_datos_usuario", methods:"POST")]
    public function cambiarDatosUsuario(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idUsuario = $request->request->get('idUsuario');
        $campoUsuario = $request->request->get('campoUsuario');
        $valorUsuario = $request->request->get('valorUsuario');

        // Obtener el usuario a editar
        $usuario = $this->getDoctrine()->getRepository(User::class)->find($idUsuario);

        // Verificar si el usuario existe
        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuario);
        }

        // Actualizar el campo del usuario
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
                // Asegúrate de manejar correctamente la contraseña
                $usuario->setPassword($valorUsuario);
                break;
            case 'direccion':
                $usuario->setDireccion($valorUsuario);
                break;
            default:
                return new Response('Campo de usuario no válido: '.$campoUsuario);
        }

        // Persistir en la base de datos
        $entityManager->flush();

        return new Response('Datos del usuario cambiados correctamente');
    }

#[Route("/borrar_cuenta", name:"borrar_cuenta", methods:"POST")]    
    public function borrarCuenta(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idUsuarioBorrar = $request->request->get('idUsuarioBorrar');

        // Buscar el usuario a borrar
        $usuario = $this->getDoctrine()->getRepository(User::class)->find($idUsuarioBorrar);

        // Verificar si el usuario existe
        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioBorrar);
        }

        // Eliminar el usuario de la base de datos
        $entityManager->remove($usuario);
        $entityManager->flush();

        return new Response('Cuenta de usuario borrada correctamente');
    }

#[Route("/anadir_saldo", name:"anadir_saldo", methods:"POST")]
    public function anadirSaldo(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idUsuarioSaldo = $request->request->get('idUsuarioSaldo');
        $montoSaldo = $request->request->get('montoSaldo');

        // Obtener el usuario
        $usuario = $this->getDoctrine()->getRepository(Usuario::class)->find($idUsuarioSaldo);

        // Verificar si el usuario existe
        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioSaldo);
        }

        // Añadir saldo al usuario
        $saldoActual = $usuario->getSaldo();
        $nuevoSaldo = $saldoActual + $montoSaldo;
        $usuario->setSaldo($nuevoSaldo);

        // Persistir en la base de datos
        $entityManager->flush();

        return new Response('Saldo añadido correctamente. Nuevo saldo: '.$nuevoSaldo);
    }

#[Route("/quitar_saldo", name:"quitar_saldo", methods:"POST")]
    public function quitarSaldo(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $idUsuarioQuitarSaldo = $request->request->get('idUsuarioQuitarSaldo');
        $montoQuitarSaldo = $request->request->get('montoQuitarSaldo');

        // Obtener el usuario
        $usuario = $this->getDoctrine()->getRepository(Usuario::class)->find($idUsuarioQuitarSaldo);

        // Verificar si el usuario existe
        if (!$usuario) {
            return new Response('No se encontró el usuario con ID: '.$idUsuarioQuitarSaldo);
        }

        // Verificar si el usuario tiene suficiente saldo
        $saldoActual = $usuario->getSaldo();
        if ($saldoActual < $montoQuitarSaldo) {
            return new Response('El usuario no tiene suficiente saldo para quitar');
        }

        // Quitar saldo al usuario
        $nuevoSaldo = $saldoActual - $montoQuitarSaldo;
        $usuario->setSaldo($nuevoSaldo);

        // Persistir en la base de datos
        $entityManager->flush();

        return new Response('Saldo quitado correctamente. Nuevo saldo: '.$nuevoSaldo);
    }

#[Route("/crear_codigo_descuento", name:"crear_codigo_descuento", methods:"POST")]
    public function crearCodigoDescuento(Request $request): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Recoger datos del formulario
        $codigo = $request->request->get('codigo');
        $descuento = $request->request->get('descuento');
        $fechaCaducidad = $request->request->get('fechaCaducidad');

        // Crear una nueva instancia de CodigoDescuento
        $codigoDescuento = new CodigoDescuento();
        $codigoDescuento->setCodigo($codigo);
        $codigoDescuento->setDescuento($descuento);
        $codigoDescuento->setFechaCaducidad(new \DateTime($fechaCaducidad));

        // Persistir en la base de datos
        $entityManager->persist($codigoDescuento);
        $entityManager->flush();

        return new Response('Código de descuento creado correctamente.');
    }

}
