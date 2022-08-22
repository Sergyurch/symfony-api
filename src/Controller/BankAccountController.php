<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Repository\BankAccountRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class BankAccountController extends AbstractController
{
    #[Route('/bank_accounts', name: 'bank_accounts', methods:['GET'])]
    public function getBankAccounts(BankAccountRepository $bankAccountRepository): JsonResponse
    {
        $bankAccounts = $bankAccountRepository->findAll();
        return $this->response($bankAccounts);
    }

    #[Route('/bank_accounts', name: 'bank_accounts_add', methods: ['POST'])]
    public function addBankAccount(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('iban') || !$request->get('balance') || !$request->get('client')) {
                throw new \Exception();
            }

            $balanceToCheck = $request->get('balance') * 100;
                        
            if ($balanceToCheck != floor($balanceToCheck)) {
                throw new \Exception('Wrong balance format');
            }

            $bankAccount = new BankAccount();
            $bankAccount->setIban($request->get('iban'));
            $bankAccount->setBalance($request->get('balance'));
            $bankAccount->setClient($userRepository->find($request->get('client')));
            $bankAccount->setCreatedAt(date_create());
            $bankAccount->setUpdatedAt(date_create());
            $entityManager->persist($bankAccount);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Bank account added successfully",
            ];

            return $this->response($data);

        } catch (\Exception $e) {
            $data = [
                'status' => 422,
                'errors' => "Data no valid",
                'error' => $e->getMessage()
            ];

            return $this->response($data, 422);
        }
    }

    #[Route('/bank_accounts/{id}', name: 'bank_accounts_get', methods: ['GET'])]
    public function getBankAccount(BankAccountRepository $bankAccountRepository, $id): JsonResponse
    {
        $bankAccount = $bankAccountRepository->find($id);
        
        if (!$bankAccount) {
            $data = [
                'status' => 404,
                'errors' => "Bank account not found",
            ];

            return $this->response($data, 404);
        }

        return $this->response([$bankAccount]);
    }

    #[Route('/bank_accounts/{id}', name: 'bank_accounts_put', methods: ['PUT'])]
    public function updateBankAccount(Request $request, EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository, UserRepository $userRepository, $id): JsonResponse
    {
        try {
            $bankAccount = $bankAccountRepository->find($id);

            if (!$bankAccount){
                $data = [
                    'status' => 404,
                    'errors' => "Bank account not found",
                ];

                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('iban') || !$request->get('balance') || !$request->get('client')) {
                throw new \Exception();
            }

            $bankAccount->setIban($request->get('iban'));
            $bankAccount->setBalance($request->get('balance'));
            $bankAccount->setClient($userRepository->find($request->get('client')));
            $bankAccount->setUpdatedAt(date_create());
            $entityManager->persist($bankAccount);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Bank account updated successfully",
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

    #[Route('/bank_accounts/{id}', name: 'bank_accounts_delete', methods: ['DELETE'])]
    public function deleteBankAccount(EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository, $id){
        $bankAccount = $bankAccountRepository->find($id);

        if (!$bankAccount){
            $data = [
                'status' => 404,
                'errors' => "Bank account not found",
            ];

            return $this->response($data, 404);
        }

        $entityManager->remove($bankAccount);
        $entityManager->flush();
        $data = [
            'status' => 200,
            'success' => "Bank account deleted successfully",
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
