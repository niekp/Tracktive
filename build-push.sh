#!/usr/bin/env bash
# Cross-build the amd64 image (this Mac is arm64) and push to GHCR.
# The nuc then just pulls it like any other service. One-time: `docker login ghcr.io -u niekp`.
set -euo pipefail

IMAGE="ghcr.io/niekp/tracktive"
TAG="${1:-latest}"
HERE="$(cd "$(dirname "$0")" && pwd)"

# A docker-container builder is required for cross-platform builds; create once, reuse after.
if ! docker buildx inspect tracktive-builder >/dev/null 2>&1; then
  docker buildx create --name tracktive-builder --driver docker-container >/dev/null
fi

echo "▶ Building ${IMAGE}:${TAG} for linux/amd64 and pushing…"
docker buildx build \
  --builder tracktive-builder \
  --platform linux/amd64 \
  -t "${IMAGE}:${TAG}" \
  --push \
  "${HERE}"

echo "✅ Pushed ${IMAGE}:${TAG}"
echo "Deploy on the nuc:  cd /opt/services/tracktive && docker compose pull && docker compose up -d"
echo "         (or via Ansible:  ansible-playbook site.yml -e service=tracktive)"
