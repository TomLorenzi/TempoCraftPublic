<?php
namespace App\Command;

use App\Entity\Monster;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManagerInterface;


class CreateMonsterCommand extends Command
{
    protected static $defaultName = 'app:create-monster';

    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create all dofus Monsters')
            ->setName('app:create-monster')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            ini_set('memory_limit','1G');
            ini_set('max_execution_time', 7200);
            $ch = curl_init();
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            curl_setopt($ch, CURLOPT_URL, 'https://fr.dofus.dofapi.fr/monsters');
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response = curl_exec($ch);
            if ($response === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            $data = json_decode($response, true);
            $monsterRepository = $this->em->getRepository(Monster::class);
            foreach ($data as $i => $monster) {
                $newMonster = new Monster();
                if (isset($monster['name'])) {
                    $newMonster->setName($monster['name']);
                }
                if (isset($monster['ankamaId'])) {
                    $newMonster->setAnkamaId($monster['ankamaId']);
                }
                $this->em->persist($newMonster);
            }

            $this->em->flush();

            $io->success('Insertion réussit');
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        return 0;
    }
}
