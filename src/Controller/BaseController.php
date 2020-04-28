<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BaseController extends AbstractController
{
    /**
     * @Route("/api/movie", methods={"GET"}, name="movie_get")
     */
    public function indexGet(MovieRepository $movieRepository)
    {
        $movies = $movieRepository->findAll();
        return $this->json($movies, 200, [], ['groups' => 'movie:read']);
    }

    /**
     * @Route("/api/movie", methods={"POST"}, name="movie_post")
     * @isGranted("ROLE_SUPER_ADMIN")
     */
    public function indexPost(EntityManagerInterface $em, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validator)
    {
        try {
            $jsonReceived = $request->getContent();

            $movie = $serializerInterface->deserialize($jsonReceived, Movie::class, 'json', ['groups' => 'movie:read']);
            $errors = $validator->validate($movie);
            if (count($errors) > 0) {
                return $this->json($errors, 400);
            }
            $em->persist($movie);
            $em->flush();
            return $this->json($movie, 200, [], ['groups' => 'movie:read']);
        } catch (NotEncodableValueException $e) {
            return $this->json(['code' => 400, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * @Route("/api/movie/{id}", methods={"DELETE"}, name="movie_delete")
     */
    public function indexDelete(EntityManagerInterface $em, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validator, int $id)
    {
        $movie = $em->find(Movie::class, $id);
        $em->remove($movie);
        $em->flush();
        return $this->json($movie, 200);
    }

    /**
     * @Route("/api/movie/{id}", methods={"PUT"}, name="movie_update")
     */
    public function indexUpdate(EntityManagerInterface $em, Request $request, SerializerInterface $serializerInterface, ValidatorInterface $validator, int $id)
    {
        $movie = $em->find(Movie::class, $id);
        $jsonReceived = $request->getContent();
        $movieNew = $serializerInterface->deserialize($jsonReceived, Movie::class, 'json', ['groups' => 'movie:read']);

        $movie->setTitle($movieNew->getTitle());
        $movie->setRate($movieNew->getRate());
        $movie->setStock($movieNew->getStock());
        $em->flush();
        return $this->json($movie, 200, [], ['groups' => "movie:read"]);
    }
}
