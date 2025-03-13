<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class BackupController extends AbstractController
{
    #[Route('/api/backup', name: 'api_backup', methods: ['GET'])]
    public function backupUsers(EntityManagerInterface $entityManager): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($entityManager) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Username', 'Address', 'Role']);

            // Fetch user data from database
            $users = $entityManager->getRepository(User::class)->findAll();

            // Write each user to CSV
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->getId(),
                    $user->getName(),
                    $user->getEmail(),
                    $user->getUsername(),
                    $user->getAddress(),
                    $user->getRole(),
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="users_backup.csv"');

        return $response;
    }
}
