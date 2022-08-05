<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class UserController extends AbstractController
{
    #[Route('/users', name: 'users', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->response($users);
    }

    #[Route('/users', name: 'users_add', methods: ['POST'])]
    public function addUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name') || !$request->get('surname') || !$request->get('personal_code') || 
                !$request->get('phone_number') || !$request->get('date_of_birth')) {
                throw new \Exception();
            }

            $user = new User();
            $user->setName($request->get('name'));
            $user->setSurname($request->get('surname'));
            $user->setPersonalCode($request->get('personal_code'));
            $user->setPhoneNumber($request->get('phone_number'));
            $user->setDateOfBirth(date_create($request->get('date_of_birth')));
            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "User added successfully",
            ];
            return $this->response($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }
    }

    #[Route('/users/{id}', name: 'users_get', methods: ['GET'])]
    public function getOneUser(UserRepository $userRepository, $id): JsonResponse
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            $data = [
                'status' => 404,
                'errors' => "User not found",
            ];
            return $this->response($data, 404);
        }

        return $this->response([$user]);
    }

    #[Route('/users/{id}', name: 'users_put', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, $id): JsonResponse
    {
        try {
            $user = $userRepository->find($id);

            if (!$user){
                $data = [
                    'status' => 404,
                    'errors' => "User not found",
                ];
                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('name') || !$request->get('surname') || !$request->get('personal_code') || 
                !$request->get('phone_number') || !$request->get('date_of_birth')) {
                throw new \Exception();
            }

            $user->setName($request->get('name'));
            $user->setSurname($request->get('surname'));
            $user->setPersonalCode($request->get('personal_code'));
            $user->setPhoneNumber($request->get('phone_number'));
            $user->setDateOfBirth(date_create($request->get('date_of_birth')));
            $entityManager->persist($user);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "User updated successfully",
            ];
            return $this->response($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
            ];
            return $this->response($data, 422);
        }
    }

    #[Route('/users/{id}', name: 'users_delete', methods: ['DELETE'])]
    public function deleteUser(EntityManagerInterface $entityManager, UserRepository $userRepository, $id){
        $user = $userRepository->find($id);

        if (!$user){
            $data = [
                'status' => 404,
                'errors' => "User not found",
            ];
            return $this->response($data, 404);
        }

        $entityManager->remove($user);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'success' => "User deleted successfully",
        ];
        return $this->response($data);
    }

    protected function response(array $data, $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function transformJsonBody(Request $request): Request
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $request;
        }

        $request->request->replace($data);

        return $request;
    }
}
