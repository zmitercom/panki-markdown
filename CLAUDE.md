# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Symfony console application for converting markdown files into Anki flashcards using the Anki-Connect API. It supports both basic cards (front/back) and Cloze cards with audio and image attachments.

## Common Development Commands

### Core Commands
```bash
# Install dependencies
composer install

# Run a specific console command
bin/console [command]

# Clear Symfony cache
bin/console cache:clear
```

### Card Management
```bash
# Convert markdown file to Anki cards
bin/console card:convert_simple_card path/to/file.md

# Add individual card
bin/console card:add

# Generate audio for cards
bin/console card:generate_audio
```

### Deck Operations
```bash
# Clear all cards from deck
bin/console deck:clear

# List all available decks
bin/console deck:list
```

### Media Management
```bash
# Store media file
bin/console media:store_file

# Get media directory path
bin/console media:dir_path

# List media filenames
bin/console media:filenames
```

## Architecture

### Core Services
- **CardService** (`src/Service/CardService.php`): Main service for adding cards to Anki via API
- **MarkdownService** (`src/Service/MarkdownService.php`): Parses markdown files and splits them into card objects
- **RequestService** (`src/Service/RequestService.php`): HTTP client wrapper for Anki-Connect API calls
- **MediaService** (`src/Service/MediaService.php`): Handles file uploads and media management
- **AudioService** (`src/Service/AudioService.php`): Audio processing and TTS integration

### Card Types
- **Card** (`src/Dto/Card.php`): Abstract base class for all card types
- **BasicCard** (`src/Dto/BasicCard.php`): Standard front/back flashcards
- **ClozetCard** (`src/Dto/ClozetCard.php`): Cloze deletion cards with `{{c1::word}}` syntax

### API Integration
- Anki-Connect API endpoint: `http://localhost:8765` (defined in `src/Constant/Api.php`)
- Google Cloud Text-to-Speech integration for audio generation
- Supports image and audio attachments in cards

### Markdown Format
Cards are separated by `##` headings. Basic cards use `==` to separate front from back. Tags are specified with `[#tagname]()` syntax. Cloze cards use `{{c1::word}}` format.

### Media Handling
Images and audio files are automatically detected and uploaded to Anki. Supports both local files and URLs. Images can be in attachments subdirectory or alongside markdown files.