<?php

namespace App\Controller;

use Symfony\UX\Map\Map;
use Symfony\UX\Map\Point;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\InfoWindow;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient, 
    ) {}

    #[Route('/')]
    public function __invoke(): Response
    {
        // Create a new map instance
        $myMap = (new Map())
            // Explicitly set the center and zoom
            ->center(new Point(46.903354, 1.888334))
            ->zoom(6)
            // Or automatically fit the bounds to the markers
            ->fitBoundsToMarkers()
        ;

        // Get all sports spots Paris 2024 OG
        // $response = $this->httpClient->request('GET', 'https://www.data.gouv.fr/fr/datasets/r/1d61b1f4-4730-4dfa-aa44-34220f67f493');
        $response = $this->httpClient->request('GET', 'http://localhost:3000/api/poi');
        $points = json_decode($response->getContent(), true);
        // dd($points);

        foreach($points as $record) {

            $myMap->addMarker(new Marker(
                position: new Point(
                    // $record['point_geo']['lat'],
                    // $record['point_geo']['lon'],
                    $record['latitude'],
                    $record['longitude'],
                ), 
                // title: $record['nom_site'],
                title: $record['name'],
                // infoWindow: new InfoWindow(
                //     headerContent: '<b>'.$record['nom_site'].' - Du '.$record['start_date'].' au '.$record['end_date'].'</b>',
                //     content: $record['sports']
                // )
                infoWindow: new InfoWindow(
                    headerContent: '<h2>'.$record['city'].' - '.$record['name'].'</h2>',
                    content: '<p>'.$record['description'].'</p>'
                ),
            ));
        }

        // Inject the map in your template to render it
        return $this->render('home/index.html.twig', [
            'my_map' => $myMap,
            'data' => $points
        ]);
    }
}
