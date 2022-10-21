<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create client
        for ($i = 0; $i < 5; $i++) {
            $client = new Client();
            $client->setName('Client' . $i);
            $client->setTelephone('0123456789');
            $manager->persist($client);
            $this->setReference('client' . $i, $client);
        }

        // Create normal user
        for ($i = 0; $i < 15; $i++) {
            $user = new User();
            $user->setEmail("user$i@bilemo.com");
            $user->setRoles(["ROLE_USER"]);
            $user->setClient($this->getReference('client' . random_int(0, 4)));
            $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
            $manager->persist($user);
        }

        // Create user admin
        for ($i = 0; $i < 5; $i++) {
            $admin = new User();
            $admin->setEmail("admin$i@bilemo.com");
            $admin->setRoles(["ROLE_ADMIN"]);
            $admin->setClient($this->getReference('client' . $i));
            $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "password"));
            $manager->persist($admin);
        }

        // Create brand
        $arrayBrand = ['Apple', 'Samsung', 'Huawei', 'OneNote', 'Xiaomi'];
        for ($i = 0; $i < 5; $i++) {
            $brand = new Brand();
            $brand->setName($arrayBrand[random_int(0, 4)]);
            $manager->persist($brand);
            $this->setReference('brand' . $i, $brand);
        }

        for ($i = 0; $i < 20; $i++) {
            $product = new Product();
            $product->setModel('Model ' . $i);
            $product->setBrand($this->getReference('brand' . random_int(0, 4)));
            $product->setColor('Black');
            $product->setMemory('64 Mb');
            $product->setRearCamera('12 MP');
            $product->setScreenSize('6.1 in');
            $manager->persist($product);
        }

        $manager->flush();
    }
}
