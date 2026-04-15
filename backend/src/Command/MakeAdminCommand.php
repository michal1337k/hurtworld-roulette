<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:make-admin',
    description: 'Add admin by steamid',
)]

class MakeAdminCommand extends Command
{

    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('steamid', InputArgument::REQUIRED, 'Steam ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $steamId = $input->getArgument('steamid');

        $user = $this->userRepository->find($steamId);

            if (!$user) {
                $output->writeln('User not found');
                return Command::FAILURE;
            }

            $user->setRoles(['ROLE_ADMIN']);

            $this->em->persist($user);
            $this->em->flush();

            $output->writeln('Admin added');

            return Command::SUCCESS;
    }
}
