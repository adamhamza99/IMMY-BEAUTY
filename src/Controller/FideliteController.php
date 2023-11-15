<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FideliteController extends AbstractController
{
    #[Route('/fidelite', name: 'app_fidelite')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('fidelite/index.html.twig', [
            'user' => $user,
        ]);
    }
}
