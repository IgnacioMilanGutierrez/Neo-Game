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
        } else {
            try {
                $email = (new Email())
                    ->from('tu_correo@gmail.com')
                    ->to($correo)
                    ->subject('Confirmación de compra en Neo-Game')
                    ->html($this->renderView(
                        'emails/confirmacion_compra.html.twig',
                        ['carrito' => $carrito, 'fecha' => new \DateTime()]
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


    #[Route('/pSaldo', name: 'realizar_pago_Saldo')]
    public function pagarCarrito(Request $request, MailerInterface $mailer,SessionInterface $session,EntityManagerInterface $em)
    {
        $usuario = $this->getUser();
        $carrito = $session->get('carrito', []);
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        $bonus = 0;
        if ($usuario->getSaldo() >= $total) {
            $usuario->setSaldo($usuario->getSaldo() - $total);
            $em->persist($usuario);
            $em->flush();

            try {
                $email = (new Email())
                    ->from('tu_correo@gmail.com')
                    ->to($usuario->getEmail())
                    ->subject('Confirmación de compra con saldo en Neo-Game')
                    ->html($this->renderView(
                        'emails/confirmacion_compra.html.twig',
                        ['carrito' => $carrito, 'fecha' => new \DateTime(), 'bonus' => $bonus]
                    ));
                $mailer->send($email);

                $this->addFlash('success', 'Compra realizada correctamente. Correo electrónico de confirmación enviado.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al enviar el correo electrónico: ' . $e->getMessage());
            }
            return $this->redirectToRoute('confirmacion_pago');
        } else {
            $this->addFlash('error', 'Saldo insuficiente para completar la compra.');
            return $this->redirectToRoute('carrito');
        }
    }
}
