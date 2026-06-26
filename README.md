<img src="susie.png" alt="Susie Logo" width="300" valign="middle">

#  Susie
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://www.php.net/)
[![Dependencies](https://img.shields.io/badge/Dependencies-Zero-brightgreen.svg)](https://suckless.org/)

> A minimalist, high-performance static site and blog generator powered by native Bash and PHP. Built with the "suckless" philosophy in mind: no heavy dependencies, no bloated node_modules, just pure speed and efficiency.

---

## Features

- **Blazing Fast:** Compiles your entire site in milliseconds.
- **Suckless Architecture:** Only standard UNIX tools (`bash`, `grep`, `sed`, `awk`) and native PHP.
- **Zero-Downtime Atomic Swaps:** Production builds are compiled in a staging area and swapped instantly to prevent downtime.
- **Asset Pipeline:** Automated CSS minification and JavaScript bundling built-in.
- **Image Matrix Optimization:** Automatic conversion and resizing of raster images to next-gen WebP formats.
- **AI-Ready (Native `llms.txt`):** Automatically maps and generates a dynamic context discovery manifest for LLMs and AI crawlers on every build.
- **SEO & Syndication:** Built-in dynamic XML sitemaps and universal RSS feed generation.
- **Live Dev Mode:** Includes a lightweight hot-reloading mechanism for local development.

## Directory Structure

```text
.
├── dist/               # Compiled production-ready static files (gitignored)
├── generators/         # Core PHP page and post compilers
├── images/             # Raw image assets (PNG, JPG) to be optimized
├── pages/              # Static layout pages (PHP or Markdown)
├── posts/              # Markdown blog posts
├── templates/          # Base HTML layouts (main.php)
├── build.sh            # The orchestrator pipeline script
└── config.ini          # Global runtime environment configuration
```
## Getting Started
### Prerequisites

You only need standard terminal utilities and PHP installed locally:

- Bash (4.0+)
- PHP (8.0+ recommended)
- cwebp (optional, for automated WebP image optimization)

## Installation & Usage

1. Clone the repository:
 ```bash
git clone https://github.com/JC0nde/susie.git
cd susie
```
2. Configure your site:
Edit the config.ini file at the root to match your credentials, URLs, and feature preferences (Sitemap, RSS, Search Index, etc.).

3. Trigger the orchestrator build:
```bash
./build.sh
```
Your fully optimized static site will be compiled into the dist/ directory, ready to be served by Apache, Nginx, or any static hosting platform.

4. Developement Mode (with Hot-Reload)
To preview live changes while writing posts, toggle DEV_MODE=1 and serve the dist/ directory via PHP's built-in server:
```bash
./dev.sh
```

## License

This project is open-source and available under the MIT License.
