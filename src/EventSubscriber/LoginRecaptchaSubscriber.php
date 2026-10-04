<?php

namespace App\EventSubscriber;

use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

class LoginRecaptchaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private Recaptcha3Validator $recaptcha3Validator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckPassportEvent::class => ['onCheckPassport', 10],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        // Solo aplica en la ruta de login y con método POST
        if ($request->attributes->get('_route') !== 'app_login' || !$request->isMethod('POST')) {
            return;
        }

        $token = (string) $request->request->get('g-recaptcha-response');

        if (!$token) {
            throw new CustomUserMessageAuthenticationException('Actividad sospechosa detectada. Intente de nuevo. (Token vacío)');
        }

        $isValid = $this->validateToken($token, 'login', $request->getClientIp() ?? '');

        if (!$isValid) {
            throw new CustomUserMessageAuthenticationException('Error de validación reCAPTCHA. Actividad sospechosa.');
        }
    }

    private function validateToken(string $token, string $action, string $ip): bool
    {
        $secret = $_ENV['RECAPTCHA3_SECRET'] ?? '';

        if (empty($secret)) {
            // Si no hay configuracion, podemos ignorarlo en dev o fallar
            return true;
        }

        $recaptcha = new \Karser\Recaptcha3Bundle\ReCaptcha\ReCaptcha($secret);

        //puntaje minimo de 0.5, puntaje perfecto es 1.0 (humano perfecto)
        //para probar que se evalue bien el puntaje prueba a subirlo a 1.1 (puntaje imposible)
        $resp = $recaptcha->setExpectedAction($action)
                          ->setScoreThreshold(0.5)
                          ->verify($token, $ip);

        return $resp->isSuccess();
    }
}
