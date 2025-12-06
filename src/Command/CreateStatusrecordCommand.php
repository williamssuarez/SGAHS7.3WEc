<?php

namespace App\Command;

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
    name: 'app:create-statusrecord',
    description: 'Este comando genera los valores para la tabla StatusRecord. Estos registros deben generarse despues de la migracion para que los Repositories funcionen correctamente',
)]
class CreateStatusrecordCommand extends Command
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

        /*if ($input->getOption('option1')) {
        }*/

        //crear status record
        $codeArr = ['ACTRECORD', 'REMRECORD', 'DISHRECORD', 'NEXPREC', 'NLOKREC', 'CEXPREC'];
        $titelArr = [
            'Registro Activo',
            'Registro Eliminado',
            'Registro Deshabilitado',
            'Usuario no Expirado',
            'Usuario no Bloqueado',
            'Credencial no Expirado'
        ];

        for ($i = 0; $i <= 5; $i++){
            $statusRecord = new StatusRecord();
            $statusRecord->setId($i + 1);
            $statusRecord->setCodigo($codeArr[$i]);
            $statusRecord->setTitulo($titelArr[$i]);
            $this->em->persist($statusRecord);
            $this->em->flush();
        }


        $io->success('Status Records Creados!');

        return Command::SUCCESS;
    }
}
