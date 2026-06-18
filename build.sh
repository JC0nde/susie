#!/usr/bin/env bash
shopt -s extglob
# ==============================================================================
# Susie - Suckless Static Site Generator Orchestrator Pipeline
# ==============================================================================

# Microtime initialization benchmark via PHP
START_TIME=$(php -r 'echo microtime(true);')

# ------------------------------------------------------------------------------
# 1. Environment Purge & Workspace Initialization
# ------------------------------------------------------------------------------
mkdir -p dist
if [ -d "dist" ]; then
    find dist -mindepth 1 -maxdepth 1 ! -name 'images' -exec rm -rf {} +
fi
# Récupérer le cache images de l'ancien dist/ si il existe
mkdir -p dist_tmp/images
if [ -d "dist/images" ]; then
    cp -r dist/images/. dist_tmp/images/
fi
 
# Generate Unix Timestamp signature for non-blocking Cache Busting assets
export BUILD_VERSION=$(date +%Y%m%d%H%M)
date +%s > dist_tmp/build-version.txt

# Parse runtime execution parameters from config.ini
IGNORE_FILES=$(grep -E '^ignore_files[[:space:]]*=' config.ini | cut -d'=' -f2 | tr -d '"')
CSS_MODE=$(grep -E '^css_mode[[:space:]]*=' config.ini | cut -d'"' -f2)

# Global compilation of external stylesheets (via le nouveau minify_css)
if [ -f "style.css" ]; then
    php -r "
        require 'functions.php';
        echo minify_css(file_get_contents('style.css'));
    " > style.min.css
    echo "[BUILD] Asset Pipeline: minified style.min.css generated."
fi

# ------------------------------------------------------------------------------
# 2. Asset Ingestion & Image Matrix Optimization
# ------------------------------------------------------------------------------
[ -f "favicon.png" ] && cp favicon.png dist_tmp/

if [ -d "images" ]; then
    if ! command -v cwebp &> /dev/null; then
        echo "[WARN]  Dependency Alert: cwebp binary missing. Performing raw ingestion."
        cp -R images/* dist_tmp/images/ 2>/dev/null || true
    else
        echo "[BUILD] Optimization Matrix: Compressing image assets to WebP format..."
        shopt -s nullglob

        CONVERTED=0
        SKIPPED=0

        # Conversion des formats raster supportés (avec cache mtime)
        for img in images/*.{jpg,jpeg,png,JPG,JPEG,PNG}; do
            [ -e "$img" ] || continue
            filename=$(basename "$img")
            filename="${filename%.*}"

            out_main="dist_tmp/images/${filename}.webp"
            out_mobile="dist_tmp/images/${filename}-mobile.webp"

            if [ -e "$out_main" ] && [ -e "$out_mobile" ] && [ "$out_main" -nt "$img" ] && [ "$out_mobile" -nt "$img" ]; then
                SKIPPED=$((SKIPPED + 1))
                continue
            fi

            cwebp -q 80 -resize 1200 0 "$img" -o "$out_main" &> /dev/null
            cwebp -q 80 -resize 600 0 "$img" -o "$out_mobile" &> /dev/null
            CONVERTED=$((CONVERTED + 1))
        done

        # Copie brute des formats non convertis (svg, webp, gif, etc.) — avec cache
        for img in images/*.!(jpg|jpeg|png|JPG|JPEG|PNG); do
            [ -e "$img" ] || continue
            filename=$(basename "$img")
            out="dist_tmp/images/${filename}"

            if [ -e "$out" ] && [ "$out" -nt "$img" ]; then
                SKIPPED=$((SKIPPED + 1))
                continue
            fi

            cp "$img" "$out"
            CONVERTED=$((CONVERTED + 1))
        done

        shopt -u nullglob
        echo "   -> ${CONVERTED} image(s) traitee(s), ${SKIPPED} ignoree(s) (cache)."

        # Nettoyage des fichiers orphelins dans dist/images/ (sources supprimées)
        ORPHANS=0
        shopt -s nullglob
        for f in dist_tmp/images/*; do
            [ -e "$f" ] || continue
            filename=$(basename "$f")

            base="$filename"
            base="${base%.webp}"
            base="${base%-mobile}"

            found=0
            for ext in jpg jpeg png JPG JPEG PNG; do
                [ -e "images/${base}.${ext}" ] && found=1 && break
            done
            [ -e "images/${filename}" ] && found=1

            if [ "$found" -eq 0 ]; then
                rm "$f"
                ORPHANS=$((ORPHANS + 1))
            fi
        done
        shopt -u nullglob

        [ "$ORPHANS" -gt 0 ] && echo "   -> ${ORPHANS} fichier(s) orphelin(s) supprime(s) de dist/images/."
    fi
fi

# ------------------------------------------------------------------------------
# 2b. JavaScript Ingestion, Bundling & Compression Stage
# ------------------------------------------------------------------------------
JS_MODE=$(grep -E '^js_mode[[:space:]]*=' config.ini | cut -d'"' -f2)

rm -f dist_tmp/temp_bundle.js dist_tmp/bundle.js

for f in *.js; do [ -e "$f" ] && cat "$f" >> dist_tmp/temp_bundle.js; done
if [ -d "js" ]; then cat js/*.js 2>/dev/null >> dist_tmp/temp_bundle.js; fi

if [ -s "dist_tmp/temp_bundle.js" ]; then
    echo "[BUILD] Script Pipeline: Compiling JavaScript ($JS_MODE strategy)..."
    
    php -r "
        require 'functions.php';
        \$js = file_get_contents('dist_tmp/temp_bundle.js');
        \$minified = function_exists('minify_js') ? minify_js(\$js) : \$js;
        
        if ('$JS_MODE' === 'inline') {
            file_put_contents('dist_tmp/temp_bundle.js', \$minified);
        } else {
            file_put_contents('dist_tmp/bundle.js', \$minified);
        }
    "
    [ "$JS_MODE" != "inline" ] && rm -f dist_tmp/temp_bundle.js
else
    echo "[SKIP]  Script Pipeline: No active source JavaScript discovered."
    rm -f dist_tmp/temp_bundle.js
fi

# =========================================================
# 3. Compilation intelligente (PHP exécuté / MD parsé)
# =========================================================
if [ -d "pages" ]; then
    find pages -type f \( -name "*.php" -o -name "*.md" \) | while read -r filepath; do

        filename_raw=$(basename "$filepath")
        extension="${filename_raw##*.}"

        # Ignorer les fichiers exclus
        if echo "$IGNORE_FILES" | grep -qE "(^| )$filename_raw( |$)"; then
            echo "[IGNORE] Ingestion Rule: Static page ignored -> $filename_raw"
            continue
        fi

        relative_path="${filepath#pages/}"
        output_html="dist_tmp/${relative_path%.*}.html"
        mkdir -p "$(dirname "$output_html")"
        filename_slug="${relative_path%.*}"

        php generators/generate_page.php "$filepath" "$filename_slug" > "$output_html"

        echo "[PAGE]  Generated [ $extension -> HTML ] : $output_html"
    done
fi

# ------------------------------------------------------------------------------
# 4. Markdown Posts Assembly
# ------------------------------------------------------------------------------
if [ -d "posts" ]; then
    mkdir -p dist_tmp/blog
    for filepath in posts/*.md; do
        [ -e "$filepath" ] || continue
        filename_raw=$(basename "$filepath")
        filename=$(basename "$filepath" .md)
        if echo "$IGNORE_FILES" | grep -qE "(^| )$filename_raw( |$)"; then
            echo "[IGNORE] Ingestion Rule: Markdown post ignored -> $filename_raw"
            continue
        fi
        php generators/generate_post.php "$filepath" "$filename" > "dist_tmp/blog/${filename}.html"
        echo "[POST]  Generated : dist_tmp/blog/${filename}.html"
    done
fi

# ------------------------------------------------------------------------------
# 4b. Category Listing Pages Generation
# ------------------------------------------------------------------------------
php generators/generate_categories.php

# ------------------------------------------------------------------------------
# 5 & 6. Condition-Based Search Index & Feed Manifest Synthesizers
# ------------------------------------------------------------------------------
echo "[FEED]  Analyzing optional core syndication modules..."

GEN_SITEMAP=$(grep -E '^generate_sitemap[[:space:]]*=' config.ini | cut -d'=' -f2 | tr -d '[:space:]"' )
GEN_RSS=$(grep -E '^generate_rss[[:space:]]*=' config.ini | cut -d'=' -f2 | tr -d '[:space:]"' )

# Module A: Sitemap Manifest Construction Engine
if [ "$GEN_SITEMAP" = "true" ]; then
    php generators/generate_sitemap.php
else
    echo "[SKIP]  Syndication Module: Sitemap indexing framework deactivated."
fi

# Module B: Universal RSS Feed Manifest Synthesizer
if [ "$GEN_RSS" = "true" ]; then
    php generators/generate_feed.php
else
    echo "[SKIP]  Syndication Module: RSS transmission feed engine deactivated."
fi

# Module C: Search Index Compilation Engine
GEN_SEARCH=$(grep -E '^generate_search[[:space:]]*=' config.ini | cut -d'=' -f2 | tr -d '[:space:]"' )
if [ "$GEN_SEARCH" = "true" ]; then
    php generators/generate_search.php
else
    echo "[SKIP]  Search Module: Indexing framework deactivated."
fi

# ------------------------------------------------------------------------------
# 6. Global Housekeeping Post-Build Routine
# ------------------------------------------------------------------------------
rm -f dist_tmp/temp_bundle.js
rm -f style.min.css
[ -f ".htaccess" ] && cp .htaccess dist_tmp/.htaccess

# Swap atomique : dist_tmp devient dist sans trou de disponibilité
rm -rf dist_old
mv dist dist_old 2>/dev/null || true
mv dist_tmp dist
rm -rf dist_old

END_TIME=$(php -r 'echo microtime(true);')
ELAPSED_MS=$(php -r "echo round(($END_TIME - $START_TIME) * 1000);")

echo "Susie compiled your site successfully in ${ELAPSED_MS}ms!"
