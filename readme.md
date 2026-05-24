# S2J Slug Generater

A WordPress plugin that generates optimal slug candidates using translation service APIs. Supports both Gutenberg block editor and Classic editor.

## Features

- **Translation API Integration**: Uses DeepL API or Google Translate API to generate English slugs from Japanese (or other language) titles
- **Similarity Check**: Implements Levenshtein distance algorithm to ensure translation quality
- **Dual Editor Support**: Works seamlessly with both Gutenberg block editor and Classic editor
- **Configurable Settings**: Customizable API keys, source languages, and similarity thresholds
- **Modern UI**: Clean, responsive interface built with React and WordPress components

## Requirements

- WordPress v5.0+
- PHP v7.4+
- Node.js v16+ (for development)

## Installation

1. Download the plugin files
2. Upload the plugin folder to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to 'Settings' > 'S2J Slug Generater' to configure your API keys

## Configuration

### API Setup

1. **DeepL API**:
   - Visit [DeepL Pro API Pricing](https://www.deepl.com/pro-api#api-pricing)
   - Get your API key from [DeepL Developer](https://www.deepl.com/ja/pro#developer)

2. **Google Translate API**:
   - Visit [Google Cloud Translate Pricing](https://cloud.google.com/translate/pricing?hl=ja)
   - Follow the [setup guide](https://cloud.google.com/translate/docs/setup?hl=ja)

### Plugin Settings

- **Translation Service**: Choose between DeepL and Google Translate
- **API Key**: Enter your chosen service's API key
- **Source Language**: Select the language of your post titles
- **Similarity Threshold**: Set minimum similarity percentage (0-100%)

## Usage

### Gutenberg Editor

1. Create or edit a post
2. Enter your post title
3. Look for the "S2J Slug Generater" block below the title
4. Click "Generate Candidates" to create slug suggestions
5. Review the similarity score and select your preferred candidate
6. Click "Apply Slug" to set the slug

### Classic Editor

1. Create or edit a post
2. Enter your post title
3. Find the "S2J Slug Generater" metabox below the content area
4. Click "Generate Candidates" to create slug suggestions
5. Review the similarity score and select your preferred candidate
6. Click "Slugify" to apply the slug

## How It Works

1. **Translation**: Your title is translated from the source language to English
2. **Reverse Translation**: The English translation is translated back to the source language
3. **Similarity Check**: The original title and reverse translation are compared using Levenshtein distance
4. **Slug Generation**: English translation is converted to slug format with various combinations
5. **Filtering**: Only candidates meeting your similarity threshold are shown

## Development

### Building from Source

```bash
# Install dependencies
npm install --force

# Development build
npm run build:dev

# Production build
npm run build:production

# Watch mode for development
npm run dev

# Type checking
npm run type-check
```

### Project Structure

```
s2j-slug-generater/
├── includes/           # PHP classes
├── src/               # TypeScript/React source
│   ├── admin/        # Admin settings
│   ├── gutenberg/    # Gutenberg block
│   ├── classic/      # Classic editor
│   └── styles/       # SCSS styles
├── dist/             # Built assets
└── languages/        # Translation files
```

### Architecture

- **Template Method Pattern**: Used for translation flow
- **Abstract Factory Pattern**: Used for translation service selection
- **REST API**: Handles slug generation requests
- **React Components**: Modern UI for Gutenberg and admin
- **jQuery**: Classic editor compatibility

## API Endpoints

- `POST /wp-json/s2j-slug-generater/v1/generate`
  - Generates slug candidates from a title
  - Requires authentication and nonce verification

## Internationalization

The plugin supports multiple languages through WordPress's built-in internationalization system. Text domain: `s2j-slug-generater`

## License

GPL v2+

## Support

For support and feature requests, please visit the plugin's GitHub repository.

## Changelog

### 1.0.0
- Initial release
- DeepL and Google Translate API support
- Gutenberg and Classic editor integration
- Similarity threshold system
- Responsive admin interface
