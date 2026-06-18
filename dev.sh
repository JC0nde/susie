#!/usr/bin/env bash
# ==============================================================================
# Susie - Dev Server with Live Rebuild (zero dependencies, cross-platform)
# ==============================================================================

PORT=8000
export DEV_MODE=1

./build.sh

php -S "localhost:$PORT" router.php &
SERVER_PID=$!

trap "kill $SERVER_PID 2>/dev/null; exit" INT TERM

echo ""
echo "[SERVER] Serveur : http://localhost:$PORT"
echo "[WATCH]  Surveillance des fichiers (Ctrl+C pour arreter)..."
echo ""

# Détection de la commande de hash disponible (Linux/Git Bash vs macOS)
if command -v md5sum &> /dev/null; then
    HASH_CMD="md5sum"
else
    HASH_CMD="md5 -q"
fi

# Détection du support de find -printf (GNU find vs BSD find)
if find . -maxdepth 0 -printf '' &> /dev/null; then
    get_hash() {
        find . \
            -path ./dist -prune -o \
            -path ./.git -prune -o \
            -path ./dist_tmp -prune -o \
            -path ./dist_old -prune -o \
            \( -name "*.php" -o -name "*.md" -o -name "*.css" -o -name "*.js" -o -name "*.ini" \) -printf '%T@ %p\n' \
            2>/dev/null | sort | $HASH_CMD
    }
else
    get_hash() {
        find . \
            -path ./dist -prune -o \
            -path ./.git -prune -o \
            -path ./dist_tmp -prune -o \
            -path ./dist_old -prune -o \
            \( -name "*.php" -o -name "*.md" -o -name "*.css" -o -name "*.js" -o -name "*.ini" \) -print \
            2>/dev/null | sort | while read -r f; do
                stat -f '%m %N' "$f" 2>/dev/null
            done | $HASH_CMD
    }
fi

LAST_HASH=$(get_hash)

while true; do
    sleep 1
    CURRENT_HASH=$(get_hash)
    if [ "$CURRENT_HASH" != "$LAST_HASH" ]; then
        echo "[REBUILD] Changement detecte, rebuild..."
        ./build.sh
        LAST_HASH=$(get_hash)
    fi
done
