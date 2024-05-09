<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Endroid\QrCode\QrCode;

class QrCodeController extends AbstractController
{
    #[Route('/generate-qr', name: 'generate_qr')]
    public function generateQrCode(Request $request)
    {
        $data = $request->query->get('data');

        // Création du code QR avec les données fournies
        $qrCode = new QrCode($data);

        // Configuration du code QR
        $qrCode->setSize(300);

        // Génération de la réponse avec le contenu du code QR
        $response = new Response($qrCode->writeString(), 200, [
            'Content-Type' => $qrCode->getContentType(),
        ]);

        return $response;
    }
}
