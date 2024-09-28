<?php

namespace App\Command;

use App\Entity\Shop;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'addAdmin',
    description: 'Add Prime user admin for manage the platform',
)]
class AddAdminCommand extends Command
{
    protected static $defaultName = 'addAdmin';
    protected static $defaultDescription = 'Add admin user for manage the platform';

    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fullName', InputArgument::REQUIRED, 'Full name')
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('telefone', InputArgument::REQUIRED, 'Phone number')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Mot de passe')
            ->setHelp('Add admin user for manage the platform');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fullName = $input->getArgument('fullName');
        $email = $input->getArgument('email');
        $telefone = $input->getArgument('telefone');
        $password = $input->getOption('password');

        if (!$password) {
            $password = "BenteDev1*";
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['telefone' => $telefone]);

        if ($existingUser) {
            $io->error(sprintf('User %s already exists', $email .' '. $telefone));
            return Command::FAILURE;
        }

        $user = new User();

        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $password);
        
        $fullName == "Abdoul" ? $fullName = "Abdoul Wakhab Diallo" : $fullName = "Amadou Bente Diallo";

        $user->setFullName($fullName)
            ->setEmail($email)
            ->setTelefone($telefone)
            ->setPassword($hashedPassword)
            ->setUserType('owner')
            ->setAdress('Dakar/Senegal')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setRoles(['ROLE_SUPER_ADMIN'])
        ;

        if($fullName == "Abdoul Wakhab Diallo") {
            $shop = new Shop();
            $shop->setComName('Setsetal Service')
                ->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setPhoneNumber($telefone)
                ->setAdress("Sénégal, Dakar, Rue 59 X 54 Gueule Tapee")
                ->setOwner($user)
            ;
        }

        $this->entityManager->persist($shop);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('OK, Super admin manager successfully added');

        return Command::SUCCESS;
    }
}
