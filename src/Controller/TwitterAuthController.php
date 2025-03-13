<?php
// src/Controller/TwitterAuthController.php
namespace App\Controller;

use App\Service\TwitterOAuthService;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TwitterAuthController extends AbstractController
{
    #[Route('/auth/twitter', name: 'auth_twitter')]
    public function redirectToTwitter(TwitterOAuthService $twitterOAuthService, SessionInterface $session): RedirectResponse
    {
        $authData = $twitterOAuthService->getAuthorizationUrl();

        if (!isset($authData['url'], $authData['oauth_token'], $authData['oauth_token_secret'])) {
            throw new \Exception("Twitter authorization failed.");
        }

        // Store token data in session
        $session->set('oauth_token', $authData['oauth_token']);
        $session->set('oauth_token_secret', $authData['oauth_token_secret']);

        return new RedirectResponse($authData['url']);
    }

    #[Route('/auth/twitter/callback', name: 'auth_twitter_callback')]
    public function handleTwitterCallback(
        Request $request,
        TwitterOAuthService $twitterOAuthService,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): RedirectResponse {
        // Retrieve session tokens
        $storedToken = $session->get('oauth_token');
        $storedTokenSecret = $session->get('oauth_token_secret');

        // Get Twitter response
        $oauthToken = $request->query->get('oauth_token');
        $oauthVerifier = $request->query->get('oauth_verifier');

        // Validate session token
        if (!$storedToken || $oauthToken !== $storedToken) {
            return $this->redirectToRoute('auth_twitter');
        }

        // Fetch user data
        $userData = $twitterOAuthService->getUserData($oauthToken, $oauthVerifier, $storedTokenSecret);

        if (!$userData || !isset($userData['id'])) {
            throw new \Exception("Failed to retrieve user data from Twitter.");
        }

        // Check if user exists
        $user = $entityManager->getRepository(User::class)->findOneBy(['twitterId' => $userData['id']]);

        if (!$user) {
            $user = new User();
            $user->setTwitterId($userData['id']);
            $user->setUsername($userData['name']);
            $user->setProfileImage($userData['avatar']);
            $entityManager->persist($user);
            $entityManager->flush();
        }

        // Clear session variables
        $session->remove('oauth_token');
        $session->remove('oauth_token_secret');

        return $this->redirect('your_mobile_app://auth_success?user_id=' . $user->getId());
    }
}
