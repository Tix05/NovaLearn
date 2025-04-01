import React, { useState, useEffect } from 'react';
import { Cloud, CloudRain, Sun, Wind } from 'lucide-react';

function Weather() {
    const [weather, setWeather] = useState(null);
    const [error, setError] = useState('');
    const [city, setCity] = useState('Antananarivo');
    const [isGeolocationLoading, setIsGeolocationLoading] = useState(true);

    const API_KEY = '84fab37fb2319645a0aef2d253643da8';

    useEffect(() => {
        const getUserLocation = () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        try {
                            const response = await fetch(
                                `https://api.openweathermap.org/geo/1.0/reverse?lat=${position.coords.latitude}&lon=${position.coords.longitude}&limit=1&appid=${API_KEY}`
                            );

                            if (!response.ok) {
                                throw new Error('Erreur lors de la récupération de la localisation');
                            }

                            const locationData = await response.json();
                            if (locationData && locationData.length > 0) {
                                setCity(locationData[0].name);
                            }
                        } catch (err) {
                            console.error('Erreur:', err);
                            setError('Impossible de déterminer votre ville, utilisation de la ville par défaut');
                        } finally {
                            setIsGeolocationLoading(false);
                        }
                    },
                    (error) => {
                        console.error('Erreur de géolocalisation:', error);
                        setError('La géolocalisation est désactivée. Utilisation de la ville par défaut.');
                        setIsGeolocationLoading(false);
                    }
                );
            } else {
                setError('La géolocalisation n\'est pas supportée par votre navigateur. Utilisation de la ville par défaut.');
                setIsGeolocationLoading(false);
            }
        };

        getUserLocation();
    }, []);

    useEffect(() => {
        if (!isGeolocationLoading) {
            fetchWeather();
        }
    }, [city, isGeolocationLoading]);

    const fetchWeather = async () => {
        try {
            const response = await fetch(
                `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${API_KEY}&units=metric&lang=fr`
            );

            if (!response.ok) {
                throw new Error('Erreur réseau');
            }

            const currentWeather = await response.json();
            const forecastResponse = await fetch(
                `https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${API_KEY}&units=metric&lang=fr`
            );

            if (!forecastResponse.ok) {
                throw new Error('Erreur réseau pour les prévisions');
            }

            const forecastData = await forecastResponse.json();

            setWeather({
                current: currentWeather,
                forecast: forecastData
            });
            setError('');
        } catch (err) {
            console.error('Erreur:', err);
            setError('Erreur lors de la récupération des données météo');
        }
    };

    const getWeatherIcon = (condition) => {
        switch (condition) {
            case 'Rain':
                return <CloudRain className="w-16 h-16 text-white" />;
            case 'Clouds':
                return <Cloud className="w-16 h-16 text-white" />;
            default:
                return <Sun className="w-16 h-16 text-white" />;
        }
    };

    if (isGeolocationLoading) {
        return (
            <div className=" bg-gradient-to-br w-[40vw] flex items-center justify-center">
                <p className="text-gray-800 text-xl">Détection de votre localisation...</p>
            </div>
        );
    }

    if (error && !weather) {
        return (
            <div className=" bg-gradient-to-br w-[40vw] flex items-center justify-center">
                <p className="text-gray-800 text-xl">{error}</p>
            </div>
        );
    }

    if (!weather) {
        return (
            <div className=" bg-gradient-to-br w-[40vw] flex items-center justify-center">
                <p className="text-gray-800 text-xl">Chargement des données météo...</p>
            </div>
        );
    }

    return (
        <div className=" bg-gradient-to-br p-4">
            {error && (
                <div className="max-w-md mx-auto mb-4 p-4 bg-yellow-500 text-white rounded-lg">
                    {error}
                </div>
            )}
            <div className="max-w-md mx-auto bg-blue-500 rounded-lg shadow-xl overflow-hidden">
                <div className="p-4">
                    <div className="text-center mb-4">
                        <h1 className="text-4xl font-bold text-white mb-2">{city}</h1>
                        <p className="text-blue-100">
                            {new Date().toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })}
                        </p>
                    </div>

                    <div className="flex items-center justify-center mb-4">
                        {getWeatherIcon(weather.current.weather[0].main)}
                        <div className="ml-6">
                            <p className="text-6xl font-bold text-white">
                                {Math.round(weather.current.main.temp)}°C
                            </p>
                            <p className="text-xl text-blue-100 capitalize">
                                {weather.current.weather[0].description}
                            </p>
                        </div>
                    </div>

                    <div className="flex justify-between text-blue-100 mb-4">
                        <div className="text-center">
                            <Wind className="w-6 h-6 mx-auto mb-2" />
                            <p>Vent</p>
                            <p className="font-bold">{Math.round(weather.current.wind.speed)} km/h</p>
                        </div>
                        <div className="text-center">
                            <div className="w-6 h-6 mx-auto mb-2">💧</div>
                            <p>Humidité</p>
                            <p className="font-bold">{weather.current.main.humidity}%</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-4 gap-3">
                        {weather.forecast.list
                            .filter((_, index) => index % 8 === 0)
                            .slice(0, 4)
                            .map((day, index) => (
                                <div key={index} className="text-center text-white p-2 rounded-lg bg-blue-400">
                                    <p className="text-sm mb-2">
                                        {new Date(day.dt * 1000).toLocaleDateString('fr-FR', { weekday: 'short' })}
                                    </p>
                                    {getWeatherIcon(day.weather[0].main)}
                                    <p className="font-bold mt-2">{Math.round(day.main.temp)}°C</p>
                                </div>
                            ))}
                    </div>
                </div>
            </div>
        </div>
    );
}

export default Weather;