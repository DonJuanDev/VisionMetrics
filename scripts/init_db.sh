#!/bin/bash
# ═══════════════════════════════════════════════════════════
# VisionMetrics - Database Initialization Script
# Idempotent migrations + seed data
# ═══════════════════════════════════════════════════════════

set -e

echo "🔄 VisionMetrics - Database Initialization"
echo "=========================================="

# Load env vars if .env exists
if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Default values
DB_HOST=${DB_HOST:-mysql}
DB_NAME=${DB_NAME:-visionmetrics}
DB_USER=${DB_USER:-visionmetrics}
DB_PASS=${DB_PASS:-visionmetrics}
MYSQL_ROOT_PASSWORD=${MYSQL_ROOT_PASSWORD:-root}

echo "📊 Database: $DB_NAME"
echo "🏠 Host: $DB_HOST"
echo ""

# Wait for MySQL (max 60 seconds)
echo "⏳ Waiting for MySQL..."
for i in {1..60}; do
    if mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; then
        echo "✅ MySQL is ready!"
        break
    fi
    echo "   Attempt $i/60..."
    sleep 1
done

# Create database if not exists
echo ""
echo "🗄️  Creating database..."
mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Create user if not exists
echo "👤 Creating user..."
mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '$DB_USER'@'%' IDENTIFIED BY '$DB_PASS';"
mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%';"
mysql -h "$DB_HOST" -u root -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"

# Run schema
echo ""
echo "📋 Running schema..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < sql/schema.sql

# Run seed
echo "🌱 Running seed data..."
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < sql/seed.sql

echo ""
echo "✅ Database initialized successfully!"
echo ""
echo "📧 Admin credentials:"
echo "   Email: admin@visionmetrics.test"
echo "   Password: ChangeMe123!"
echo ""
echo "🔑 Demo API Key:"
echo "   vm_test_1234567890abcdef"
echo ""