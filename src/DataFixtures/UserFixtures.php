<?php
// src/DataFixtures/UserFixtures.php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $users = [
            ['email' => 'admin@example.com', 'roles' => ['ROLE_ADMIN'], 'password' => '1234'],
            ['email' => 'dispatcher@example.com', 'roles' => ['ROLE_DISPATCHER'], 'password' => '1234'],
            ['email' => 'driver@example.com', 'roles' => ['ROLE_DRIVER'], 'password' => '1234'],
        ];

        foreach ($users as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $manager->persist($user);
        }

        $manager->flush();
    }
}
