<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Récupérer les données météo pour une position géographique
     * Utilise l'API OpenWeatherMap One Call (gratuite)
     */
    public static function getCurrentWeather(float $latitude, float $longitude): ?array
    {
        try {
            // API gratuite open-meteo.com (pas besoin de clé)
            $response = Http::timeout(10)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code',
                'timezone' => 'Europe/Paris',
                'forecast_days' => 1
            ]);

            if (!$response->successful()) {
                Log::warning('Erreur API météo', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['current'])) {
                return null;
            }

            $current = $data['current'];
            
            return [
                'temperature' => round($current['temperature_2m'], 1),
                'humidity' => $current['relative_humidity_2m'],
                'wind_speed' => $current['wind_speed_10m'], // km/h
                'weather_code' => $current['weather_code'],
                'meteo' => self::mapWeatherCode($current['weather_code']),
                'force_vent' => self::mapWindSpeed($current['wind_speed_10m']),
            ];

        } catch (\Exception $e) {
            Log::error('Erreur récupération météo', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Convertir le code météo en valeur de notre ENUM
     * Codes météo open-meteo.com
     */
    private static function mapWeatherCode(int $code): string
    {
        // Codes météo WMO standardisés
        if ($code <= 3) {
            return 'ensoleille'; // Clear sky, mainly clear, partly cloudy
        }
        
        if ($code <= 48) {
            return 'nuageux'; // Overcast, fog
        }
        
        if ($code <= 67) {
            return 'pluvieux'; // Rain
        }
        
        if ($code <= 82) {
            return 'pluvieux'; // Rain showers
        }
        
        if ($code >= 95) {
            return 'orageux'; // Thunderstorm
        }
        
        return 'nuageux'; // Par défaut
    }

    /**
     * Convertir la vitesse du vent en valeur de notre ENUM
     */
    private static function mapWindSpeed(float $windSpeedKmh): string
    {
        if ($windSpeedKmh < 5) {
            return 'nul';
        }
        
        if ($windSpeedKmh < 20) {
            return 'faible';
        }
        
        if ($windSpeedKmh < 40) {
            return 'modere';
        }
        
        return 'fort';
    }

    /**
     * Récupérer la météo pour un rucher spécifique
     */
    public static function getWeatherForRucher(\App\Models\Rucher $rucher): ?array
    {
        if (!$rucher->latitude || !$rucher->longitude) {
            return null;
        }

        return self::getCurrentWeather($rucher->latitude, $rucher->longitude);
    }
}