<?php

namespace App\Tests\Controller;
use App\Entity\BankAccount;
use App\Test\CustomApiTestCase;

class TransferControllerTest extends CustomApiTestCase
{
    public function testNoAccessForUnauthenticatedUserToTransfers(): void
    {
        static::createClient()->request('GET', '/api/transfers');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAccessForAuthenticatedUserToTransfers(): void
    {
        $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');
        static::createClient()->request('GET', '/api/transfers', [
            'headers' => ['Authorization' => "Bearer $token"]
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testAuthenticatedUserTransferNotFound(): void
    {
        $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');
        static::createClient()->request('GET', '/api/transfers/1', [
            'headers' => ['Authorization' => "Bearer $token"]
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['errors' => 'Transfer not found']);
    }

    public function testNoAccessForUnauthenticatedUserAddTransfers(): void
    {
        static::createClient()->request('POST', '/api/transfers', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserAddTransfer(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $firstBankAccount = $this->createBankAccount('1234567', 100.23, $user);
        $secondBankAccount = $this->createBankAccount('5834567', 50.00, $user);
        $token = $this->getToken('user1@email.com', '123456');
        
        static::createClient()->request('POST', '/api/transfers', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'json' => [
                'from_account' => $firstBankAccount->getId(),
                'to_account' => $secondBankAccount->getId(),
                'amount' => 20.23
            ],
        ]);

        $bankRepository = static::getContainer()->get('doctrine')->getRepository(BankAccount::class);

        $updatedFirstBankAccount = $bankRepository->find($firstBankAccount->getId());
        $updatedSecondBankAccount = $bankRepository->find($secondBankAccount->getId());

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => 'Transfer added successfully']);
        $this->assertEquals(80.00, $updatedFirstBankAccount->getBalance());
        $this->assertEquals(70.23, $updatedSecondBankAccount->getBalance());
    }

    public function testAuthenticatedUserNotEnoughMoneyTransfer(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $firstBankAccount = $this->createBankAccount('1234567', 100.23, $user);
        $secondBankAccount = $this->createBankAccount('5834567', 50.00, $user);
        $token = $this->getToken('user1@email.com', '123456');

        static::createClient()->request('POST', '/api/transfers', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'json' => [
                'from_account' => $firstBankAccount->getId(),
                'to_account' => $secondBankAccount->getId(),
                'amount' => 200.23
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['message' => 'Not enough money']);
    }
}