<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Entity\User;
use App\Message\SendEmailMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:add-role',
    description: 'Add some role to some user',
    aliases: [ 'a:u:a-r' ],
)]
class UserAddRoleCommand extends Command
{
    public function __construct(private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'User role', 'ROLE_ADMIN')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info("Start of add role");
        $io->progressStart(1);

        $email = $input->getArgument('email');
        $role = $input->getOption('role');

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if ($user === null) {
            $io->error("User with this email not found: $email");
            return Command::INVALID;
        }
        
        $user->addRole($role);
        $this->userRepository->flush();
        $io->progressAdvance(1);

        $io->success('DONE!');
        $io->progressFinish();


        return Command::SUCCESS;
    }
}