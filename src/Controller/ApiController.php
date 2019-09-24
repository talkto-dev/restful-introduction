<?php

namespace App\Controller;

use App\Factory\PaginationFactory;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends AbstractFOSRestController
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var PaginationFactory
     */
    protected $pagination;

    /**
     * BusScheduleController constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em, PaginationFactory $factory)
    {
        $this->em = $em;
        $this->pagination = $factory;
    }

    /**
     * @param int $code
     * @param $data
     * @param array $errors
     * @param array|null $page
     * @param array|null $links
     * @return Response
     */
    protected function handleResponse(int $code, $data, array $errors = [], array $page = [], array $links = []): Response
    {
        return parent::handleView($this->view($this->buildResponseBody($data, $errors, $page, $links), $code));
    }

    protected function buildResponseBody($data, $errors, $page, $links): array
    {
        $body = [];
        $body['data'] = $data;
        $body['errors'] = $errors;

        if (count($page) > 1) {
            $body['page'] = $page;
        }
        if (count($links) > 1) {
            $body['links'] = $links;
        }

        return $body;
    }
}