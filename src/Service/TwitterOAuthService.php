<?php
// src/Service/TwitterOAuthService.php
namespace App\Service;

use League\OAuth1\Client\Server\Twitter;
use League\OAuth1\Client\Credentials\TemporaryCredentials;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TwitterOAuthService
{
    private Twitter $server;
    private SessionInterface $session;

    public function __construct(UrlGeneratorInterface $router, RequestStack $requestStack)
    {
        $this->server = new Twitter([
            'identifier'    => $_ENV['TWITTER_CLIENT_ID'],
            'secret'        => $_ENV['TWITTER_CLIENT_SECRET'],
            'callback_uri'  => $router->generate('auth_twitter_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        $this->session = $requestStack->getSession();
    }

    public function getAuthorizationUrl(): array
    {
        $temporaryCredentials = $this->server->getTemporaryCredentials();

        if (!$temporaryCredentials) {
            throw new \Exception('Failed to obtain temporary credentials from Twitter.');
        }

        // Store credentials in Symfony session
        $this->session->set('oauth_token', serialize($temporaryCredentials));

        return [
            'url'             => $this->server->getAuthorizationUrl($temporaryCredentials),
            'oauth_token'     => $temporaryCredentials->getIdentifier(),
            'oauth_token_secret' => $temporaryCredentials->getSecret(),
        ];
    }

    public function getUserData(string $oauthToken, string $oauthVerifier): array
    {
        // Retrieve stored credentials
        $storedCredentials = $this->session->get('oauth_token');

        if (!$storedCredentials) {
            throw new \Exception('Session data for oauth_token is missing.');
        }

        $temporaryCredentials = unserialize($storedCredentials);

        if (!$temporaryCredentials instanceof TemporaryCredentials) {
            throw new \Exception('Invalid or corrupted temporary credentials.');
        }

        // Get the access token
        $tokenCredentials = $this->server->getTokenCredentials(
            $temporaryCredentials,
            $oauthToken,
            $oauthVerifier
        );

        $user = $this->server->getUserDetails($tokenCredentials);

        // Clear session after successful authentication
        $this->session->remove('oauth_token');

        return [
            'id'     => $user->uid,
            'name'   => $user->nickname,
            'avatar' => $user->imageUrl,
        ];
    }
}
