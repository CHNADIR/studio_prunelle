<?php

namespace App\Command;

use App\Service\UserPasswordService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

#[AsCommand(
    name: 'app:user:reset-password',
    description: 'Réinitialise le mot de passe d\'un utilisateur',
)]
class UserResetPasswordCommand extends Command
{
    public function __construct(private UserPasswordService $userPasswordService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'L\'email de l\'utilisateur')
            ->addOption('send-email', null, InputOption::VALUE_NONE, 'Envoyer un email avec le nouveau mot de passe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $sendEmail = $input->getOption('send-email');

        try {
            $newPassword = $this->userPasswordService->resetPassword($email, $sendEmail);
            
            $io->success("Le mot de passe de l'utilisateur $email a été réinitialisé.");
            
            if ($sendEmail) {
                $io->info("Un email a été envoyé à l'utilisateur avec le nouveau mot de passe.");
            } else {
                $io->text("Nouveau mot de passe: $newPassword");
                $io->warning("Aucun email n'a été envoyé. L'utilisateur devra être informé manuellement.");
            }
            
            return Command::SUCCESS;
        } catch (UserNotFoundException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        } catch (\Exception $e) {
            $io->error("Une erreur est survenue: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}