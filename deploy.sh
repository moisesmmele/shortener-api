#!/bin/bash

set -e

# Configuration
REGISTRY="registry.mele.lat"
IMAGE_NAME="shortener-api"
COMPOSE_FILE="compose.production.yml"
ENV_FILE=".env"

# Architecture mapping
declare -A ARCH_MAP=(
    ["x86_64"]="amd64"
    ["aarch64"]="arm64"
    ["armv7l"]="arm/v7"
    ["armv6l"]="arm/v6"
    ["i386"]="386"
    ["i686"]="386"
)

SYSTEM_ARCH="$(uname -m)"
DOCKER_ARCH="${ARCH_MAP[$SYSTEM_ARCH]:-$SYSTEM_ARCH}"
FULL_IMAGE="${REGISTRY}/${IMAGE_NAME}:${DOCKER_ARCH}"

log() {
    echo -e "\033[1;34m[INFO]\033[0m $1"
}

error() {
    echo -e "\033[0;31m[ERROR]\033[0m $1"
    exit 1
}

command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Check podman & podman-compose
for cmd in podman podman-compose; do
    command_exists "$cmd" || error "$cmd is required but not installed"
done

# Login to registry
log "Logging in to $REGISTRY"
read -p "Username: " USERNAME
read -s -p "Password: " PASSWORD
echo
echo "$PASSWORD" | podman login "$REGISTRY" -u "$USERNAME" --password-stdin

# Check if image exists by trying to pull quietly
log "Checking if image exists in registry..."
if podman pull "$FULL_IMAGE" 2>/dev/null; then
    read -p "Image found. Use it? (y/n): " use_image
    if [[ "$use_image" != "y" ]]; then
        build_image=true
    fi
else
    log "Image not found in registry. Will build locally."
    build_image=true
fi

# Build image if requested
if [[ "$build_image" == "true" ]]; then
    log "Building image from Containerfile..."
    podman build --platform "linux/$DOCKER_ARCH" -f Containerfile -t "$FULL_IMAGE" .
    read -p "Push image to registry? (y/n): " push_image
    if [[ "$push_image" == "y" ]]; then
        podman push "$FULL_IMAGE"
    fi
fi

# Create .env file if missing
if [[ ! -f "$ENV_FILE" ]]; then
    log "Creating default .env file..."
    cat <<EOF > "$ENV_FILE"
APP_NAME=shortener-api
APP_ENV=production
APP_DEBUG=false
APP_PORT=8080
DB_DRIVER=mongodb
DB_HOST=mongodb
DB_PORT=27017
DB_NAME=shortener-api
DB_USER=shortener-api
DB_PASSWORD=
MEMCACHED_HOST=memcached
MEMCACHED_PORT=11211
EOF
    echo ".env created. Edit DB_PASSWORD."
fi

# Add or update ARCH key in .env file
log "Setting architecture in .env file..."
if grep -q "^ARCH=" "$ENV_FILE"; then
    # Update existing ARCH line
    sed -i "s/^ARCH=.*/ARCH=$DOCKER_ARCH/" "$ENV_FILE"
else
    # Append new ARCH line
    echo "ARCH=$DOCKER_ARCH" >> "$ENV_FILE"
fi
log "Architecture set to: $DOCKER_ARCH (system: $SYSTEM_ARCH)"

# Setup compose.yml symlink
if [ -e compose.yml ]; then
    rm -f compose.yml
fi
ln -s "$COMPOSE_FILE" compose.yml
log "Symlink compose.yml -> $COMPOSE_FILE created."

# Deploy with podman-compose
log "Stopping any existing deployment..."
podman-compose -f "$COMPOSE_FILE" down -v || true

log "Pulling latest image..."
podman pull "$FULL_IMAGE" || log "Pull failed, proceeding with local image..."

log "Starting application..."
sudo podman-compose -f "$COMPOSE_FILE" up -d

log "Deployment complete! Application running at http://localhost:$(grep APP_PORT $ENV_FILE | cut -d= -f2)"

# Show status and recent logs
# podman-compose -f "$COMPOSE_FILE" ps
# podman-compose -f "$COMPOSE_FILE" logs --tail=20