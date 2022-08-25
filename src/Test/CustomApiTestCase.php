<?php

namespace App\Test;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\BankAccount;
use App\Entity\User;

class CustomApiTestCase extends ApiTestCase
{
    protected function createUser(string $name, string $surname, int $personalCode, string $phoneNumber, string $dateOfBirth, string $email, string $password): User
    {
        $user = new User();
        $user->setName($name);
        $user->setSurname($surname);
        $user->setPersonalCode($personalCode);
        $user->setPhoneNumber($phoneNumber);
        $user->setDateOfBirth($dateOfBirth);
        $user->setEmail($email);
        $hashedPassword = static::getContainer()->get('security.user_password_hasher')->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->setCreatedAt(date_create());
        $user->setUpdatedAt(date_create());
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    protected function getToken(string $login, string $password)
    {
        $client = static::createClient();
        $client->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'username' => $login,
                'password' => $password,
            ]
        ]);

        return $client->getResponse()->toArray()['token'];
    }

    protected function createBankAccount(string $iban, float $balance, User $client): BankAccount
    {
        $bankAccount = new BankAccount();
        $bankAccount->setIban($iban);
        $bankAccount->setBalance($balance);
        $bankAccount->setClient($client);
        $bankAccount->setCreatedAt(date_create());
        $bankAccount->setUpdatedAt(date_create());
        $em = static::getContainer()->get('doctrine')->getManager();
        $em->persist($bankAccount);
        $em->flush();

        return $bankAccount;
    }
}