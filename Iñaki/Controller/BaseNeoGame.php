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
use Symfony\Component\HttpFoundation\File\UploadedFile;


class BaseNeoGame extends AbstractController
{
    #[Route('/inicio', name: 'inicio')]
        public function inicio(EntityManagerInterface $em): Response{
            $usuario = $this->getUser();

        $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

        if ($usuarioAdmin) {
            return $this->redirectToRoute('zonaAdmin');
        } else {

            $hoy = new \DateTime();

            #Juegos ya lanzados
            $queryLanzados = $em->createQuery(
                'SELECT v
                FROM App\Entity\Videojuego v
                WHERE v.fechaLanzamiento <= :hoy
                ORDER BY v.fechaLanzamiento DESC'
            )->setParameter('hoy', $hoy)
            ->setMaxResults(5);
    
            $juegosLanzados = $queryLanzados->getResult();

            #Futuros Lanzamientos
            $query = $em->createQuery(
                'SELECT v
                FROM App\Entity\Videojuego v
                WHERE v.fechaLanzamiento > :hoy'
            )->setParameter('hoy', $hoy);
    
            $juegos = $query->getResult();
    

            return $this->render('inicio.html.twig' ,['juegosLanzados' => $juegosLanzados, 'juegos' => $juegos,]);
            }
        }

        #[Route('/juego/{id}', name: 'juego_detalle')]
        public function juegoDetalle(EntityManagerInterface $em, $id): Response
        {
            $juego = $em->getRepository(Videojuego::class)->find($id);
    
            if (!$juego) {
                throw $this->createNotFoundException('El juego no existe');
            }
            return $this->render('juego_detalle.html.twig', [
                'juego' => $juego,
            ]);
        }

    #[Route('/perfil/{id}', name: 'perfil')]
        public function perfil(EntityManagerInterface $entityManager, $id): Response{

            $usuarioActual = $this->getUser();
            $usuarioPerfil = $entityManager->getRepository(Usuario::class)->find($id);


            return $this->render('perfil.html.twig', ['user' => $usuarioPerfil]);
        }
    
        #[Route('/producto/{id}', name: 'producto')]
        public function producto(EntityManagerInterface $entityManager, $id): Response{

            $productoActual = $this->getVideojuego();
            $productoDatos = $entityManager->getRepository(Usuario::class)->find($id);


            return $this->render('perfil.html.twig', ['user' => $usuarioPerfil]);
        }

    #[Route('/login', name: 'login')]
        public function login(){
            return $this->render('login.html.twig');
        }
    
    #[ROUTE('/logout', name:'app_logout')]
    public function logout(){
        $session->remove('carrito');
        return $this->render('inicio.html.twig');
    }
    
    #[Route('/procesar_login', name:'procesar_login')]
        public function process(Request $request, EntityManagerInterface $entityManager, AuthenticationUtils $authenticationUtils): Response
        {
            $lastUsername = $authenticationUtils->getLastUsername();

            $error = $authenticationUtils->getLastAuthenticationError();

            if (!$lastUsername){
                return $this->render('error_login.html.twig', ['error' => "Este usuario no existe"]);
            }

            if(!$error){
                $session->remove('carrito');
                return $this->redirectToRoute('inicio');
            } else {
                return $this->render('error_login.html.twig', ['error' => "Contraseña incorrecta"]);
            }
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
                <p>Para ello <a href='http://localhost:8000/confirmar_correo/{$nombre}/{$apellidos}/{$correo}/{$contraseña}/{$direccion}'>Haz click aquí</a>.</p>");

            $mailer->send($email);
        } else {
            return $this->render('registro.html.twig');
        }

        return $this->redirectToRoute('inicio');
    }

    #[Route('/confirmar_correo/{n}/{a}/{c}/{co}/{d}', name: 'confirmar_correo')]
    public function confirmar_correo(Request $request, EntityManagerInterface $entityManager, $n, $a, $c, $co, $d){
        $correoExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $c]);

        if(!$correoExistente){
            $admin=0;
            $nuevo=new Usuario();
            $nuevo->setNombreUsuario($n);
            $nuevo->setApellidoUsuario($a);
            $nuevo->setEmail($c);
            $nuevo->setContraseña(password_hash($co, PASSWORD_BCRYPT));
            $nuevo->setDireccion($d);
            $nuevo->setSaldo(0);
            $nuevo->setValoracion(0);
            $nuevo->setUsuarioAdmin(0);
            $nuevo->setExisteFoto(0);

            $entityManager->persist($nuevo);
            $entityManager->flush();

            $nombreCarpeta = $n . $a;
            $rutaCarpeta = __DIR__ . '/../../public/Usuarios/' . $nombreCarpeta;

            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta);
            }
            return $this->redirectToRoute('inicio');
        } else {
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

    #[Route('/carrito', name: 'carrito')]
    public function verCarrito(SessionInterface $session): Response
    {
        $carrito = $session->get('carrito', []);
        $total = array_reduce($carrito, function($carry, $item) {
            return $carry + $item['precio'] * $item['cantidad'];
        }, 0);

        $total = (float) $total;

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

        return $this->redirectToRoute('carrito');
    }

    #[Route('/carrito/vaciar', name: 'vaciar_carrito')]
    public function vaciarCarrito(SessionInterface $session): Response
    {
        $session->remove('carrito');

        return $this->redirectToRoute('carrito');
    }

    #[Route('/carrito/pagar', name: 'pagar_carrito')]
    public function pagarCarrito(SessionInterface $session): Response
    {
        $stripePublishableKey = $this->getParameter('stripe_publishable_key');
        $carrito = $session->get('carrito', []);
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return $this->render('pagar.html.twig', [
            'stripe_publishable_key' => $stripePublishableKey, 'total' => $total]);
    }

    #[Route('/novedades', name: 'novedades')]
    public function novedades(){
        $usuario = $this->getUser();

        $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

        if ($usuarioAdmin) {
            return $this->redirectToRoute('zonaAdmin');
        } else {
            return $this->render('inicio.html.twig');
        }
    }


    #[Route('/ofertas', name: 'ofertas')]
    public function ofertas(){
        $usuario = $this->getUser();

        $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

        if ($usuarioAdmin) {
            return $this->redirectToRoute('zonaAdmin');
        } else {
        return $this->render('inicio.html.twig');
        }
    }

    #[Route('/pago', name: 'realizar_pago')]
public function realizarPago(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, Security $security, MailerInterface $mailer): Response
{
    $token = $request->request->get('stripeToken');
    $nombre = $request->request->get('nombre');
    $correo = $request->request->get('correo');
    $direccion = $request->request->get('direccion');
    Stripe::setApiKey($this->getParameter('stripe_api_key'));

    $carrito = $session->get('carrito', []);
    $total = 0;
    foreach ($carrito as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }

    try {
        $user = $security->getUser();

        if ($user === null) {
            $customer = \Stripe\Customer::create([
                'name' => $nombre,
                'email' => $correo,
                'address' => [
                    'line1' => $direccion,
                ],
                'source' => $token,
            ]);

            \Stripe\Charge::create([
                'amount' => $total * 100,
                'currency' => 'eur',
                'customer' => $customer->id,
                'description' => 'Compra en Neo-Game',
                'metadata' => [
                    'nombre' => $nombre,
                    'correo' => $correo,
                    'direccion' => $direccion,
                ],
            ]);
        } else {
            // Obtener o crear el cliente en Stripe para el usuario
            $stripeCustomerId = $user->getStripeCustomerId();
            if (!$stripeCustomerId) {
                $customer = \Stripe\Customer::create([
                    'name' => $user->getNombreUsuario() . ' ' . $user->getApellidoUsuario(),
                    'email' => $user->getEmail(),
                    'address' => [
                        'line1' => $user->getDireccion(),
                    ],
                    'source' => $token,
                ]);
                $stripeCustomerId = $customer->id;
                // Asignar el ID de cliente de Stripe al usuario en la base de datos
                $user->setStripeCustomerId($stripeCustomerId);
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
                // Añadir la nueva fuente de pago al cliente si es necesario
                $customer->source = $token;
                $customer->save();
            }

            \Stripe\Charge::create([
                'amount' => $total * 100,
                'currency' => 'eur',
                'customer' => $stripeCustomerId,
                'description' => 'Compra en Neo-Game',
                'metadata' => [
                    'nombre' => $user->getNombreUsuario(),
                    'correo' => $user->getEmail(),
                    'direccion' => $user->getDireccion(),
                ],
            ]);
        }

        $bonus = $total * 0.05;
        if ($user !== null) {
            $user->setSaldo($user->getSaldo() + $bonus);
            $entityManager->persist($user);
            try {
                $email = (new Email())
                    ->from('tu_correo@gmail.com')
                    ->to($user->getEmail())
                    ->subject('Confirmación de compra en Neo-Game')
                    ->html($this->renderView(
                        'emails/confirmacion_compra.html.twig',
                        ['carrito' => $carrito, 'fecha' => new \DateTime(), 'bonus' => $bonus]
                    ));
        
                $mailer->send($email);
        
                $this->addFlash('success', 'Correo electrónico de confirmación enviado correctamente.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al enviar el correo electrónico: ' . $e->getMessage());
            }
        }

        foreach ($carrito as $id => $item) {
            $videojuego = $entityManager->getRepository(Videojuego::class)->find($id);

            if ($videojuego) {
                $nuevoStock = $videojuego->getStock() - $item['cantidad'];
                if ($nuevoStock < 0) {
                    $this->addFlash('error', 'Stock insuficiente para el videojuego: ' . $videojuego->getNombreJuego());
                    return $this->redirectToRoute('carrito');
                }
                $videojuego->setStock($nuevoStock);
                $entityManager->persist($videojuego);
            }
        }

        $entityManager->flush();
        $session->remove('carrito');
        return $this->redirectToRoute('confirmacion_pago');
    } catch (\Stripe\Exception\CardException $e) {
        $this->addFlash('error', $e->getMessage());
        return $this->redirectToRoute('carrito'); // Redireccionar al carrito si hay un error
    }
}


        #[Route('/confirmacion-pago', name: 'confirmacion_pago')]
    public function confirmacionPago(): Response
    {
        return $this->render('confirmacion_pago.html.twig');
    }
}
