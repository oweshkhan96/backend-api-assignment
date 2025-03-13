<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class RestoreController extends AbstractController
{
    #[Route('/api/restore', name: 'api_restore', methods: ['POST'])]
    public function restoreDatabase(Request $request, EntityManagerInterface $entityManager): Response
    {
        $file = $request->files->get('backup');

        if (!$file) {
            return $this->json(['status' => 'error', 'message' => 'No file uploaded.'], 400);
        }

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            fgetcsv($handle); // Skip CSV header row
            
            while (($data = fgetcsv($handle)) !== false) {
                $user = new User();
                $user->setName($data[1]);
                $user->setEmail($data[2]);
                $user->setUsername($data[3]);
                $user->setAddress($data[4]);
                $user->setRole($data[5]);

                $entityManager->persist($user);
            }

            fclose($handle);
            $entityManager->flush();

            return $this->json(['status' => 'success', 'message' => 'Database restored successfully.']);
        }

        return $this->json(['status' => 'error', 'message' => 'Failed to read the file.'], 500);
    }
}
