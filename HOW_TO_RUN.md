# HOW TO RUN LOCALLY

## Quick Start

1. **Start all services**:
   ```bash
   docker compose up --build
   ```

2. **Initialize database** (in a new terminal):
   ```bash
   docker compose exec php sh scripts/init_db.sh
   ```

3. **Access the application**:
   - App: http://localhost
   - phpMyAdmin: http://localhost:8081

## Default Credentials

- **Email**: admin@visionmetrics.test
- **Password**: ChangeMe123!

## Test the System

1. Login with the credentials above
2. Go to **Settings** and copy the tracking snippet
3. The dashboard will show demo data (3 leads, 5 events)
4. Try the **Events** page with filters
5. Click "Replay" on any event to see the worker in action
6. Generate a new API key and test tracking

## Tracking Test

Use curl to test the tracking endpoint:

```bash
curl -X POST http://localhost/track.php \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "YOUR_API_KEY_HERE",
    "event_type": "page_view",
    "page_url": "https://example.com/test",
    "utm_source": "google",
    "utm_medium": "cpc",
    "email": "test@example.com"
  }'
```

## phpMyAdmin Access

- URL: http://localhost:8081
- Server: mysql
- Username: root
- Password: root
- Database: visionmetrics

## Worker Logs

To see the worker processing jobs:

```bash
docker compose logs -f worker
```

## Stop Services

```bash
docker compose down
```

## Troubleshooting

### Database not initialized?
Run the init script again:
```bash
docker compose exec php sh scripts/init_db.sh
```

### Can't login?
Check if the database seed ran successfully:
```bash
docker compose exec mysql mysql -u root -proot -e "USE visionmetrics; SELECT * FROM users;"
```

### Worker not processing jobs?
Check worker logs:
```bash
docker compose logs worker
```

Restart the worker:
```bash
docker compose restart worker
```

## Next Steps

1. Change the default password after first login
2. Generate your own API keys
3. Configure the tracking snippet for your domain
4. Set ADAPTER_MODE=live in docker-compose.yml to enable real webhooks
5. Configure your webhook endpoints in worker/process_jobs.php

Enjoy VisionMetrics! 🚀






