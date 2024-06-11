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

class zonaAdmin extends AbstractController
{
    #[Route('/zona_admin', name: 'zonaAdmin')]
    public function zonaAdmin(){
        $usuario = $this->getUser();

        $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

        if ($usuarioAdmin) {
            return $this->render('zonaAdmin.html.twig');
        } else {
        return $this->redirectToRoute('zonaAdmin');
        }
    }
    
    #[Route("/admin/products", name:"admin_products")]
    public function products(EntityManagerInterface $em)
    {
        $products = $em->getRepository(Videojuego::class)->findAll();
        return $this->render('productosAdmin.html.twig', [
            'products' => $products,
        ]);
    }



}
