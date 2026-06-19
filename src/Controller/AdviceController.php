<?php

namespace App\Controller;

use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class AdviceController extends AbstractController
{
    #[Route('/api/conseil', name: 'currentAdvice', methods: ['GET'])]
    public function getCurrentAdvice(AdviceRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $currentMonth = date('n');
        $adviceList = $repo->findByMonth($currentMonth);
        $jsonAdviceList = $serializer->serialize($adviceList, 'json');
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/conseil/{mois}', name: 'monthAdvice', methods: ['GET'])]
    public function getMonthAdvice(int $mois, AdviceRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $adviceList = $repo->findByMonth($mois);
        $jsonAdviceList = $serializer->serialize($adviceList, 'json');
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }
}
