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


class PlataformaPago extends AbstractController
{

   #[Route('/pago', name: 'realizar_pago')]
public function realizarPago(Request $request, SessionInterface $session, EntityManagerInterface $entityManager, Security $security, MailerInterface $mailer): Response
{
    $token = $request->request->get('stripeToken');
    $nombre = $request->request->get('nombre');
    $correo = $request->request->get('correo');
    $direccion = $request->request->get('direccion');
    Stripe::setApiKey($this->getParameter('stripe_api_key'));

    $carrito = $session->get('carrito', []);
    $total = array_reduce($carrito, function($carry, $item) {
        return $carry + $item['precio'] * $item['cantidad'];
    }, 0);

    $descuento = $session->get('descuento', 0);
    $totalConDescuento = $total - ($total * $descuento / 100);

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
                'amount' => $totalConDescuento * 100,
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
                $user->setStripeCustomerId($stripeCustomerId);
                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                $customer = \Stripe\Customer::retrieve($stripeCustomerId);
                $customer->source = $token;
                $customer->save();
            }

            \Stripe\Charge::create([
                'amount' => $totalConDescuento * 100,
                'currency' => 'eur',
                'customer' => $customer->id,
                'description' => 'Compra en Neo-Game',
                'metadata' => [
                    'nombre' => $user->getNombreUsuario() . ' ' . $user->getApellidoUsuario(),
                    'correo' => $user->getEmail(),
                    'direccion' => $user->getDireccion(),
                ],
            ]);
        }

        $pedido = new Pedido();
        $pedido->setNombre($nombre);
        $pedido->setCorreo($correo);
        $pedido->setDireccion($direccion);
        $pedido->setFecha(new \DateTime());
        $pedido->setTotal($totalConDescuento);

        foreach ($carrito as $id => $item) {
            $producto = $entityManager->getRepository(Productos::class)->find($id);
            if (!$producto) {
                continue;
            }

            $pedidoProducto = new PedidoProducto();
            $pedidoProducto->setProducto($producto);
            $pedidoProducto->setCantidad($item['cantidad']);
            $pedidoProducto->setPrecio($item['precio']);
            $pedidoProducto->setPedido($pedido);

            $entityManager->persist($pedidoProducto);
        }

        $entityManager->persist($pedido);
        $entityManager->flush();

        $this->enviarCorreoConfirmacion($nombre, $correo, $pedido, $carrito, $totalConDescuento, $mailer);

        $session->remove('carrito');
        $session->remove('descuento');

        return $this->redirectToRoute('confirmacion_pago');
    } catch (\Exception $e) {
        $this->addFlash('error', 'Hubo un error al procesar el pago: ' . $e->getMessage());
        return $this->redirectToRoute('carrito');
    }
}

        #[Route('/confirmacion-pago', name: 'confirmacion_pago')]
    public function confirmacionPago(): Response
    {
        return $this->render('confirmacion_pago.html.twig');
    }

}
