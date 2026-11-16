#!/bin/bash
set -e

echo "=========================================="
echo "  Tourist Car System - Automated Installer"
echo "  Server IP: 157.230.170.214"
echo "=========================================="

# 1. Update packages
echo "Updating packages..."
apt-get update -y
apt-get install -y curl git ufw

# 2. Configure firewall (UFW) to allow SSH and HTTP
echo "Configuring firewall..."
ufw allow 22/tcp || true
ufw allow 80/tcp || true
ufw allow 443/tcp || true
ufw --force enable || true

# 3. Install Docker if not already installed
if ! command -v docker &> /dev/null; then
    echo "Installing Docker..."
    curl -fsSL https://get.docker.com | sh
fi

# 4. Clone or update repository
APP_DIR="/var/www/tourist-car-system"
if [ -d "$APP_DIR" ]; then
    echo "Updating repository..."
    cd "$APP_DIR"
    git fetch origin main
    git reset --hard origin/main
else
    echo "Cloning repository..."
    mkdir -p /var/www
    git clone https://github.com/amjad467/system_df.git "$APP_DIR"
    cd "$APP_DIR"
fi

# 5. Build Docker Image
echo "Building application container..."
docker build -t tourist-car-system:latest .

# 6. Stop and remove existing container if running
if docker ps -a --format '{{.Names}}' | grep -q "^tourist-app$"; then
    echo "Restarting existing container..."
    docker stop tourist-app || true
    docker rm tourist-app || true
fi

# 7. Run container with persistent volumes and auto-restart
echo "Starting container on port 80..."
docker run -d \
    --name tourist-app \
    --restart always \
    -p 80:8080 \
    -v tourist_db:/var/www/database \
    -v tourist_storage:/var/www/storage \
    tourist-car-system:latest

echo "=========================================="
echo " SUCCESS! The system is now LIVE!"
echo " Open in browser: http://157.230.170.214"
echo "=========================================="
