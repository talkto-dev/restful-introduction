<?php

namespace App\Controller;

use App\Entity\PaginatedCollection;
use App\Entity\TodoList;
use App\Factory\PaginationFactory;
use App\Form\TodoListType;
use App\Repository\TodoListRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Movie controller.
 * @Route("/todolist", name="api_")
 */
class TodoListController extends ApiController
{
    /**
     * @Rest\Get("/", name="get_list", options={ "method_prefix" = false })
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listList(Request $request)
    {
        $name = $request->query->get('name', '');
        /** @var TodoListRepository $todoItemRepository */
        $todoItemRepository = $this->em->getRepository(TodoList::class);
        $qb = $todoItemRepository->findAllQueryBuilder($name);
        $page = $request->query->get('page', 1);

        /** @var PaginatedCollection $paginatedCollection */
        $paginatedCollection = $this->pagination
            ->createCollection($page, $qb, $request, 'api_get_list');

        return $this->handleResponse(
            Response::HTTP_OK,
            $paginatedCollection->getItems(),
            [],
            [
                'size'    => PaginationFactory::PER_PAGE,
                'total'   => (int)ceil($paginatedCollection->getTotal() / PaginationFactory::PER_PAGE),
                'current' => (int)$page,
            ],
            $paginatedCollection->getLinks()
        );

    }

    /**
     * @Rest\Get("/{code}")
     *
     * @param string $code
     *
     * @return Response
     */
    public function getList(string $code)
    {
        /** @var TodoListRepository $todoListRepository */
        $todoListRepository = $this->em->getRepository(TodoList::class);
        $todoList = null;
        try {
            $todoList = $todoListRepository->findByCode($code);
        } catch (\Exception $exception) {
            $todoList = null;
        }

        if (!$todoList) {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $code));
        }

        return $this->handleResponse(Response::HTTP_OK, $todoList);
    }

    /**
     * @Rest\Post("/")
     *
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function postList(Request $request)
    {
        try {
            $todoList = $this->createNewList($request);

            return $this->handleResponse(Response::HTTP_CREATED, $todoList);
        } catch (\Exception $exception) {
            return $this->handleResponse(Response::HTTP_BAD_REQUEST, [], explode("|", $exception->getMessage()));
        }
    }

    /**
     * @Rest\Put("/{code}")
     *
     * @param Request $request
     * @param string $code
     *
     * @return Response
     */
    public function putList(Request $request, string $code)
    {
        try {
            /** @var TodoListRepository $todoListRepository */
            $todoListRepository = $this->em->getRepository(TodoList::class);
            /** @var TodoList $todoList */
            $todoList = $todoListRepository->findByCode($code);
            if (!$todoList) {
                $statusCode = Response::HTTP_CREATED;
                $todoList = $this->createNewList($request);
            } else {
                $statusCode = Response::HTTP_OK;
                $todoList = $this->processForm($todoList, $request->request->all(), 'PUT');
            }

            return $this->handleResponse($statusCode, $todoList);
        } catch (\Exception $exception) {
            return $this->handleResponse(Response::HTTP_BAD_REQUEST, [], explode("|", $exception->getMessage()));
        }
    }

    /**
     * @Rest\Delete("/{code}")
     *
     * @param Request $request
     * @param $code
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteList(Request $request, $code)
    {
        /** @var TodoListRepository $todoListRepository */
        $todoListRepository = $this->em->getRepository(TodoList::class);
        /** @var TodoList $todoList */
        $todoList = $todoListRepository->findByCode($code);

        if ($todoList) {
            $todoList->setDeleted(1);
            $this->em->persist($todoList);
            $this->em->flush();
        } else {
            throw new NotFoundHttpException(sprintf('The resource \'%s\' was not found.', $code));
        }

        return $this->handleResponse(Response::HTTP_NO_CONTENT, []);
    }


    /**
     * @param Request $request
     *
     * @return \App\Entity\TodoList|null
     * @throws \Exception
     */
    protected function createNewList(Request $request)
    {
        $todoList = new TodoList();
        $parameters = $request->request->all();

        return $this->processForm($todoList, $parameters, 'POST');
    }

    /**
     * Processes the form.
     *
     * @param \App\Entity\TodoList $todoList
     * @param array $parameters
     * @param string $method
     *
     * @return \App\Entity\TodoList|object
     * @throws \Exception
     */
    private function processForm(TodoList $todoList, array $parameters, $method = 'PUT')
    {
        $form = $this->createForm(TodoListType::class, $todoList, ['method' => $method]);

        $form->submit($parameters, 'PATCH' !== $method);

        if ($form->isValid()) {
            /** @var TodoList $todoList */
            $todoList = $form->getData();

            if ($todoList->getId() !== null) {
                $todoList = $this->em->merge($todoList);
            } else {
                $this->em->persist($todoList);
            }
            $this->em->flush();

            return $todoList;
        }

        throw new \Exception(\implode("|", $form->getErrors()));
    }
}