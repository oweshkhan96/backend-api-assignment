<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;

class UserUploadController extends AbstractController
{
    #[Route('/api/upload', name: 'upload_csv', methods: ['POST'])]
    public function uploadCSV(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            return new JsonResponse(['error' => 'No file uploaded or incorrect key used'], 400);
        }

        // Define the upload directory
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';

        // Ensure the directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Move the uploaded file
        $filePath = $uploadDir . 'data.csv';
        $file->move($uploadDir, 'data.csv');

        // Read CSV file
        if (($handle = fopen($filePath, "r")) !== false) {
            fgetcsv($handle); // Skip the first row (column headers)

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                [$name, $email, $username, $address, $role] = $data;

                $user = new User();
                $user->setName($name);
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setAddress($address);
                $user->setRole($role);

                $entityManager->persist($user);

                // Send Email
                $this->sendEmail($mailer, $email, $name);
                
            }
            $entityManager->flush(); // Flush once after loop
            fclose($handle);
        }

        return new JsonResponse(['message' => 'File uploaded, data stored, and emails sent!', 'path' => $filePath]);
    }

    private function sendEmail(MailerInterface $mailer, string $email, string $name): void
    {
        $emailMessage = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($email)
            ->subject('Welcome to Our Platform')
            ->text("Hello $name,\n\nYour data has been added successfully!\n\nThank you!");

        $mailer->send($emailMessage);
    }
}
