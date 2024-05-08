<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
