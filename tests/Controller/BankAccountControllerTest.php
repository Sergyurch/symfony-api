<?php

namespace App\Tests\Controller;

use App\Entity\BankAccount;
use App\Test\CustomApiTestCase;

class BankAccountControllerTest extends CustomApiTestCase
{
    public function testNoAccessForUnauthenticatedUserToBankAccounts(): void
    {
        static::createClient()->request('GET', '/api/bank_accounts');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAccessForAuthenticatedUserToBankAccounts(): void
    {
        $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');
        static::createClient()->request('GET', '/api/bank_accounts', [
            'headers' => ['Authorization' => "Bearer $token"]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAuthenticatedUserBankAccountNotFound(): void
    {
        $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');
        static::createClient()->request('GET', '/api/bank_accounts/1', [
            'headers' => ['Authorization' => "Bearer $token"]
        ]);

        $this->assertResponseStatusCodeSame(404);
        $this->assertJsonContains(['errors' => 'Bank account not found']);
    }

    public function testNoAccessForUnauthenticatedUserAddBankAccounts(): void
    {
        static::createClient()->request('POST', '/api/bank_accounts', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserAddBankAccounts(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');

        static::createClient()->request('POST', '/api/bank_accounts', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'json' => [
                'iban' => '123456789',
                'balance' => '1200.65',
                'client' => $user->getId()
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['success' => 'Bank account added successfully']);
    }

    public function testAuthenticatedUserWrongBalanceFormat(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $token = $this->getToken('user1@email.com', '123456');

        static::createClient()->request('POST', '/api/bank_accounts', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'json' => [
                'iban' => '123456789',
                'balance' => '1200.656',
                'client' => $user->getId()
            ],
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['error' => 'Wrong balance format']);
    }

    public function testNoAccessUnauthenticatedUserUpdateBankAccount(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $bankAccount = $this->createBankAccount('1234567', '100.23', $user);
        
        static::createClient()->request('PUT', "/api/bank_accounts/{$bankAccount->getId()}", [
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserUpdateBankAccount(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $bankAccount = $this->createBankAccount('1234567', '100.23', $user);
        $token = $this->getToken('user1@email.com', '123456');

        static::createClient()->request('PUT', "/api/bank_accounts/{$bankAccount->getId()}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ],
            'json' => [
                'iban' => '123456789',
                'balance' => '900.22',
                'client' => $user->getId()
            ],
        ]);

        $updatedBankAccount = static::getContainer()->get('doctrine')->getRepository(BankAccount::class)->find($bankAccount->getId());

        $this->assertResponseIsSuccessful();
        $this->assertEquals(900.22, $updatedBankAccount->getBalance());
    }

    public function testNoAccessUnauthenticatedUserDeleteBankAccount(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $bankAccount = $this->createBankAccount('1234567', '100.23', $user);
        
        static::createClient()->request('DELETE', "/api/bank_accounts/{$bankAccount->getId()}", [
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testAuthenticatedUserDeleteBankAccount(): void
    {
        $user = $this->createUser('username1', 'usersurname1', 123456789, '+380978547584', '1990-01-22', 'user1@email.com', '123456');
        $bankAccount = $this->createBankAccount('1234567', '100.23', $user);
        $token = $this->getToken('user1@email.com', '123456');
        
        static::createClient()->request('DELETE', "/api/bank_accounts/{$bankAccount->getId()}", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer $token"
            ]
        ]);

        $this->assertResponseIsSuccessful();
    }

}
