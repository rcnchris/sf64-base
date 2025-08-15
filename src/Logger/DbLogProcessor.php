<?php

namespace App\Logger;

use Monolog\Attribute\AsMonologProcessor;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsMonologProcessor(null, 'dbLogHandler')]
final class DbLogProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security
    ) {}

    public function __invoke(LogRecord $record): LogRecord
    {
        try {
            $session = $this->requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            return $record;
        }

        if (!$session->isStarted()) {
            $session->start();
        }

        $request = $this->requestStack->getCurrentRequest();
        $record->extra += [
            'ip' => $request->getClientIp(),
            'browser' => $request->server->get('HTTP_USER_AGENT'),
            'url' => $request->getUri(),
            'method' => $request->getMethod(),
            'ajax' => $request->isXmlHttpRequest(),
            'route' => $request->attributes->get('_route'),
            'route_params' => $request->attributes->get('_route_params'),
            'route_query' => $request->query->all(),
            'locale' => $request->getLocale(),
            'controller' => $request->attributes->get('_controller'),
            'session' => $request->getSession()->getId(),
            'user' => $this->security->getUser(),
        ];
        return $record;
    }
}
