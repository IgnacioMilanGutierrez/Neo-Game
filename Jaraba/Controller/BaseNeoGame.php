<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;
use App\Entity\Videojuego;
use App\Entity\CodigoDescuento;
use App\Entity\Plataforma;
use App\Entity\Marca;
use DateTime;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class BaseNeoGame extends AbstractController
{

    #[Route('/perfil/cambiar-foto', name: 'perfil_cambiar_foto')]
    public function fotoperfil(Request $request): Response
    {
        $usuario = $this->getUser(); 
        $existeFoto = $usuario->getExisteFoto();
        $fotoPerfil = $request->files->get('foto_perfil');
    
        if ($fotoPerfil) {
            $nombreUsuario = $usuario->getNombreUsuario();
            
            if ($existeFoto == false || $existeFoto == 0) {
                $rutaCarpeta = $this->getParameter('foto_perfil_directorio') . '/' . $nombreUsuario;
                if (!file_exists($rutaCarpeta)) {
                    mkdir($rutaCarpeta, 0777, true);
                }
                $usuario->setExisteFoto(true);
            } else {
                $rutaCarpeta = $this->getParameter('foto_perfil_directorio') . '/' . $nombreUsuario;
            }
    
            $nombreArchivo = uniqid() . '.' . $fotoPerfil->guessExtension();
    
            $fotoPerfil->move($rutaCarpeta, $nombreArchivo);
    
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($usuario);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('perfil'); // Redirigir de vuelta al perfil
    }

    #[Route('/inicio', name: 'inicio')]
    public function inicio(){
        return $this->render('inicio.html.twig');
    }

#[Route('/perfil/{id}', name: 'perfil')]
    public function perfil(EntityManagerInterface $entityManager, $id): Response{

        $usuarioActual = $this->getUser();
        $usuarioPerfil = $entityManager->getRepository(Usuario::class)->find($id);


        return $this->render('perfil.html.twig',['usuario' => $usuarioActual]);
    }

#[Route('/registro', name: 'registro')]
public function registro(MailerInterface $mailer, Request $request)
{
    if($request->isMethod('POST')){
        $nombre = $request->request->get('nombre');
        $apellidos = $request->request->get('apellidos');
        $correo = $request->request->get('correo');
        $contraseña = $request->request->get('contraseña');
        $direccion = $request->request->get('direccion');
        
        $email = (new Email())
            ->from('no_replyNeoGame@gmail.com')
            ->to($correo)
            ->subject('Correo de confirmación')
            ->html("<h2>Bievenido a Neo Game</h2>
            <p>Sólo te queda un paso para estar registrado en la web y es confirmar el registro</p>
            <p>Para ello <a href='http:://localhost:8000/confirmar_correo/{$nombre}/{$apellidos}/{$correo}/{$contraseña}/{$direccion}'Haz click aquí</a></p>");
    } else {
        return $this->render('registro.html.twig');
    }

    return $this->redirectToRoute('inicio');
}

#[Route('/confirmar_correo/{n}/{a}/{c}/{co}/{d}', name: 'confirmar_correo')]
public function confirmar_correo(Request $request, EntityManagerInterface $entityManager, $n, $a, $c, $co, $d){
    $correoExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['Email' => $c]);

    if(!$correoExistente){
        $admin=0;
        $nuevo=new Usuario();
        $nuevo->setNombreUsuario($n);
        $nuevo->setApellidoUsuario($a);
        $nuevo->setEmail($c);
        $nuevo->setContraseña($co, PASSWORD_BCRYPT);
        $nuevo->setDireccion($d);
        $nuevo->setSaldo(0);
        $nuevo->setValoracion(0);

        $entityManager->persist($nuevo);
        $entityManager->flush();

        return $this->redirectToRoute('inicio');
    } else {
        return $this->redirectToRoute('inicio');
    }
}


#[Route('/perfil/cambiar-nombre', name: 'cambiar_nombre')]
    public function cambiarNombre(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevoNombre = $request->request->get('nuevo_nombre');

        if ($nuevoNombre) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevoNombre($nuevoNombre);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $email = (new Email())
                ->from('no_replyNeoGame@gmail.com')
                ->to($usuario->getEmail())
                ->subject('Confirmar Cambio de Nombre de Usuario')
                ->html("<p>Para confirmar el cambio de nombre de usuario, haz clic en el siguiente enlace:</p>
                        <p><a href='http://localhost:8000/perfil/confirmar-cambio-nombre/{$token}'>Confirmar Cambio de Nombre</a></p>");

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado un correo de confirmación para cambiar el nombre de usuario.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-cambio-nombre/{token}', name: 'confirmar_cambio_nombre')]
    public function confirmarCambioNombre(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $usuario->setNombreUsuario($usuario->getNuevoNombre());
            $usuario->setNuevoNombre(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'El nombre de usuario ha sido cambiado exitosamente.');
        } else {
            $this->addFlash('error', 'Token inválido o expirado.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/cambiar-apellidos', name: 'cambiar_apellidos')]
    public function cambiarApellidos(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevosApellidos = $request->request->get('nuevo_apellidos');

        if ($nuevosApellidos) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevoApellido($nuevosApellidos);  // Necesitarás agregar este campo en tu entidad Usuario

            $entityManager->persist($usuario);
            $entityManager->flush();

            $email = (new Email())
                ->from('no_replyNeoGame@gmail.com')
                ->to($usuario->getEmail())
                ->subject('Confirmar Cambio de Apellidos de Usuario')
                ->html("<p>Para confirmar el cambio de apellidos de usuario, haz clic en el siguiente enlace:</p>
                        <p><a href='http://localhost:8000/perfil/confirmar-cambio-apellidos/{$token}'>Confirmar Cambio de Apellidos</a></p>");

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado un correo de confirmación para cambiar los apellidos de usuario.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-cambio-apellidos/{token}', name: 'confirmar_cambio_apellidos')]
    public function confirmarCambioApellidos(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $usuario->setApellidoUsuario($usuario->getNuevoApellido());  // Ajustar en la entidad Usuario
            $usuario->setNuevoApellido(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'Los apellidos de usuario han sido cambiados exitosamente.');
        } else {
            $this->addFlash('error', 'Token inválido o expirado.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }


    #[Route('/perfil/cambiar-contrasena', name: 'cambiar_contrasena')]
    public function cambiarContrasena(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = $this->getUser();
        $nuevaContrasena = $request->request->get('nueva_contrasena');

        if ($nuevaContrasena) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevaContrasena($passwordHasher->hashPassword($usuario, $nuevaContrasena));  // Necesitarás agregar este campo en tu entidad Usuario

            $entityManager->persist($usuario);
            $entityManager->flush();

            $email = (new Email())
                ->from('no_replyNeoGame@gmail.com')
                ->to($usuario->getEmail())
                ->subject('Confirmar Cambio de Contraseña')
                ->html("<p>Para confirmar el cambio de contraseña, haz clic en el siguiente enlace:</p>
                        <p><a href='http://localhost:8000/perfil/confirmar-cambio-contrasena/{$token}'>Confirmar Cambio de Contraseña</a></p>");

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado un correo de confirmación para cambiar la contraseña.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-cambio-contrasena/{token}', name: 'confirmar_cambio_contrasena')]
    public function confirmarCambioContrasena(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $usuario->setContraseña($usuario->getNuevaContrasena());  // Ajustar en la entidad Usuario
            $usuario->setNuevaContrasena(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

        }
        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/cambiar-correo', name: 'cambiar_correo')]
    public function cambiarEmail(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevosApellidos = $request->request->get('nuevo_correo');

        if ($nuevosApellidos) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setEmail($nuevoCorreo);  // Necesitarás agregar este campo en tu entidad Usuario

            $entityManager->persist($usuario);
            $entityManager->flush();

            $email = (new Email())
                ->from('no_replyNeoGame@gmail.com')
                ->to($usuario->getEmail())
                ->subject('Confirmar Cambio de Correo de Usuario')
                ->html("<p>Para confirmar el cambio de correo de usuario, haz clic en el siguiente enlace:</p>
                        <p><a href='http://localhost:8000/perfil/confirmar-cambio-correo/{$token}'>Confirmar Cambio de Correo</a></p>");

            $mailer->send($email);

        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-cambio-correo/{token}', name: 'confirmar_cambio_correo')]
    public function confirmarCambioCorreo(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $usuario->setcorreo($usuario->getNuevoCorreo());  // Ajustar en la entidad Usuario
            $usuario->setNuevoCorreo(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/cambiar-direccion', name: 'cambiar_direccion')]
    public function cambiarDireccion(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevaDireccion = $request->request->get('nueva_direccion');

        if ($nuevaDireccion) {
            $usuario->setDireccion($nuevaDireccion);  // Necesitarás agregar este campo en tu entidad Usuario

            $entityManager->persist($usuario);
            $entityManager->flush();

        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }


    #[Route('/perfil/borrar-cuenta', name: 'borrar_cuenta')]
    public function cambiarBorrarCuenta(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();

        $usuario->setToken($token);
        $entityManager->persist($usuario);
        $entityManager->flush();

        $email = (new Email())
            ->from('no_replyNeoGame@gmail.com')
            ->to($usuario->getEmail())
            ->subject('Confirmar Borro Cuenta')
            ->html("<p>Para confirmar el borro de cuenta, haz clic en el siguiente enlace:</p><br>
                    <p>(Una vez que se efectue el borrado perdera todo y sera irrecuperable)</p><br>
                    <p><a href='http://localhost:8000/perfil/confirmar-borro-cuenta/{$token}'>Confirmar Cambio de Correo</a></p>");

        $mailer->send($email);


        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-borro-cuenta/{token}', name: 'confirmar_borro_cuenta')]
    public function confirmarBorroCuenta(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $entityManager->remove($usuario);
            $entityManager->flush();

            return $this->redirectToRoute('inicio');
        } 
    }


    #[Route('/carrito', name: 'ver_carrito')]
    public function verCarrito(SessionInterface $session): Response
    {
        $carrito = $session->get('carrito', []);
        $total = array_reduce($carrito, function($carry, $item) {
            return $carry + $item['precio'] * $item['cantidad'];
        }, 0);

        return $this->render('carrito.html.twig', [
            'carrito' => $carrito,
            'total' => $total,
        ]);
    }

    #[Route('/carrito/agregar/{id}', name: 'agregar_carrito')]
    public function agregarAlCarrito(int $id, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $carrito = $session->get('carrito', []);

        $videojuego = $entityManager->getRepository(Videojuego::class)->find($id);

        if (!$videojuego) {
            throw $this->createNotFoundException('El videojuego no existe');
        }

        if (isset($carrito[$id])) {
            $carrito[$id]['cantidad']++;
        } else {
            $carrito[$id] = [
                'nombre' => $videojuego->getNombreJuego(),
                'precio' => $videojuego->getPrecio(),
                'cantidad' => 1,
            ];
        }

        $session->set('carrito', $carrito);

        return $this->redirectToRoute('ver_carrito');
    }

    #[Route('/carrito/eliminar/{id}', name: 'eliminar_carrito')]
    public function eliminarDelCarrito(int $id, SessionInterface $session): Response
    {
        $carrito = $session->get('carrito', []);

        if (isset($carrito[$id])) {
            unset($carrito[$id]);
            $session->set('carrito', $carrito);
        }

        return $this->redirectToRoute('ver_carrito');
    }

    #[Route('/carrito/vaciar', name: 'vaciar_carrito')]
    public function vaciarCarrito(SessionInterface $session): Response
    {
        $session->remove('carrito');

        return $this->redirectToRoute('ver_carrito');
    }

    #[Route('/carrito/pagar', name: 'pagar_carrito')]
    public function pagarCarrito(SessionInterface $session): Response
    {
        // Aquí puedes añadir la lógica para el proceso de pago
        // Por ahora, simplemente vaciamos el carrito y mostramos un mensaje de éxito

        $session->remove('carrito');

        return $this->render('pagar.html.twig', [
            'mensaje' => 'Pago realizado con éxito',
        ]);
    }
}
