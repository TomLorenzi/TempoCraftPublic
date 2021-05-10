<?php
namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class ResetUsersVoteCommand extends Command
{
    protected static $defaultName = 'app:reset-users';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Reset users votes and crafts number')
            ->setName('app:reset-users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $userRepository = $this->em->getRepository(User::class);

            $allUsers = $userRepository->findAll();
            foreach ($allUsers as $key => $user) {
                $user->setCraftPerDay(0);
                $user->setVotesPerDay(0);
                $this->em->persist($user);
            }
            
            $this->em->flush();

            $io->success('Reset effectué');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
