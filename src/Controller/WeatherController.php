<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class WeatherController extends AbstractController
{

    public function __construct(#[Autowire('%env(WEATHER_API_KEY)%')] private string $weatherAPIKey) {}

    #[Route('/api/meteo/{ville}', name: 'cityCurrentWeather')]
    public function getCityCurrentWeather(string $ville, HttpClientInterface $httpClient): JsonResponse
    {

        $cityResponse = $httpClient->request(
            'GET',
            'http://api.openweathermap.org/geo/1.0/direct?q=' . $ville . '&limit=1&appid=' . $this->weatherAPIKey
        );

        $cityData = $cityResponse->toArray();

        if (empty($cityData)) {
            return new JsonResponse(['message' => "La ville est introuvable"], Response::HTTP_BAD_REQUEST);
        }

        $lat = $cityData[0]['lat'];
        $lon = $cityData[0]['lon'];


        $response = $httpClient->request(
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
    }

    #[Route('/api/meteo', name: 'currentWeather')]
    public function getCurrentWeather(HttpClientInterface $httpClient): JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            return $this->getCityCurrentWeather($user->getCity(), $httpClient);
        }

        return new JsonResponse(['message' => "La ville est introuvable"], Response::HTTP_BAD_REQUEST);
    }
}
