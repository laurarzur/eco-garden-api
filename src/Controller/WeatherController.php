<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WeatherController extends AbstractController
{

    public function __construct(#[Autowire('%env(WEATHER_API_KEY)%')] private string $weatherAPIKey, private HttpClientInterface $httpClient, private TagAwareCacheInterface $cache) {}

    #[Route('/api/meteo/{ville}', name: 'cityCurrentWeather')]
    public function getCityCurrentWeather(string $ville): JsonResponse
    {
        $idCache = "meteo-" . $ville;

        $cityCurrentWeather = $this->cache->get($idCache, function (ItemInterface $item) use ($ville) {
            echo ("Pas en cache\n");
            $item->tag("citiesCache");
            $item->expiresAfter(3600);

            $cityResponse = $this->httpClient->request(
                'GET',
                'http://api.openweathermap.org/geo/1.0/direct?q=' . $ville . '&limit=1&appid=' . $this->weatherAPIKey
            );

            $cityData = $cityResponse->toArray();

            if (empty($cityData)) {
                return new JsonResponse(['message' => "La ville est introuvable"], Response::HTTP_BAD_REQUEST);
            }

            $lat = $cityData[0]['lat'];
            $lon = $cityData[0]['lon'];


            $response = $this->httpClient->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather?lat=' . $lat . '&lon=' . $lon . '&units=metric&lang=fr&appid=' . $this->weatherAPIKey
            );

            $weatherData = $response->toArray();

            $filteredWeatherData = [
                'city' => $weatherData['name'] ?? null,
                'country' => $weatherData['sys']['country'] ?? null,
                'description' => $weatherData['weather'][0]['description'] ?? null,
                'temperature' => $weatherData['main']['temp'] ?? null,
                'feelsLike' => $weatherData['main']['feels_like'] ?? null,
                'humidity' => $weatherData['main']['humidity'] ?? null,
                'windSpeed' => $weatherData['wind']['speed'] ?? null
            ];

            return new JsonResponse($filteredWeatherData, $response->getStatusCode());
        });

        return $cityCurrentWeather;
    }

    #[Route('/api/meteo', name: 'currentWeather')]
    public function getCurrentWeather(): JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            return $this->getCityCurrentWeather($user->getCity());
        }

        return new JsonResponse(['message' => "La ville est introuvable"], Response::HTTP_BAD_REQUEST);
    }
}
