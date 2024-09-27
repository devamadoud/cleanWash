<?php

namespace App\DataFixtures;

use App\Entity\Categories;
use App\Entity\Customer;
use App\Entity\Employe;
use App\Entity\Product;
use App\Entity\Shop;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher = UserPasswordHasherInterface::class;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $date = $faker->dateTimeBetween('-10 years', 'now');
        $dateTimeZone = new \DateTimeZone('Africa/Dakar');
        $date = $date->setTimezone($dateTimeZone);

        $superAdmin = new User();
        $superAdmin->setTelefone("783841245")
            ->setFullName($faker->firstName() . ' ' . $faker->lastName())
            ->setAdress($faker->address())
            ->setUserType('admin')
            ->setCreatedAt(DateTimeImmutable::createFromMutable($date))
            ->setUpdatedAt(DateTimeImmutable::createFromMutable($date))
            ->setPassword($this->passwordHasher->hashPassword($superAdmin, 'password'))
            ->setRoles(['ROLE_SUPER_ADMIN'])
        ;
        $manager->persist($superAdmin);

        for ($i = 0; $i < 10; $i++) {
            // Créer un utilisateur pour la sh$shop
            $owner = new User();
            $shop = new Shop();
            $owner->setTelefone($faker->numberBetween(770000000, 779999999))
                ->setFullName($faker->firstName() . ' ' . $faker->lastName())
                ->setAdress($faker->address())
                ->setUserType('owner')
                ->setCreatedAt(DateTimeImmutable::createFromMutable($date))
                ->setUpdatedAt(DateTimeImmutable::createFromMutable($date))
                ->setPassword($this->passwordHasher->hashPassword($owner, 'password'))
                ->setShop($shop)
                ->setRoles(['ROLE_OWNER'])
            ;

            $manager->persist($owner);

            // Créer la $shop
            $shop->setOwner($owner)
                ->setComName($faker->company())
                ->setAdress($faker->address())
                ->setCreatedAt(DateTimeImmutable::createFromMutable($date))
                ->setUpdatedAt(DateTimeImmutable::createFromMutable($date))
                ->setOwner($owner)
            ;

            $manager->persist($shop);

            // Ajouter des employés à la sh$shop
            $numEmployees = rand(1, 3);
            for ($j = 0; $j < $numEmployees; $j++) {
                $user = new User();
                $employe = new Employe();
                $user->setTelefone($faker->numberBetween(770000000, 779999999))
                    ->setFullName($faker->firstName() . ' ' . $faker->lastName())
                    ->setAdress($faker->address())
                    ->setUserType('employe')
                    ->setCreatedAt(DateTimeImmutable::createFromMutable($date))
                    ->setUpdatedAt(DateTimeImmutable::createFromMutable($date))
                    ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                    ->setJob($employe)
                    ->setRoles(['ROLE_EMPLOYE'])
                ;

                $manager->persist($user);

                $employe->setUser($user)
                    ->setShop($shop)
                    ->setPoste($faker->randomElement(['collecteur', 'laveur', 'caissier']))
                    ->setEmployedAt(DateTimeImmutable::createFromMutable($date))
                    ->setRevokedAt(DateTimeImmutable::createFromMutable($date))
                    ->setActive(true)
                ;

                $manager->persist($employe);
            }

            $category = new Categories();
            for ($x=0; $x < 10; $x++) { 
                $category->setName($faker->word())
                    ->setCategoryDescription($faker->text(200))
                ;

                $manager->persist($category);
            }

            // Ajouter des produits à la sh$shop
            $numProducts = rand(10, 60);
            for ($k = 0; $k < $numProducts; $k++) {
                $product = new Product();
                $product->setName($faker->word)
                    ->setShop($shop)
                    ->setCreatedAt(DateTimeImmutable::createFromMutable($date))
                    ->setUpdatedAt(DateTimeImmutable::createFromMutable($date))
                    ->setPrice($faker->numberBetween(500, 50000))
                    ->setQuantityStocke($faker->numberBetween(1, 500))
                    ->setQuantitySales($faker->numberBetween(1, 1000))
                    ->setPurchasePrice($faker->numberBetween(500, 50000))
                    ->setProductDescription($faker->text)
                    ->setProductImage("https://via.assets.so/furniture.png?id=1&q=95&w=360&h=360&fit=fill")
                    ->setPublished(true)
                    ->setPromo($faker->randomElement([$faker->numberBetween(0, 50), 0]))
                    ->setPromoPrice($faker->numberBetween(500, 50000))
                    ->addCategory($category)
                ;

                $manager->persist($product);
            }

            // Ajouter des clients à la sh$shop
            $numCustomers = rand(0, 150);
            for ($l = 0; $l < $numCustomers; $l++) {
                $customer = new Customer();
                $customer->setFullName($faker->name)
                    ->setShop($shop)
                    ->setPhoneNumber($faker->numberBetween(770000000, 779999999))
                    ->setCoordLat($faker->latitude)
                    ->setCoordLng($faker->longitude)
                    ->setAdress($faker->address)
                ;

                $manager->persist($customer);
            }
        }

        $manager->flush();
    }
}
