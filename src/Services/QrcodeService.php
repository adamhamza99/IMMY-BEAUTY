<?php

namespace App\Services;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Label\Margin\Margin;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Builder\BuilderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QrcodeService
{
    /**
     * @var BuilderInterface
     */
    protected $builder;

    public function __construct(BuilderInterface $builder)
    {
        $this->builder = $builder;
    }

    public function generateQrCode($user)
    {
        if ($user->getCard()) {
            return new JsonResponse(['message' => "Le user possède déjà une carte de fidélité."], Response::HTTP_BAD_REQUEST);
        }

        $url = 'http://localhost:8000/admin/carte/' . $user->getId();
        $objDateTime = new \DateTime('NOW');
        $dateString = $objDateTime->format('d-m-Y H:i:s');
        $path = dirname(__DIR__, 2).'/public/';

        // set qrcode
        $result = $this->builder
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->size(400)
            ->margin(10)
            ->logoPath($path.'images/logo.png')
            ->logoResizeToWidth('150')
            ->logoResizeToHeight('150')
            ->backgroundColor(new Color(255, 255, 255, 127))
            ->build()
        ;

        $uniqueId = substr(uniqid(), -4);  // prend les 4 derniers caractères de l'ID unique
        $namePng = $uniqueId . '-' . $user->getEmail() . '.png';

        //Save img png
        $imagePath = $path . 'qr-code/' . $namePng;

        $result->saveToFile($imagePath);

        return 'qr-code/' . $namePng;
    }
}