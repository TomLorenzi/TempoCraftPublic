<?php
namespace App\Command;

use App\Entity\Level;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CreateLevelsCommand extends Command
{
    protected static $defaultName = 'app:create-levels';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create all dofus Level')
            ->setName('app:create-levels')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            for ($i = 1; $i <= 200; $i++) {
                $level = new Level();
                $level->setDofusLevel($i);
                $this->em->persist($level);
            }

            $this->em->flush();

            $io->success('Levels inseré en bdd');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
