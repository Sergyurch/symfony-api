<?php

namespace App\Controller;

use App\Entity\Transfer;
use App\Repository\BankAccountRepository;
use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class TransferController extends AbstractController
{
    #[Route('/transfers', name: 'transfers', methods:['GET'])]
    public function getTransfers(TransferRepository $transferRepository): JsonResponse
    {
        $transfers = $transferRepository->findAll();
        return $this->response($transfers);
    }

    #[Route('/transfers', name: 'transfers_add', methods: ['POST'])]
    public function addBankAccount(Request $request, EntityManagerInterface $entityManager, BankAccountRepository $bankAccountRepository): JsonResponse
    {
        try {
            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('from_account') || !$request->get('to_account') || !$request->get('amount') ) {
                throw new \Exception();
            }

            $transfer = new Transfer();
            $transfer->setFromAccount($bankAccountRepository->find($request->get('from_account')));
            $transfer->setToAccount($bankAccountRepository->find($request->get('to_account')));
            $transfer->setAmount($request->get('amount'));
            $transfer->setCreatedAt(date_create());
            $transfer->setUpdatedAt(date_create());
            $entityManager->persist($transfer);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Transfer added successfully",
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

    #[Route('/transfers/{id}', name: 'trnasfers_get', methods: ['GET'])]
    public function getBankAccount(TransferRepository $transferRepository, $id): JsonResponse
    {
        $transfer = $transferRepository->find($id);
        
        if (!$transfer) {
            $data = [
                'status' => 404,
                'errors' => "Transfer not found",
            ];

            return $this->response($data, 404);
        }

        return $this->response([$transfer]);
    }

    #[Route('/transfers/{id}', name: 'transfers_put', methods: ['PUT'])]
    public function updateTransfer(Request $request, EntityManagerInterface $entityManager, TransferRepository $transferRepository, BankAccountRepository $bankAccountRepository, $id): JsonResponse
    {
        try {
            $transfer = $transferRepository->find($id);

            if (!$transfer){
                $data = [
                    'status' => 404,
                    'errors' => "Transfer not found",
                ];

                return $this->response($data, 404);
            }

            $request = $this->transformJsonBody($request);

            if (!$request || !$request->get('from_account') || !$request->get('to_account') || !$request->get('amount')) {
                throw new \Exception();
            }

            $transfer->setFromAccount($bankAccountRepository->find($request->get('from_account')));
            $transfer->setToAccount($bankAccountRepository->find($request->get('to_account')));
            $transfer->setAmount($request->get('amount'));
            $transfer->setUpdatedAt(date_create());
            $entityManager->persist($transfer);
            $entityManager->flush();

            $data = [
                'status' => 200,
                'success' => "Transfer updated successfully",
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

    #[Route('/transfers/{id}', name: 'transfers_delete', methods: ['DELETE'])]
    public function deleteTransfer(EntityManagerInterface $entityManager, TransferRepository $transferRepository, $id){
        $transfer = $transferRepository->find($id);

        if (!$transfer){
            $data = [
                'status' => 404,
                'errors' => "Transfer not found",
            ];

            return $this->response($data, 404);
        }

        $entityManager->remove($transfer);
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
