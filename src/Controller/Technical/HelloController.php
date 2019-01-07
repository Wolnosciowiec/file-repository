<?php declare(strict_types=1);

namespace App\Controller\Technical;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Lists all public routes
 */
class HelloController extends Controller
{
    public function sayHelloAction(): JsonResponse
    {
        return new JsonResponse(
            'Hello, welcome. Please take a look at /repository/routing/map for the list of available routes.',
            JsonResponse::HTTP_OK
        );
    }
}