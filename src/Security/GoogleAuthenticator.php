<?php

namespace App\Security;

use App\Entity\StatusRecord;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private readonly ClientRegistry         $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router
    ) {}

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();

                // 1) Find existing user_internal
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

                // 2) If new user_internal, create them
                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setPassword(bin2hex(random_bytes(32))); //make password IMPOSSIBLE to type and remember since google will handle authentication anyways

                    // Logic: Determine Role based on Domain
                    if (str_ends_with($email, '@corporate.com')) {
                        $user->setRoles(['ROLE_INTERNAL']);
                    } else {
                        $user->setRoles(['ROLE_EXTERNAL']);
                    }

                    $user->setUidCreate(-1);
                    $user->setCreated(new \DateTime('now'));
                    $user->setIsVerified(true); //google already checked this for me
                    $user->setStatus($this->entityManager->getRepository(StatusRecord::class)->getActive());
                    $user->setAvatarUrl($googleUser->getAvatar());
                    $this->entityManager->persist($user);
                    $this->entityManager->flush();
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();

        // 3) Redirection Logic: If external and profile is empty, force completion
        if (in_array('ROLE_EXTERNAL', $user->getRoles())) {
            if (!$user->getExternalProfile() || !$user->getExternalProfile()->getNroDocumento()) {
                return new RedirectResponse($this->router->generate('app_profile_complete'));
            }
        }

        return new RedirectResponse($this->router->generate('app_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed!', Response::HTTP_FORBIDDEN);
    }
}
