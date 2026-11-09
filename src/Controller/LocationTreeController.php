<?php

namespace App\Controller;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Entity\Sector;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ubicaciones')]
class LocationTreeController extends AbstractController
{
    #[Route('/', name: 'app_ubicaciones_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = trim($request->request->get('nombre', ''));
            if ($nombre !== '') {
                $estado = new Estado();
                $estado->setNombre($nombre);
                $em->persist($estado);
                $em->flush();
                return $this->redirectToRoute('app_ubicaciones_index');
            }
        }

        $estados = $em->getRepository(Estado::class)->findBy([], ['nombre' => 'ASC']);
        return $this->render('ubicaciones/index.html.twig', ['estados' => $estados]);
    }

    #[Route('/estado/{id}/municipios', name: 'app_ubicaciones_municipios', methods: ['GET', 'POST'])]
    public function municipios(Request $request, Estado $estado, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = trim($request->request->get('nombre', ''));
            if ($nombre !== '') {
                $mun = new Municipio();
                $mun->setNombre($nombre);
                $mun->setEstado($estado);
                $em->persist($mun);
                $em->flush();
            }
        }
        return $this->render('ubicaciones/_municipios.html.twig', ['estado' => $estado]);
    }

    #[Route('/municipio/{id}/parroquias', name: 'app_ubicaciones_parroquias', methods: ['GET', 'POST'])]
    public function parroquias(Request $request, Municipio $municipio, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = trim($request->request->get('nombre', ''));
            if ($nombre !== '') {
                $par = new Parroquia();
                $par->setNombre($nombre);
                $par->setMunicipio($municipio);
                $em->persist($par);
                $em->flush();
            }
        }
        return $this->render('ubicaciones/_parroquias.html.twig', ['municipio' => $municipio]);
    }

    #[Route('/parroquia/{id}/sectores', name: 'app_ubicaciones_sectores', methods: ['GET', 'POST'])]
    public function sectores(Request $request, Parroquia $parroquia, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = trim($request->request->get('nombre', ''));
            if ($nombre !== '') {
                $sec = new Sector();
                $sec->setNombre($nombre);
                $sec->setParroquia($parroquia);
                $em->persist($sec);
                $em->flush();
            }
        }
        return $this->render('ubicaciones/_sectores.html.twig', ['parroquia' => $parroquia]);
    }

    #[Route('/rename/{type}/{id}', name: 'app_ubicaciones_rename', methods: ['POST'])]
    public function rename(string $type, int $id, Request $request, EntityManagerInterface $em): Response
    {
        $nombre = trim($request->request->get('nombre', ''));
        if ($nombre === '') {
            return $this->json(['success' => false, 'message' => 'El nombre no puede estar vacío.'], Response::HTTP_BAD_REQUEST);
        }

        $entityClass = match ($type) {
            'estado' => Estado::class,
            'municipio' => Municipio::class,
            'parroquia' => Parroquia::class,
            'sector' => Sector::class,
            default => null,
        };

        if (!$entityClass) {
            return $this->json(['success' => false, 'message' => 'Tipo de entidad inválido.'], Response::HTTP_BAD_REQUEST);
        }

        $entity = $em->getRepository($entityClass)->find($id);
        if (!$entity) {
            return $this->json(['success' => false, 'message' => 'Entidad no encontrada.'], Response::HTTP_NOT_FOUND);
        }

        $entity->setNombre($nombre);
        $em->flush();

        return $this->json([
            'success' => true,
            'id' => $id,
            'nombre' => $entity->getNombre()
        ]);
    }
}
