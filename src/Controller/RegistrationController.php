<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator): Response
    {
        $user = new User();
        $ip = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
        $user->setIp($ip);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $entityManager = $this->getDoctrine()->getManager();
                $userRepository = $this->getDoctrine()->getRepository(User::class);
                $testUser = $userRepository->findOneByPseudo($user->getPseudo());
                $testUser = (null === $testUser)
                    ? $userRepository->findOneByIp($user->getIp())
                    : $testUser;

                if (null !== $testUser) {
                    return $this->render('registration/register.html.twig', [
                        'registrationForm' => $form->createView(),
                        'error' => 'Pseudo ou IP déjà utilisé'
                    ]);
                }
                
                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $authenticator,
                    'main' // firewall name in security.yaml
                );
            }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'error' => null
        ]);
    }
}
