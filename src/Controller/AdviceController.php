<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use App\Repository\MonthRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class AdviceController extends AbstractController
{
    #[Route('/api/conseil', name: 'currentAdvice', methods: ['GET'])]
    public function getCurrentAdvice(AdviceRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $currentMonth = date('n');
        $adviceList = $repo->findByMonth($currentMonth);
        $jsonAdviceList = $serializer->serialize($adviceList, 'json', ['groups' => 'adviceList']);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/conseil/{mois}', name: 'monthAdvice', methods: ['GET'])]
    public function getMonthAdvice(int $mois, AdviceRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $adviceList = $repo->findByMonth($mois);
        $jsonAdviceList = $serializer->serialize($adviceList, 'json', ['groups' => 'adviceList']);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/conseil', name: 'createAdvice', methods: ['POST'])]
    public function createAdvice(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, MonthRepository $monthRepo, ValidatorInterface $validator): JsonResponse
    {
        $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['months']]);

        $errors = $validator->validate($advice);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $monthsIds = $content['months'] ?? [];
        foreach ($monthsIds as $monthId) {
            $month = $monthRepo->find($monthId);
            if ($month) {
                $advice->addMonth($month);
            }
        }

        $em->persist($advice);
        $em->flush();
        $jsonAdvice = $serializer->serialize($advice, 'json', ['groups' => 'adviceList']);

        return new JsonResponse($jsonAdvice, Response::HTTP_CREATED, [], true);
    }

    #[Route('/api/conseil/{id}', name: 'editAdvice', methods: ['PUT'])]
    public function editAdvice(Request $request, SerializerInterface $serializer, Advice $currentAdvice, EntityManagerInterface $em, MonthRepository $monthRepo, ValidatorInterface $validator): JsonResponse
    {
        $updatedAdvice = $serializer->deserialize($request->getContent(), Advice::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice,
            'ignored_attributes' => ['months']
        ]);

        $errors = $validator->validate($updatedAdvice);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $monthsIds = $content['months'] ?? [];
        foreach ($monthsIds as $monthId) {
            $month = $monthRepo->find($monthId);
            if ($month) {
                $updatedAdvice->addMonth($month);
            }
        }

        $em->persist($updatedAdvice);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/conseil/{id}', name: 'deleteAdvice', methods: ['DELETE'])]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
