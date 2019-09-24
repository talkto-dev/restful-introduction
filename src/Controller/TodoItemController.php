<?php

namespace App\Controller;

use App\Entity\PaginatedCollection;
use App\Entity\TodoItem;
use App\Entity\TodoList;
use App\Factory\PaginationFactory;
use App\Form\TodoItemType;
use App\Repository\TodoItemRepository;
use App\Repository\TodoListRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Movie controller.
 * @Route("/todolist/{listCode}", name="api_")
 */
class TodoItemController extends ApiController
{
    /**
     * @Rest\Get("/item", name="get_item", options={ "method_prefix" = false })
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $listCode
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listItem(Request $request, string $listCode)
    {
        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [$e->getMessage()]
            );
        }

        $name = $request->query->get('name', '');

        /** @var TodoItemRepository $todoItemRepository */
        $todoItemRepository = $this->em->getRepository(TodoItem::class);
        $qb = $todoItemRepository->findAllQueryBuilder($list, $name);

        $page = $request->query->get('page', 1);

        /** @var PaginatedCollection $paginatedCollection */
        $paginatedCollection = $this->pagination
            ->createCollection($page, $qb, $request, 'api_get_item', ['listCode' => $listCode]);

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
     * @Rest\Get("/item/{code}")
     *
     * @param $listCode
     * @param $code
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getItem($listCode, $code)
    {

        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [$e->getMessage()]
            );
        }

        /** @var TodoItemRepository $todoItemRepository */
        $todoItemRepository = $this->em->getRepository(TodoItem::class);
        $todoItem = null;
        try {
            $todoItem = $todoItemRepository->findByCode($list, $code);
        } catch (\Exception $exception) {
            $todoItem = null;
        }

        if (!$todoItem) {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [sprintf('The \'%s\' todo list item resource was not found.', $code)]
            );
        }

        return $this->handleResponse(Response::HTTP_OK, $todoItem);
    }

    /**
     * @Rest\Post("/item")
     *
     * @param string $listCode
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postItem(string $listCode, Request $request)
    {
        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [$e->getMessage()]
            );
        }


        try {
            $todoItem = $this->createNewItem($request, $list);

            return $this->handleResponse(Response::HTTP_CREATED, $todoItem);
        } catch (\Exception $exception) {
            return $this->handleResponse(Response::HTTP_BAD_REQUEST, [], [$exception->getMessage()]);
        }
    }

    /**
     * @Rest\Put("/item/{code}")
     *
     * @param string $listCode
     * @param string $code
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putItem(string $listCode, string $code, Request $request)
    {
        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [$e->getMessage()]
            );
        }

        try {
            /** @var TodoItemRepository $todoListRepository */
            $todoListRepository = $this->em->getRepository(TodoItem::class);
            /** @var TodoItem $todoList */
            $todoList = $todoListRepository->findByCode($list, $code);
            if (!$todoList) {
                $statusCode = Response::HTTP_CREATED;
                $todoList = $this->createNewItem($request, $list);
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
     * Update TodoList
     * @Rest\Put("/item/{code}/status")
     *
     * @param string $listCode
     * @param string $code
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function statusItem(string $listCode, string $code, Request $request)
    {
        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(Response::HTTP_NOT_FOUND, [], [$e->getMessage()]);
        }


        /** @var TodoItemRepository $todoListRepository */
        $todoListRepository = $this->em->getRepository(TodoItem::class);
        /** @var TodoItem $todoList */
        $todoList = $todoListRepository->findByCode($list, $code);

        if ($todoList) {
            $todoList->setStatus($request->get('status'));
            $this->em->persist($todoList);
            $this->em->flush();

            return $this->handleResponse(Response::HTTP_OK, $todoList);
        }

        return $this->handleResponse(Response::HTTP_BAD_REQUEST, [], []);

    }

    /**
     * @Rest\Delete("/item/{code}")
     *
     * @param $listCode
     * @param $code
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function deleteItem($listCode, $code, Request $request)
    {
        try {
            $list = $this->getTodoList($listCode);
        } catch (\Exception $e) {
            return $this->handleResponse(Response::HTTP_NOT_FOUND, [], [$e->getMessage()]);
        }

        /** @var TodoItemRepository $todoItemRepository */
        $todoItemRepository = $this->em->getRepository(TodoItem::class);
        /** @var TodoList $todoList */
        $todoList = $todoItemRepository->findByCode($list, $code);

        if ($todoList) {
            $todoList->setDeleted(1);
            $this->em->persist($todoList);
            $this->em->flush();
        } else {
            return $this->handleResponse(
                Response::HTTP_NOT_FOUND,
                [],
                [sprintf('The \'%s\' todo list item resource was not found.', $code)]
            );
        }

        return $this->handleResponse(Response::HTTP_NO_CONTENT, []);
    }


    /**
     * @param Request $request
     * @param TodoList $list
     *
     * @return TodoItem|null
     * @throws \Exception
     */
    protected function createNewItem(Request $request, TodoList $list): ?TodoItem
    {
        $todoItem = new TodoItem();
        $todoItem->setList($list);
        $parameters = $request->request->all();

        return $this->processForm($todoItem, $parameters, 'POST');
    }

    /**
     * @param \App\Entity\TodoItem $todoItem
     * @param array $parameters
     * @param string $method
     *
     * @return \App\Entity\TodoItem|object
     * @throws \Exception
     */
    private function processForm(TodoItem $todoItem, array $parameters, $method = 'PUT')
    {
        $form = $this->createForm(TodoItemType::class, $todoItem, ['method' => $method]);

        $form->submit($parameters, 'PATCH' !== $method);

        if ($form->isValid()) {
            /** @var TodoItem $todoItem */
            $todoItem = $form->getData();

            if ($todoItem->getId() !== null) {
                $todoItem = $this->em->merge($todoItem);
            } else {
                $this->em->persist($todoItem);
            }
            $this->em->flush();

            return $todoItem;
        }

        throw new \Exception(\implode("|", $form->getErrors()));
    }


    /**
     * @param string $listCode
     *
     * @return TodoList|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function getTodoList(string $listCode): ?TodoList
    {
        /** @var TodoListRepository $todoListRepository */
        $todoListRepository = $this->em->getRepository(TodoList::class);
        $list = $todoListRepository->findByCode($listCode);

        if (!$list) {
            throw new NotFoundHttpException(sprintf('The \'%s\' todo list resource was not found.', $listCode));
        }

        return $list;
    }
}