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
use Stripe\Stripe;
use Symfony\Bundle\SecurityBundle\Security;

class ConfigPerfil extends AbstractController
{

    #[Route('/cambiar-nombre', name: 'cambiar_nombre')]
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

    #[Route('/cambiar-apellidos', name: 'cambiar_apellidos')]
    public function cambiarApellidos(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevosApellidos = $request->request->get('nuevo_apellidos');

        if ($nuevosApellidos) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevoApellido($nuevosApellidos);

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
            $usuario->setApellidoUsuario($usuario->getNuevoApellido());
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

    #[Route('/cambiar-contrasena', name: 'cambiar_contrasena')]
    public function cambiarContrasena(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = $this->getUser();
        $nuevaContrasena = $request->request->get('nueva_contrasena');

        if ($nuevaContrasena) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevaContrasena($passwordHasher->hashPassword($usuario, $nuevaContrasena));

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
            $usuario->setContraseña($usuario->getNuevaContrasena());
            $usuario->setNuevaContrasena(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'La contraseña ha sido cambiada exitosamente.');
        } else {
            $this->addFlash('error', 'Token inválido o expirado.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/cambiar-correo', name: 'cambiar_correo')]
    public function cambiarEmail(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevoCorreo = $request->request->get('nuevo_correo');

        if ($nuevoCorreo) {
            $token = bin2hex(random_bytes(16));
            $usuario->setToken($token);
            $usuario->setNuevoCorreo($nuevoCorreo);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $email = (new Email())
                ->from('no_replyNeoGame@gmail.com')
                ->to($nuevoCorreo)
                ->subject('Confirmar Cambio de Correo Electrónico')
                ->html("<p>Para confirmar el cambio de correo electrónico, haz clic en el siguiente enlace:</p>
                        <p><a href='http://localhost:8000/perfil/confirmar-cambio-correo/{$token}'>Confirmar Cambio de Correo Electrónico</a></p>");

            $mailer->send($email);

            $this->addFlash('success', 'Se ha enviado un correo de confirmación para cambiar el correo electrónico.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/perfil/confirmar-cambio-correo/{token}', name: 'confirmar_cambio_correo')]
    public function confirmarCambioEmail(EntityManagerInterface $entityManager, $token): Response
    {
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['token' => $token]);

        if ($usuario) {
            $usuario->setEmail($usuario->getNuevoCorreo());
            $usuario->setNuevoCorreo(null);
            $usuario->setToken(null);

            $entityManager->persist($usuario);
            $entityManager->flush();

            $this->addFlash('success', 'El correo electrónico ha sido cambiado exitosamente.');
        } else {
            $this->addFlash('error', 'Token inválido o expirado.');
        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/cambiar-direccion', name: 'cambiar_direccion')]
    public function cambiarDireccion(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $nuevaDireccion = $request->request->get('nueva_direccion');

        if ($nuevaDireccion) {
            $usuario->setDireccion($nuevaDireccion);

            $entityManager->persist($usuario);
            $entityManager->flush();

        }

        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
    }

    #[Route('/borrar-cuenta', name: 'borrar_cuenta')]
    public function cambiarBorrarCuenta(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $token = bin2hex(random_bytes(16));
        $usuario->setToken($token);
        $entityManager->persist($usuario);
        $entityManager->flush();

        $email = (new Email())
            ->from('no_replyNeoGame@gmail.com')
            ->to($usuario->getEmail())
            ->subject('Confirmar Borrado de Cuenta')
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
    
    #[Route('/cambiar-foto', name: 'perfil_cambiar_foto')]
    public function cambiarFotoPerfil(Request $request, EntityManagerInterface $entityManager): Response
{
    $usuario = $this->getUser();
    $fotoPerfilFile = $request->files->get('foto_perfil');

    if($fotoPerfilFile instanceof UploadedFile){
        $nombreUsuario = $usuario->getNombreUsuario();
        $apellidoUsuario = $usuario->getApellidoUsuario(); 

        $rutaCarpeta = $this->getParameter('kernel.project_dir') . '/public/Usuarios/' . $nombreUsuario . $apellidoUsuario . '/';

            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0777, true);
            }

            $nombreArchivo = 'foto.jpg';
            $rutaFotoPerfil = '/Usuarios/' . $nombreUsuario . $apellidoUsuario . '/' . $nombreArchivo;

            if (file_exists($rutaFotoPerfil)) {
                unlink($rutaFotoPerfil);
            }

            // Mover el archivo al directorio de destino
            $fotoPerfilFile->move($rutaCarpeta, $nombreArchivo);

        $usuario->setFoto($rutaFotoPerfil);
        $entityManager->flush();
    }

    return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);

}
}
