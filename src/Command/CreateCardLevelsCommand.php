<?php
namespace App\Command;

use App\Entity\Card;
use App\Entity\Level;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CreateCardLevelsCommand extends Command
{
    protected static $defaultName = 'app:create-cardlevel';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create all dofus levels with cards')
            ->setName('app:create-cardlevel')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $levelRepository = $this->em->getRepository(Level::class);
            $cardRepository = $this->em->getRepository(Card::class);
        
            $json = json_decode(file_get_contents('resources/levels.json'), true);
            foreach ($json as $level) {
                $currentLevel = $levelRepository->findOneByDofusLevel(substr($level['nameLevel'], 7));
                foreach ($level['cards'] as $card) {
                    $currentCard = $cardRepository->findOneById($card['cardIconId']);
                    $currentLevel->addCard($currentCard);
                }
                $this->em->persist($currentLevel);
            }
            
            $this->em->flush();

            $io->success('Insertion réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
