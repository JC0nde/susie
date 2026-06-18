# Susie 🐦

Suckless Static Site Generator — un générateur de site statique minimaliste,
sans dépendances lourdes, fait pour mon usage personnel.

**Stack** : PHP (CLI) + bash + [Parsedown](https://parsedown.org/) + `cwebp` (optionnel).
**Build** : ~200-500ms pour l'ensemble du site.

## Démarrage rapide

```bash
./build.sh   # build complet, une fois (prod)
./dev.sh     # build + serveur local + rebuild auto au changement de fichier
```

`dev.sh` lance `php -S localhost:8000 -t dist` et surveille les fichiers
sources (`.php`, `.md`, `.css`, `.js`, `.ini`). Le navigateur se recharge
automatiquement via un petit script de polling (`dist/build-version.txt`),
injecté uniquement quand `DEV_MODE=1`.

## Structure du projet

```
.
├── build.sh            # Orchestration du build (bash, pas de logique PHP inline)
├── dev.sh              # Serveur de dev + watch + rebuild auto
├── config.ini          # Configuration du site (voir ci-dessous)
├── functions.php       # Bibliothèque de helpers PHP (pure, pas d'effets de bord)
├── Parsedown.php        # Parseur Markdown (dépendance externe, fichier unique)
├── style.css           # Feuille de style source
├── favicon.png
├── robots.txt
│
├── generators/          # Scripts PHP de génération, un par responsabilité
│   ├── generate_page.php       # pages/*.php et pages/*.md -> dist/**.html
│   ├── generate_post.php       # posts/*.md -> dist/blog/*.html
│   ├── generate_categories.php # dist/categorie/*.html (si activé)
│   ├── generate_sitemap.php    # dist/sitemap.xml (si activé)
│   └── generate_feed.php       # dist/feed.xml (si activé)
│
├── layouts/
│   └── main.php         # Layout HTML global (<head>, header, footer, CSS/JS inline ou liés)
│
├── templates/
│   ├── post_wrapper.php     # Wrapper d'un article de blog (meta-header + nav prev/next)
│   └── category_wrapper.php # Wrapper d'une page de catégorie (liste d'articles filtrée)
│
├── components/
│   ├── header.php       # Header du site (logo, nav principale)
│   ├── footer.php       # Footer (copyright, liens, inclut categories.php)
│   └── categories.php   # Menu des catégories (lien vers /categorie/{slug}.html)
│
├── pages/                # Pages statiques du site (PHP ou Markdown)
│   ├── index.php         # Page d'accueil = liste des articles de blog
│   ├── projets.php
│   └── projets/
│       └── susie.md       # -> dist/projets/susie.html
│
├── posts/                # Articles de blog (Markdown + front-matter)
│   └── *.md               # -> dist/blog/{slug}.html
│
├── images/               # Images sources -> converties en WebP dans dist/images/
│
└── dist/                 # Sortie générée (ne pas éditer à la main, régénéré à chaque build)
```

## Pipeline de build (`build.sh`)

1. **Init** : purge `dist/` (sauf `dist/images/`, qui est mis en cache),
   génère `BUILD_VERSION` (cache-busting) et `dist/build-version.txt`
   (déclencheur du live-reload en dev).
2. **CSS** : génère toujours `dist/style.min.css`. En mode `inline`, injecté
   dans `<style>` par `main.php`. En mode `file`, copié vers `dist/style.css`
   et lié via `<link>`.
3. **Images** : conversion WebP (1200px desktop + 600px mobile via `cwebp`),
   avec cache basé sur mtime — seules les images modifiées sont reconverties.
   Les fichiers `.gif`/`.svg`/etc. sont copiés tels quels. Les orphelins
   (images supprimées des sources) sont nettoyés de `dist/images/`.
4. **JS** : concatène `*.js` + `js/*.js`, minifie, inline ou en `dist/bundle.js`
   selon `js_mode`.
5. **Pages** (`pages/`) : chaque `.php`/`.md` → `generators/generate_page.php`
   → `dist/{chemin}.html`. Les `.md` supportent le front-matter YAML
   (`title`, `description`, `lang`).
6. **Posts** (`posts/`) : chaque `.md` → `generators/generate_post.php`
   → `dist/blog/{slug}.html`, avec navigation prev/next automatique
   (basée sur la date).
7. **Catégories** : `generators/generate_categories.php` → une page
   `dist/categorie/{slug}.html` par catégorie détectée dans le front-matter
   des posts (`category: Dev, Voyage` — plusieurs catégories possibles,
   séparées par virgules).
8. **Sitemap** / **RSS** : `generators/generate_sitemap.php` et
   `generate_feed.php`, conditionnés par `config.ini`.

## Conventions importantes

### Front-matter des posts (`posts/*.md`)

```yaml
---
title: Mon article
description: Description SEO
date: 2026-06-08
lang: fr
author: Jonathan Conde
category: Dev, Archéologie
---
```

Tous les champs sont optionnels (valeurs par défaut dans `get_blog_posts()`).

### Images dans le Markdown

```markdown
![Texte alternatif](images/mon-image)        → <picture> WebP responsive (1200/600px)
![Texte alternatif](images/mon-image.png)    → idem (extension ignorée pour jpg/png)
![Texte alternatif](images/animation.gif)    → <img> brut vers le fichier original
![Texte alternatif](images/icone.svg)        → <img> brut vers le fichier original
```

### `ignore_files` (`config.ini`)

Liste de noms de fichiers (séparés par espace) exclus du build — match exact
sur le nom de fichier, pas de substring.

### Flags activables/désactivables (`config.ini`)

- `[assets] css_mode` / `js_mode` : `"inline"` ou `"file"`
- `[syndication] generate_sitemap` / `generate_rss` : `"true"`/`"false"`
- `[features] generate_categories` : `"true"`/`"false"` — si désactivé,
  aucune page `categorie/*` n'est générée et le lien disparaît du footer.
- `[seo] exclude_categories_from_sitemap` : `"true"`/`"false"` — si activé,
  les pages de catégories restent navigables sur le site mais sont absentes
  du sitemap et marquées `<meta name="robots" content="noindex, follow">`.

## Pièges connus / notes pour plus tard

- **Scope PHP partagé** : tous les fichiers de `components/`, `templates/`,
  `layouts/` partagent le même espace de variables globales (pas de
  namespace/classe). Éviter les noms de variables génériques (`$slug`,
  `$post`, `$item`, `$cat`) dans les composants inclus au milieu d'une
  boucle — préférer un préfixe (`$__cat_slug`) pour éviter d'écraser une
  variable de boucle d'un script appelant.
- **`functions.php` est une pure bibliothèque** : pas d'effets de bord, pas
  de génération de fichiers, pas de CLI router. Toute génération de sortie
  va dans `generators/`.
- **Cache images** (`dist/images/`) : persiste entre les builds. Si une
  image semble "ne pas se mettre à jour", vérifier le mtime du fichier
  source (`touch images/photo.png` force la reconversion).
- **Architecture des URLs** : articles sous `/blog/{slug}.html`, catégories
  sous `/categorie/{slug}.html`, reste à plat à la racine de `dist/`.

## TODO / idées pour plus tard

- [ ] Pagination de la liste d'articles (`pages/index.php`) — actuellement
      pas nécessaire avec peu d'articles, mais structure prête à accueillir
      `$posts` + `$pagination_html` injectés par un futur générateur dédié.
- [ ] Swap atomique de `dist/` pendant le build (éviter le 404 transitoire
      en mode dev pendant la régénération).
- [ ] Pages de taxonomie paginées si le nombre d'articles par catégorie
      devient important.
