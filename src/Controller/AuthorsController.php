<?php

namespace App\Controller;

use App\Entity\Authors;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AuthorsController extends AbstractController
{
    #[Route("/authors", methods: ('GET'))]
    public function index(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $surname = $request->query->get('surname');

        if ($surname) {
            $authors = $entityManager->getRepository(Authors::class)->createQueryBuilder('a')
                ->where('a.surname LIKE :surname')
                ->setParameter('Surname', '%' . $surname . '%')
                ->getQuery()
                ->getResult();
        } else {
            $authors = $entityManager->getRepository(Authors::class)->findAll();
        }


        if(empty($authors)){
        $authors = [
            "error" => [
                "code" => -60, 
                "message" => "There is no authors in DB" 
            ] 
            ];
        }

        return $this->json($authors);
    }

    #[Route("/authors", methods: ('POST'))]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }
    
        $authors = new Authors();
    
        if (isset($data['name'])) {
            $authors->setName($data['name']);
        } else {
            return $this->json(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }
    
        if (isset($data['surname'])) {
            $authors->setSurname($data['surname']);
        } else {
            return $this->json(['error' => 'Surname is required'], Response::HTTP_BAD_REQUEST);
        }
    
        if (isset($data['country'])) {
            $authors->setCountry($data['country']);
        } else {
            return $this->json(['error' => 'Country is required'], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($authors);
        $entityManager->flush();
    
        return $this->json($authors, Response::HTTP_CREATED);
    }

    #[Route("/authors/{id}", methods: ('DELETE'))]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $authors = $entityManager->getRepository(Authors::class)->find($id);

        if (!$authors) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($authors);
        $entityManager->flush();

        return $this->json(['message' => 'Book deleted']);
    }

    #[Route("/authors/{id}", methods: ('PUT'))]
    public function update(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $authors = $entityManager->getRepository(Authors::class)->find($id);

        if (!$authors) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['name'])) {
            $authors->setName($data['name']);
        }
        if (isset($data['surname'])) {
            $authors->setSurname($data['surname']);
        }
        if (isset($data['country'])) {
            $authors->setCountry($data['country']);
        }

        $entityManager->flush();

        return $this->json($authors);
    }

    #[Route("/authors/search", methods: ['POST'])]
    public function search(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $surname = $request->query->get('surname');
    
        if (strlen($surname) < 3) {
            return $this->json(['error' => 'Surname must have at least 3 characters'], Response::HTTP_BAD_REQUEST);
        }
    
        $authors = $entityManager->getRepository(Authors::class)->createQueryBuilder('a')
            ->where('a.surname LIKE :surname')
            ->setParameter('surname', '%' . $surname . '%')
            ->getQuery()
            ->getResult();
    
        if (empty($authors)) {
            $authors = [
                "error" => [
                    "code" => -60,
                    "message" => "There are no authors in the database"
                ]
            ];
        }
    
        return $this->json($authors);
    }
    
}
