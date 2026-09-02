<?php

namespace App\Controller;

use App\Entity\Estado;
use App\Entity\Municipio;
use App\Entity\Parroquia;
use App\Entity\Sector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controlador para obtener ubicaciones por ajax usando stimulus
 */
#[Route('/api/location', name: 'api_location_')]
final class LocationApiController extends AbstractController
{
    #[Route('/municipios/{id}', name: 'municipios', methods: ['GET'])]
    public function getMunicipios(Estado $estado): JsonResponse
    {
        $data = [];
        foreach ($estado->getMunicipios() as $municipio) {
            $data[] = ['id' => $municipio->getId(), 'nombre' => $municipio->getNombre()];
        }
        return $this->json($data);
    }

    #[Route('/parroquias/{id}', name: 'parroquias', methods: ['GET'])]
    public function getParroquias(Municipio $municipio): JsonResponse
    {
        $data = [];
        foreach ($municipio->getParroquias() as $parroquia) {
            $data[] = ['id' => $parroquia->getId(), 'nombre' => $parroquia->getNombre()];
        }
        return $this->json($data);
    }

    #[Route('/sectores/{id}', name: 'sectores', methods: ['GET'])]
    public function getSectores(Parroquia $parroquia): JsonResponse
    {
        $data = [];
        foreach ($parroquia->getSectores() as $sector) {
            $data[] = ['id' => $sector->getId(), 'nombre' => $sector->getNombre()];
        }
        return $this->json($data);
    }
}
