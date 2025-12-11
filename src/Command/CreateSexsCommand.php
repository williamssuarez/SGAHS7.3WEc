<?php

namespace App\Command;

use App\Entity\Sexo;
use App\Entity\StatusRecord;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-sexs',
    description: 'Este comando genera los valores para la tabla Sexos. Estos registros deben generarse despues de la migracion para que los Repositories funcionen correctamente',
)]
class CreateSexsCommand extends Command
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->em = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            //->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        //crear sexos
        $titelArr = [
            'Masculino',
            'Feminino',
            'Otro',
            'Desconocido',
        ];

        for ($i = 0; $i <= 3; $i++){
            $sexo = new Sexo();
            $sexo->setId($i + 1);
            $sexo->setSexo($titelArr[$i]);
            $sexo->setStatus($this->em->getRepository(StatusRecord::class)->getActive());
            $sexo->setCreated(new \DateTime());
            $sexo->setUpdated(new \DateTime());
            $sexo->setUidCreate(-1);
            $sexo->setUidUpdate(-1);
            $this->em->persist($sexo);
            $this->em->flush();
        }


        $io->success('Sexos Creados!');

        return Command::SUCCESS;
    }
}
