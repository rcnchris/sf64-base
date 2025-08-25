<?php

namespace App\Twig\Components;

use App\Repository\LogRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\{Request, RequestStack};
use Symfony\UX\LiveComponent\Attribute\{AsLiveComponent, LiveProp};
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LogSearch
{
    use DefaultActionTrait;

    public string $placeholder = 'Rechercher un log...';

    #[LiveProp(writable: true)]
    public string $query = '';

    public function __construct(
        private readonly LogRepository $logRepository,
        private readonly RequestStack $requestStack,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function getLogs(): PaginationInterface
    {
        $request = $this->getRequest();
        return $this->paginator->paginate(
            $this->logRepository->searchByQueryString($this->query),
            $request->query->get('page', 1),
            $request->query->get('limit', 10)
        );
    }

    private function getRequest(?bool $parent = false): ?Request
    {
        return $parent ? $this->requestStack->getParentRequest() : $this->requestStack->getCurrentRequest();
    }
}
