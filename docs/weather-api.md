# Weather REST API Documentation

## Overview

The WP ERP Weather integration fetches forecast data from the [Open-Meteo API](https://open-meteo.com/) and serves it via a REST endpoint. You choose which hourly weather variables to fetch per request. Data is cached per location, temperature unit, and requested variables to minimize external API calls.

## Base URL

```
/wp-json/erp/v1/weather
```

## Authentication

Requires a logged-in WordPress user with the `erp_view_list` capability. Authenticate using any standard WordPress REST API authentication method (cookie, application password, or nonce).

---

## GET /erp/v1/weather

Retrieve weather forecast data for a specific location.

### Query Parameters

| Parameter          | Type   | Required | Default                                              | Description                                                                 |
|--------------------|--------|----------|------------------------------------------------------|-----------------------------------------------------------------------------|
| `latitude`         | number | No       | Settings default                                     | Latitude coordinate (WGS84). Range: `-90` to `90`.                          |
| `longitude`        | number | No       | Settings default                                     | Longitude coordinate (WGS84). Range: `-180` to `180`.                       |
| `temperature_unit` | string | No       | Settings default                                     | Temperature unit. Accepted: `celsius`, `fahrenheit`.                         |
| `hourly`           | string | No       | `temperature_2m,apparent_temperature,weather_code`   | Comma-separated list of hourly weather variables to fetch (see table below). |

If `latitude` and `longitude` are not provided in the request, the defaults configured in **ERP > Settings > Integrations > Open-Meteo Weather** are used. If neither the request nor settings provide coordinates, a `400` error is returned.

### Available Hourly Variables

| Variable               | Description                              |
|------------------------|------------------------------------------|
| `temperature_2m`       | Air temperature at 2m height.            |
| `apparent_temperature` | Feels-like temperature.                  |
| `weather_code`         | WMO weather interpretation code (0-99).  |
| `relative_humidity_2m` | Relative humidity at 2m height.          |
| `wind_speed_10m`       | Wind speed at 10m height.                |
| `wind_direction_10m`   | Wind direction at 10m height in degrees. |
| `wind_gusts_10m`       | Wind gusts at 10m height.                |
| `precipitation`        | Total precipitation (rain + snow).       |
| `rain`                 | Rain amount.                             |
| `showers`              | Showers amount.                          |
| `snowfall`             | Snowfall amount.                         |
| `cloud_cover`          | Total cloud cover percentage.            |
| `pressure_msl`         | Mean sea level pressure.                 |
| `surface_pressure`     | Surface pressure.                        |
| `uv_index`             | UV index.                                |
| `visibility`           | Viewing distance in meters.              |
| `dew_point_2m`         | Dew point at 2m height.                  |

Only the variables you request are returned in the response. Unrecognized variables are silently ignored.

### Example Requests

**Default variables (temperature, apparent temperature, weather code):**

```bash
curl -X GET \
  "https://example.com/wp-json/erp/v1/weather?latitude=23.84&longitude=90.38&temperature_unit=celsius" \
  -H "X-WP-Nonce: <nonce>"
```

**Custom variables:**

```bash
curl -X GET \
  "https://example.com/wp-json/erp/v1/weather?latitude=23.84&longitude=90.38&hourly=temperature_2m,apparent_temperature,weather_code&temperature_unit=celsius" \
  -H "X-WP-Nonce: <nonce>"
```

**With wind and humidity:**

```bash
curl -X GET \
  "https://example.com/wp-json/erp/v1/weather?latitude=52.52&longitude=13.41&hourly=temperature_2m,weather_code,wind_speed_10m,relative_humidity_2m&temperature_unit=celsius" \
  -H "X-WP-Nonce: <nonce>"
```

### Success Response (200 OK)

```json
{
  "latitude": 23.875,
  "longitude": 90.375,
  "elevation": 9.0,
  "timezone": "Asia/Dhaka",
  "temperature_unit": "°C",
  "fetched_at": "2026-04-06T12:00:00+00:00",
  "source": "api",
  "hourly": {
    "time": [
      "2026-04-06T00:00",
      "2026-04-06T01:00",
      "..."
    ],
    "temperature_2m": [28.2, 27.9, "..."],
    "apparent_temperature": [31.1, 30.8, "..."],
    "weather_code": [3, 2, "..."]
  },
  "hourly_units": {
    "time": "iso8601",
    "temperature_2m": "°C",
    "apparent_temperature": "°C",
    "weather_code": "wmo code"
  }
}
```

### Response Fields

| Field              | Type     | Description                                                              |
|--------------------|----------|--------------------------------------------------------------------------|
| `latitude`         | number   | Actual latitude used by Open-Meteo (may differ slightly from input).     |
| `longitude`        | number   | Actual longitude used by Open-Meteo.                                     |
| `elevation`        | number   | Elevation in meters above sea level.                                     |
| `timezone`         | string   | Timezone of the location (IANA format).                                  |
| `temperature_unit` | string   | Temperature unit symbol used in the response (e.g., `°C`, `°F`).        |
| `fetched_at`       | string   | ISO 8601 timestamp of when the data was fetched from the upstream API.   |
| `source`           | string   | `"api"` if freshly fetched, `"cache"` if served from cache.             |
| `hourly`           | object   | Hourly forecast data. Only contains the variables you requested.         |
| `hourly_units`     | object   | Units for each hourly variable in the response.                          |

### WMO Weather Codes

The `weather_code` variable returns [WMO weather interpretation codes](https://open-meteo.com/en/docs):

| Code | Description          | Code | Description              |
|------|----------------------|------|--------------------------|
| 0    | Clear sky            | 51   | Light drizzle            |
| 1    | Mainly clear         | 53   | Moderate drizzle         |
| 2    | Partly cloudy        | 55   | Dense drizzle            |
| 3    | Overcast             | 61   | Slight rain              |
| 45   | Fog                  | 63   | Moderate rain            |
| 48   | Depositing rime fog  | 65   | Heavy rain               |
| 71   | Slight snowfall      | 80   | Slight rain showers      |
| 73   | Moderate snowfall    | 81   | Moderate rain showers    |
| 75   | Heavy snowfall       | 82   | Violent rain showers     |
| 77   | Snow grains          | 95   | Thunderstorm             |
| 85   | Slight snow showers  | 96   | Thunderstorm + slight hail |
| 86   | Heavy snow showers   | 99   | Thunderstorm + heavy hail  |

Forecast data covers **7 days** with hourly resolution (168 data points per variable).

---

## Error Responses

### 400 Bad Request — Missing Coordinates

```json
{
  "code": "erp_weather_missing_coords",
  "message": "Latitude and longitude are required. Provide them as query parameters or configure defaults in settings.",
  "data": { "status": 400 }
}
```

### 400 Bad Request — Invalid Coordinates

```json
{
  "code": "erp_weather_invalid_coords",
  "message": "Invalid coordinates. Latitude must be between -90 and 90, longitude between -180 and 180.",
  "data": { "status": 400 }
}
```

### 403 Forbidden — Integration Disabled

```json
{
  "code": "erp_weather_disabled",
  "message": "The Open-Meteo weather integration is not enabled.",
  "data": { "status": 403 }
}
```

### 502 Bad Gateway — Upstream API Error

```json
{
  "code": "erp_weather_upstream_error",
  "message": "Failed to connect to the Open-Meteo API.",
  "data": { "status": 502 }
}
```

---

## Caching Behavior

- Each unique combination of `latitude`, `longitude`, `temperature_unit`, and `hourly` variables is cached independently.
- Coordinates are rounded to 2 decimal places for cache key generation (e.g., `52.5234` and `52.5289` both map to `52.52`).
- Cache TTL is configurable in settings: **30 minutes**, **1 hour** (default), or **24 hours**.
- The `source` field in the response indicates whether data was served from `"cache"` or freshly fetched from the `"api"`.
- A cron job pre-warms the cache for the default location daily.
- Requesting different `hourly` variable combinations for the same location creates separate cache entries.

---

## Settings

Configure the integration at **ERP > Settings > Integrations > Open-Meteo Weather**:

| Setting               | Description                                                    |
|-----------------------|----------------------------------------------------------------|
| Enable                | Toggle the weather integration on/off.                         |
| API Tier              | `Free` (public API) or `Paid` (commercial API with API key).   |
| API Key               | Required when API Tier is `Paid`. Hidden when `Free`.          |
| Default Latitude      | Fallback latitude when not provided in the request.            |
| Default Longitude     | Fallback longitude when not provided in the request.           |
| Default Temp Unit     | Fallback temperature unit (`Celsius` or `Fahrenheit`).         |
| Fetch Interval        | Cache TTL: `Every 30 Minutes`, `Hourly`, or `Daily`.           |

---

## API Tiers

| Tier | Base URL                                             | API Key Required |
|------|------------------------------------------------------|------------------|
| Free | `https://api.open-meteo.com/v1/forecast`             | No               |
| Paid | `https://customer-api.open-meteo.com/v1/forecast`    | Yes              |

The free tier has rate limits imposed by Open-Meteo. The paid tier supports higher request volumes and is intended for commercial use. See [Open-Meteo Pricing](https://open-meteo.com/en/pricing) for details.
