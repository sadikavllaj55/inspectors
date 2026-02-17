<?php

namespace App\Controller\Api;

use App\Entity\Job;
use App\Enum\JobStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Inspector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\JobCompleteRequest;
use App\Helper\ValidationHelper;
use App\Helper\DtoHelper;
use App\DTO\JobUpdateRequest;
use App\DTO\JobCreateRequest;
use App\DTO\JobAssignRequest;
use App\Helper\JobHelper;


#[Route('/api/jobs')]
class JobController extends AbstractController
{

    #[Route('', methods: ['POST'])]
    #[OA\Post(
        path: '/api/jobs',
        summary: 'Create a new job',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'description'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Backend Developer'),
                    new OA\Property(property: 'description', type: 'string', example: 'Responsible for backend systems'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 400, description: 'Invalid data'),
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $dto = new JobCreateRequest();
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

        $job = new Job();
        $job->setTitle($dto->title);
        $job->setDescription($dto->description);

        $em->persist($job);
        $em->flush();

        return $this->json(JobHelper::toArray($job), 201);
    }


    #[Route('', methods: ['GET'])]
    #[OA\Get(
        path: '/api/jobs',
        summary: 'List jobs (with optional filters)',
        parameters: [
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: 'Filter by job status',
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['available', 'assigned', 'completed']
                )
            ),
            new OA\Parameter(
                name: 'inspector',
                in: 'query',
                required: false,
                description: 'Filter by inspector ID',
                schema: new OA\Schema(
                    type: 'integer'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of jobs'
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid filter value'
            )
        ]
    )]
    public function list(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $status = $request->query->get('status');
        $inspectorId = $request->query->get('inspector');

        $criteria = [];

        if ($status) {
            try {
                $criteria['status'] = JobStatus::from($status);
            } catch (\ValueError $e) {
                return $this->json(['error' => 'Invalid status value'], 400);
            }
        }

        if ($inspectorId) {
            $criteria['inspector'] = $inspectorId;
        }

        $jobs = $em->getRepository(Job::class)->findBy($criteria);

        return $this->json(
            array_map(fn(Job $job) => JobHelper::toArray($job), $jobs)
        );
    }

    #[Route('/{id}', methods: ['GET'])]
    #[OA\Get(
        path: '/api/jobs/{id}',
        summary: 'Get job by ID',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Job found'),
            new OA\Response(response: 404, description: 'Job not found')
        ]
    )]
    public function show(int $id, EntityManagerInterface $em): JsonResponse
    {
        $job = $em->getRepository(Job::class)->find($id);

        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        return $this->json(JobHelper::toArray($job));
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/jobs/{id}',
        summary: 'Update job',
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
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', example: 'AVAILABLE')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Updated'),
            new OA\Response(response: 404, description: 'Job not found')
        ]
    )]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $job = $em->getRepository(Job::class)->find($id);

        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $dto = new JobUpdateRequest();
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

        if ($dto->title !== null) {
            $job->setTitle($dto->title);
        }

        if ($dto->description !== null) {
            $job->setDescription($dto->description);
        }

        if ($dto->status !== null) {
            try {
                $job->setStatus(JobStatus::from($dto->status));
            } catch (\ValueError $e) {
                return $this->json(['error' => 'Invalid job status'], 400);
            }
        }

        $em->flush();

        return $this->json(JobHelper::toArray($job));
    }
    #[Route('/{id}', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/jobs/{id}',
        summary: 'Delete job',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Deleted'),
            new OA\Response(response: 404, description: 'Job not found')
        ]
    )]
    public function delete(int $id, EntityManagerInterface $em): JsonResponse
    {
        $job = $em->getRepository(Job::class)->find($id);

        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        $em->remove($job);
        $em->flush();

        return $this->json(null, 204);
    }

    #[Route('/{id}/assign', methods: ['POST'])]
    #[OA\Post(
        path: '/api/jobs/{id}/assign',
        summary: 'Assign a job to an inspector',
        description: 'Only available jobs can be assigned.',
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
                required: ['inspector_id', 'scheduled_at'],
                properties: [
                    new OA\Property(property: 'inspector_id', type: 'integer', example: 1),
                    new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time', example: '2026-02-17T10:00:00Z')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job assigned successfully'),
            new OA\Response(response: 400, description: 'Invalid input'),
            new OA\Response(response: 404, description: 'Job or inspector not found'),
            new OA\Response(response: 409, description: 'Job is not available')
        ]
    )]
    public function assign(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $job = $em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        if ($job->getStatus() !== JobStatus::AVAILABLE) {
            return $this->json(['error' => 'Job is not available'], 409);
        }

        $data = json_decode($request->getContent(), true);

        $dto = new JobAssignRequest();
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

        $inspector = $em->getRepository(Inspector::class)->find($dto->inspector_id);
        if (!$inspector) {
            return $this->json(['error' => 'Inspector not found'], 404);
        }

        try {
            $scheduledAt = new \DateTimeImmutable($dto->scheduled_at);
            $job->assignTo($inspector, $scheduledAt);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid scheduled_at datetime'], 400);
        }

        $em->flush();

        return $this->json(JobHelper::toArray($job));
    }

    #[Route('/{id}/complete', methods: ['POST'])]
    #[OA\Patch(
        summary: 'Mark job as completed',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['assessment'],
                properties: [
                    new OA\Property(property: 'assessment', type: 'string', example: 'Inspection successful, no issues found')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Job completed'),
            new OA\Response(response: 400, description: 'Invalid state'),
            new OA\Response(response: 404, description: 'Job not found')
        ]
    )]
    public function complete(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): JsonResponse {
        $job = $em->getRepository(Job::class)->find($id);
        if (!$job) {
            return $this->json(['error' => 'Job not found'], 404);
        }

        if ($job->getStatus() !== JobStatus::ASSIGNED) {
            return $this->json(['error' => 'Only assigned jobs can be completed'], 409);
        }

        $data = json_decode($request->getContent(), true);
        $dto = new JobCompleteRequest();

        try {
            DtoHelper::fill($dto, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }

        $violations = $validator->validate($dto);
        if (count($violations) > 0) {
            return $this->json(['errors' => ValidationHelper::formatErrors($violations)], 400);
        }

        try {
            $job->complete($dto->assessment);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        }

        $em->flush();

        return $this->json(JobHelper::toArray($job));
    }

}
