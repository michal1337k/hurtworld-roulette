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
    name: 'app:clear-inventory',
    description: 'Add a short description for your command',
)]
class ClearInventoryCommand extends Command
{
    public function __construct(private UserRepository $userRepository, private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('steamid', InputArgument::REQUIRED, 'Steam ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $steamId = $input->getArgument('steamid');

        $user = $this->userRepository->find($steamId);

        if (!$user) {
            $output->writeln('<error>User not found</error>');
            return Command::FAILURE;
        }

        $count = 0;

        foreach ($user->getInventories() as $inventory) {
            $this->em->remove($inventory);
            $count++;
        }

        $this->em->flush();

        $output->writeln(sprintf(
            '<info>Inventory cleared for %s. Removed records: %d</info>',
            $steamId,
            $count
        ));

        return Command::SUCCESS;
    }
}
