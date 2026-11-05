<?php

namespace App\Services;

use App\Models\Visite;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    /**
     * Ajoute après coup la météo à une visite, d'après sa date et la position du rucher.
     * Une visite notée sans réseau n'a pas pu la récupérer au rucher.
     */
    public static function completeVisite(Visite $visite): void
    {
        $rucher = $visite->ruche?->rucher;

        if (! $rucher || $rucher->latitude === null || $rucher->longitude === null || $visite->meteo !== null) {
            return;
        }

        $date = $visite->date_visite->toDateString();
        // Sans heure notée, le milieu de journée reste le plus représentatif d'une visite.
        $hour = $visite->heure_debut ? (int) substr((string) $visite->heure_debut, 0, 2) : 12;

        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => (float) $rucher->latitude,
                'longitude' => (float) $rucher->longitude,
                'hourly' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code',
                'timezone' => 'Europe/Paris',
                'start_date' => $date,
                'end_date' => $date,
            ]);

            $hourly = $response->successful() ? $response->json('hourly') : null;

            if (! isset($hourly['temperature_2m'][$hour], $hourly['weather_code'][$hour])) {
                return;
            }

            $visite->forceFill([
                'temperature' => round((float) $hourly['temperature_2m'][$hour], 1),
                'humidite' => (int) ($hourly['relative_humidity_2m'][$hour] ?? 0),
                'force_vent' => self::mapWindSpeed((float) ($hourly['wind_speed_10m'][$hour] ?? 0)),
                'meteo' => self::mapWeatherCode((int) $hourly['weather_code'][$hour]),
            ])->saveQuietly();
        } catch (\Throwable $e) {
            // La météo est un bonus : son absence ne doit jamais faire échouer un envoi.
            Log::warning('Météo indisponible', ['visite' => $visite->id, 'erreur' => $e->getMessage()]);
        }
    }

    private static function mapWeatherCode(int $code): string
    {
        return match (true) {
            $code <= 3 => 'ensoleille',
            $code <= 48 => 'nuageux',
            $code >= 95 => 'orageux',
            $code <= 82 => 'pluvieux',
            default => 'nuageux',
        };
    }

    private static function mapWindSpeed(float $windSpeedKmh): string
    {
        return match (true) {
            $windSpeedKmh < 5 => 'nul',
            $windSpeedKmh < 20 => 'faible',
            $windSpeedKmh < 40 => 'modere',
            default => 'fort',
        };
    }
}
