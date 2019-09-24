<?php
namespace App\Factory;

use App\Entity\PaginatedCollection;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{

    const PER_PAGE = 10;

    private $router;
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createCollection(int $page, QueryBuilder $qb, Request $request, $route, array $routeParams = array())
    {

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage(self::PER_PAGE);
        $pagerfanta->setCurrentPage($page);

        $lists = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $lists[] = $result;
        }

        $paginatedCollection = new PaginatedCollection($lists, $pagerfanta->getNbResults());

        $routeParams = array_merge($routeParams, $request->query->all());
        $createLinkUrl = function($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));
        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }

        return $paginatedCollection;
    }
}
