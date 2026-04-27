<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(
    name: 'app:set-balance',
    description: 'Set balance to user by steamid',
)]
class SetBalanceCommand extends Command
{
    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em) 
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('steamid', InputArgument::REQUIRED, 'Steam ID')
            ->addArgument('amount', InputArgument::REQUIRED, 'Amount in cents (np. 1000 = 10$)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $steamId = $input->getArgument('steamid');
        $amount = (int) $input->getArgument('amount');

        $user = $this->userRepository->find($steamId);

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return Command::FAILURE;
        }

        if ($amount < 0) {
            $output->writeln('<error>Amount must be greater than or equal to 0</error>');
            return Command::FAILURE;
        }

        $newBalance = $amount;

        $user->setBalance($newBalance);

        $this->em->flush();

        $output->writeln(sprintf(
            '<info>Balance seted: %s</info>',
            $newBalance
        ));

        return Command::SUCCESS;
    }
}
