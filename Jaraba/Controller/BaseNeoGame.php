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
use App\Repository\CodigoDescuentoRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BaseNeoGame extends AbstractController
{
   #[Route('/inicio', name: 'inicio')]
public function inicio(EntityManagerInterface $em): Response {
    $usuario = $this->getUser();

    $usuarioAdmin = $usuario && $this->isGranted('ROLE_ADMIN');

    if ($usuarioAdmin) {
        return $this->redirectToRoute('zonaAdmin');
    } else {
        $hoy = new \DateTime();

        // Juegos ya lanzados
        $queryLanzados = $em->createQuery(
            'SELECT v
            FROM App\Entity\Videojuego v
            WHERE v.fechaLanzamiento <= :hoy
            ORDER BY v.fechaLanzamiento DESC'
        )->setParameter('hoy', $hoy)
        ->setMaxResults(5);

        $juegosLanzados = $queryLanzados->getResult();

        // Futuros Lanzamientos
        $query = $em->createQuery(
            'SELECT v
            FROM App\Entity\Videojuego v
            WHERE v.fechaLanzamiento > :hoy'
        )->setParameter('hoy', $hoy)
        ->setMaxResults(5);

        $juegos = $query->getResult();

        // Todos los Juegos
        $todosLosJuegos = $em->getRepository(Videojuego::class)->findAll();

        return $this->render('inicio.html.twig', [
            'juegosLanzados' => $juegosLanzados,
            'juegos' => $juegos,
            'todosLosJuegos' => $todosLosJuegos
        ]);
    }
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

            return $this->redirectToRoute('inicio');
        } else {
            return $this->redirectToRoute('inicio');
        }
    }

    #[Route('/perfil/cambiar-foto', name: 'perfil_cambiar_foto')]
    public function cambiarFotoPerfil(Request $request, EntityManagerInterface $entityManager): Response
    {
        $usuario = $this->getUser();
        $fotoPerfilFile = $request->files->get('foto_perfil');
    
        if ($fotoPerfilFile instanceof UploadedFile) {
            $nombreUsuario = $usuario->getNombreUsuario();
            $rutaCarpeta = $this->getParameter('directorio_fotos_perfil') . '/' . $nombreUsuario;
    
            if (!file_exists($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0777, true);
            }
    
            $nombreArchivo = uniqid() . '.' . $fotoPerfilFile->guessExtension();
            $fotoPerfilFile->move($rutaCarpeta, $nombreArchivo);
    
            $rutaFotoPerfil = $rutaCarpeta . '/' . $nombreArchivo;
    
            $usuario->setFotoPerfil($rutaFotoPerfil);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('perfil', ['id' => $usuario->getIdUsuario()]);
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

    #[Route('/perfil/cambiar-contrasena', name: 'cambiar_contrasena')]
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

    #[Route('/perfil/cambiar-correo', name: 'cambiar_correo')]
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

    private $codigoDescuentoRepository;

    public function __construct(CodigoDescuentoRepository $codigoDescuentoRepository)
    {
        $this->codigoDescuentoRepository = $codigoDescuentoRepository;
    }

    #[Route('/carrito/aplicar-descuento', name: 'aplicar_descuento', methods: ['POST'])]
    public function aplicarDescuento(Request $request, SessionInterface $session): RedirectResponse
    {
        $codigoDescuento = $request->request->get('codigo_descuento');
        $descuento = $this->validarCodigoDescuento($codigoDescuento);

        if ($descuento !== false) {
            $session->set('descuento', $descuento);
            $this->addFlash('success', 'Código de descuento aplicado correctamente.');
        } else {
            $this->addFlash('error', 'Código de descuento no válido o caducado.');
        }

        return $this->redirectToRoute('carrito');
    }

    private function validarCodigoDescuento(string $codigo): ?int
    {
        $codigoDescuento = $this->codigoDescuentoRepository->findCodigoDescuento($codigo);

        if ($codigoDescuento) {
            $descuento = $codigoDescuento->getDescuento();
            if ($descuento > 0 && $descuento <= 100) {
                return $descuento;
            }
        }

        return false;
    }

    #[Route('/carrito', name: 'carrito')]
    public function verCarrito(SessionInterface $session): Response
    {
        $carrito = $session->get('carrito', []);
        $total = array_reduce($carrito, function($carry, $item) {
            return $carry + $item['precio'] * $item['cantidad'];
        }, 0);

        $descuento = $session->get('descuento', 0);
        $totalConDescuento = $total - ($total * $descuento / 100);
        $totalAhorro = $total * $descuento / 100;

        return $this->render('carrito.html.twig', [
            'carrito' => $carrito,
            'total' => $total,
            'descuento' => $descuento,
            'totalConDescuento' => $totalConDescuento,
            'totalAhorro' => $totalAhorro,
        ]);
    }

    #[Route('/carrito/pagar', name: 'pagar_carrito')]
    public function pagarCarrito(SessionInterface $session): Response
    {
        $stripePublishableKey = $this->getParameter('stripe_publishable_key');
        $carrito = $session->get('carrito', []);
        $total = array_reduce($carrito, function($carry, $item) {
            return $carry + $item['precio'] * $item['cantidad'];
        }, 0);

        $descuento = $session->get('descuento', 0);
        $totalConDescuento = $total - ($total * $descuento / 100);

        return $this->render('pagar.html.twig', [
            'carrito' => $carrito,
            'total' => $total,
            'descuento' => $descuento,
            'totalConDescuento' => $totalConDescuento,
            'stripe_publishable_key' => $stripePublishableKey,
        ]);
    }
}
    #[Route('/novedades', name: 'novedades')]
    public function novedades(){
        return $this->render('inicio.html.twig');
    }

    #[Route('/ofertas', name: 'ofertas')]
    public function ofertas(){
        return $this->render('inicio.html.twig');
    }

    #[Route('/zona_admin', name: 'zona_admin')]
    public function zonaAdmin(){
        return $this->render('zonaAdmin.html.twig');
    }
}
