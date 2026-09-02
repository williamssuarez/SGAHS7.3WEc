<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

readonly class CheckProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private Security        $security
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) return;

        $user = $this->security->getUser();
        $route = $event->getRequest()->attributes->get('_route');

        // Allow these routes to avoid an infinite redirect loop
        $allowList = [
            'app_profile_complete',
            'app_logout',
            'connect_google_start',
            'connect_google_check',
            'app_verify_email',
            'app_check_inbox',
            'app_send_check_inbox',
            'app_account_blocked',
            'app_account_expired',
            'app_verify_resend_email' // <-- Add this route!
        ];

        if ($user && !in_array($route, $allowList)) {

            // 1. Check if the account is administratively blocked
            if ($user->getStatus() && $user->getStatus()->getCodigo() === 'NLOKREC') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_account_blocked')));
                return; // Stop execution immediately
            }

            // 2. Check if the account's credentials are expired
            if ($user->getStatus() && $user->getStatus()->getCodigo() === 'CEXPREC') {
                $event->setResponse(new RedirectResponse($this->router->generate('app_account_expired')));
                return; // Stop execution immediately
            }

            //If their info is not completed then redirect bro to the complete form
            if (in_array('ROLE_EXTERNAL', $user->getRoles())) {
                //check if email is verified
                if (!$user->isVerified()) {
                    $event->setResponse(new RedirectResponse($this->router->generate('app_send_check_inbox')));
                    return; // Stop here, do not check profile completion yet
                }

                //check if profile is complete
                if (!$user->getExternalProfile() || !$user->getExternalProfile()->getNroDocumento()){
                    $event->setResponse(new RedirectResponse($this->router->generate('app_profile_complete')));
                }
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => 'onKernelRequest'];
    }
}
