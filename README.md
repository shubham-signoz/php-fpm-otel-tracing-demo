# PHP-FPM OpenTelemetry Auto-Instrumentation Example

This example runs:
- `nginx` -> `php-fpm` (Slim app)
- OpenTelemetry PHP extension + Slim auto instrumentation
- Direct OTLP export to Signoz Cloud (`OTEL_EXPORTER_OTLP_ENDPOINT`)

## What It Demonstrates

- Auto-generated traces from incoming HTTP requests in PHP-FPM.
- Trace export only via OTLP.

## Stack Files

- `docker-compose.yml`: app + nginx (direct OTLP to Signoz)
- `runtime/Dockerfile`: PHP-FPM image with `opentelemetry` extension and Composer deps.
- `nginx/Dockerfile`: Nginx image with PHP-FPM vhost config and app files.
- `runtime/zz-otel.conf`: `clear_env = no` so `OTEL_*` env vars reach FPM workers.
- `app/public/index.php`: Slim routes used to trigger spans.

## Quick Start

```bash
cd /Users/smartass08/signoz/example_doc_test/php-fpm
cp .env.example .env
# Edit .env and set:
# - OTEL_EXPORTER_OTLP_ENDPOINT (your Signoz ingest URL)
# - OTEL_EXPORTER_OTLP_HEADERS with your signoz-ingestion-key (e.g., signoz-ingestion-key=<YOUR_KEY>)
# Compose loads this with:
env_file: .env
docker compose up --build
```

In another terminal:

```bash
curl http://localhost:8080/
curl http://localhost:8080/hello/alice
curl -i http://localhost:8080/error
```

## Verification Checklist

1. Verify traces appear in Signoz Cloud under service `php-fpm-slim-auto`.

```bash
docker compose logs -f app
```

The app logs also show request traffic when endpoints are hit.

## Notes

- `.env` should contain your real endpoint/keys and is ignored by git.
- `.env.example` is sample-only with placeholders and no secrets.
- `OTEL_TRACES_EXPORTER` is set to `otlp`, `OTEL_METRICS_EXPORTER` and `OTEL_LOGS_EXPORTER` are set to `none`.
- This example currently exports directly to Signoz Cloud. The collector is kept in this repo for optional local-forwarding validation.

### Local Collector Mode (Optional)

This mode is useful for local troubleshooting because the local collector can print incoming spans to its console for easy verification.

Use this full compose file to route traces through the local collector:

```yaml
services:
  app:
    build:
      context: .
      dockerfile: runtime/Dockerfile
    env_file:
      - .env
    environment:
      OTEL_PHP_AUTOLOAD_ENABLED: "true"
      OTEL_SERVICE_NAME: php-fpm-slim-auto
      OTEL_TRACES_EXPORTER: otlp
      OTEL_METRICS_EXPORTER: none
      OTEL_LOGS_EXPORTER: none
      OTEL_EXPORTER_OTLP_PROTOCOL: http/protobuf
      OTEL_EXPORTER_OTLP_ENDPOINT: http://otel-collector:4318
      OTEL_EXPORTER_OTLP_HEADERS: ""
      OTEL_PROPAGATORS: baggage,tracecontext
    depends_on:
      - otel-collector

  nginx:
    build:
      context: .
      dockerfile: nginx/Dockerfile
    depends_on:
      - app
    ports:
      - "8080:8080"

  otel-collector:
    build:
      context: .
      dockerfile: collector/Dockerfile
    command: ["--config=/etc/otelcol-contrib/config.yaml"]
```

Then tail collector logs with:

```bash
docker compose logs -f otel-collector
```

## Useful Debug Commands

```bash
docker compose exec app php -m | grep -i opentelemetry
docker compose exec app php -i | grep -E "OTEL_|opentelemetry"
docker compose logs app
```
