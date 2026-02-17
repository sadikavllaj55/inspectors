<?php

namespace App\Controller\Api;

use App\Entity\Inspector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Helper\ValidationHelper;
use App\DTO\InspectorCreateRequest;
use App\DTO\InspectorUpdateRequest;
use App\Helper\EnumHelper;
use App\Helper\InspectorHelper;
use App\Helper\DtoHelper;


#[Route('/api/inspectors')]
class InspectorController extends AbstractController
{
    #[Route('', methods: ['POST'])]
    #[OA\Post(
        summary: 'Create a new inspector',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'timezone'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'timezone', type: 'string', example: 'UK')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 400, description: 'Invalid data')
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new InspectorCreateRequest();
        try {
            DtoHelper::fill($dto, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            return $this->json([
                'errors' => ValidationHelper::formatErrors($violations)
            ], 400);
        }

        try {
            $inspector = new Inspector();
            $inspector->setName($dto->name)
                ->setEmail($dto->email)
                ->setTimezone(EnumHelper::timezoneFromString($dto->timezone));

            $em->persist($inspector);
            $em->flush();

            return $this->json(InspectorHelper::toArray($inspector), 201);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }


    #[Route('', methods: ['GET'])]
    #[OA\Get(
        summary: 'List all inspectors',
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of inspectors'
            )
        ]
    )]
    public function list(EntityManagerInterface $em): JsonResponse
    {
        $inspectors = $em->getRepository(Inspector::class)->findAll();

        $data = array_map(fn(Inspector $i) => [
            'id' => $i->getId(),
            'name' => $i->getName(),
            'email' => $i->getEmail(),
            'timezone' => $i->getTimezone()->name
        ], $inspectors);

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/inspectors/{id}',
        summary: 'Update an inspector',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'timezone', type: 'string', example: 'UK'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Inspector updated'),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Inspector not found')
        ]
    )]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {

        $inspector = $em->getRepository(Inspector::class)->find($id);

        if (!$inspector) {
            return $this->json([
                'error' => 'Inspector not found'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        $dto = new InspectorUpdateRequest();
        try {
            DtoHelper::fill($dto, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $violations = $validator->validate($dto);

        if (count($violations) > 0) {
            return $this->json([
                'errors' => ValidationHelper::formatErrors($violations)
            ], 400);
        }

        if ($dto->name !== null) {
            $inspector->setName($dto->name);
        }

        if ($dto->email !== null) {
            $inspector->setEmail($dto->email);
        }

        if ($dto->timezone !== null) {
            try {
                $inspector->setTimezone(EnumHelper::timezoneFromString($dto->timezone));
            } catch (\InvalidArgumentException $e) {
                return $this->json(['error' => $e->getMessage()], 400);
            }
        }

        $em->flush();

        return $this->json(InspectorHelper::toArray($inspector), 201);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/inspectors/{id}',
        summary: 'Delete an inspector',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Inspector deleted'),
            new OA\Response(response: 404, description: 'Inspector not found')
        ]
    )]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse {
        $inspector = $em->getRepository(Inspector::class)->find($id);
        if (!$inspector) {
            return $this->json(['error' => 'Inspector not found'], 404);
        }

        $em->remove($inspector);
        $em->flush();
        return $this->json(null, 204);
    }
}
