<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BookController extends AbstractController
{
    #[Route("/books", methods: ('GET'))]
    public function index(EntityManagerInterface $entityManager): JsonResponse
    {
        $books = $entityManager->getRepository(Book::class)->findAll();

        //var_dump($this->json($books));

        if(empty($books)){
        $books = [
            "error" => [
                "code" => -60, 
                "message" => "There is no books in DB" 
            ] 
            ];
        }

        return $this->json($books);
    }

    #[Route("/books", methods: ('POST'))]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
        }
    
        $book = new Book();
    
        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        } else {
            return $this->json(['error' => 'Title is required'], Response::HTTP_BAD_REQUEST);
        }
    
        if (isset($data['publisher'])) {
            $book->setPublisher($data['publisher']);
        } else {
            return $this->json(['error' => 'Publisher is required'], Response::HTTP_BAD_REQUEST);
        }
    
        if (isset($data['page_count'])) {
            $book->setPagesCount($data['page_count']);
        } else {
            return $this->json(['error' => 'Page count is required'], Response::HTTP_BAD_REQUEST);
        }
    
        $book->setIsPublic(false);
    
        $entityManager->persist($book);
        $entityManager->flush();
    
        return $this->json($book, Response::HTTP_CREATED);
    }

    #[Route("/books/{id}", methods: ('DELETE'))]
    public function delete(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($book);
        $entityManager->flush();

        return $this->json(['message' => 'Book deleted']);
    }

    #[Route("/books/{id}", methods: ('PUT'))]
    public function update(Request $request, int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $book = $entityManager->getRepository(Book::class)->find($id);

        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        }
        if (isset($data['publisher'])) {
            $book->setPublisher($data['publisher']);
        }
        if (isset($data['page_count'])) {
            $book->setPagesCount($data['page_count']);
        }

        $entityManager->flush();

        return $this->json($book);
    }

    #[Route("/books/{id}/add-author", methods: ['POST'])]
    public function addAuthorToBook(Request $request, EntityManagerInterface $entityManager, int $id): JsonResponse
    {
        $book = $entityManager->getRepository(Book::class)->find($id);
        $authorId = $request->request->get('author_id');

        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $author = $entityManager->getRepository(Authors::class)->find($authorId);

        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        // Sprawdź, czy autor już nie jest przypisany do książki
        if ($book->getAuthors()->contains($author)) {
            return $this->json(['error' => 'Author is already assigned to the book'], Response::HTTP_BAD_REQUEST);
        }

        // Dodaj autora do kolekcji autorów przypisanych do książki
        $book->addAuthor($author);

        $entityManager->flush();

        return $this->json(['message' => 'Author added to the book']);
    }
}
