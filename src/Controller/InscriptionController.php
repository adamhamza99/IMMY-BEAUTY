<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Services\QrcodeService;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly QrcodeService $qrcodeService
    ) {

    }

    #[Route('/inscription', name: 'inscription')]
    public function index(): Response
    {
//        toastr()
//            ->closeButton(true)
//            ->addSuccess('Votre compte à bien été créer',' ');

        return $this->render('inscription/inscription.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

    #[Route(path: '/api/inscription', name: 'api_register', options: ["expose" => true], methods: ["POST"])]
    public function apiInscription(\Symfony\Component\HttpFoundation\Request $request, UserPasswordHasherInterface $passwordEncoder)
    {
        try {
            $data = [];
            parse_str($request->getContent(), $data);

            $requiredFields = ['email', 'password', 'confirm_password', 'prenom', 'nom', 'telephone'];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    // Envoyez une réponse JSON avec l'erreur
                    return new JsonResponse(['message' => "$field est requis."], Response::HTTP_BAD_REQUEST);
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['message' => "Format email invalide"], Response::HTTP_BAD_REQUEST);
            }

            // Check if the email already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
            if ($existingUser) {
                return new JsonResponse(['message' => "Cet email éxiste déjà."], Response::HTTP_CONFLICT);
            }

            // Check if passwords match
            if ($data['password'] !== $data['confirm_password']) {
                return new JsonResponse(['message' => "Les mot de passe ne sont pas identique."], Response::HTTP_BAD_REQUEST);
            }

            if ($data['password'] < 5) {
                return new JsonResponse(['message' => "Les mot de passe ne sont pas identique."], Response::HTTP_BAD_REQUEST);
            }

            // Create a new user entity
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPhone($data['telephone']);
            $user->setFirstname($data['prenom']);
            $user->setLastname($data['nom']);

            // Encode and set the password
            $encodedPassword = $passwordEncoder->hashPassword($user, $data['password']);
            $user->setPassword($encodedPassword);

            // Save the user to the database
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $QrCode = $this->qrcodeService->generateQrCode($user);
            $card = new Card();
            $card->setUser($user);
            $card->setQrCodeImage($QrCode);
            $card->setPoints('0');
            $this->entityManager->persist($card);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'User registered successfully.'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            dd($e);
            return new JsonResponse(['message' => 'An error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
