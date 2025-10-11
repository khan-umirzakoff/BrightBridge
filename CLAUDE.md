# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**BrightBridge/JobCare** is a Laravel 5.8 job portal application (Uzbek: "JobCare Platformasi") with AI-powered features. The platform enables job seekers to find employment, companies to post jobs, and includes an AI chatbot assistant with RAG (Retrieval-Augmented Generation) capabilities for answering user questions about jobs, news, trainings, and uploaded documents.

**Technology Stack:**
- PHP 7.4 with Laravel 5.8
- MySQL database
- Frontend: Blade templates, Vue.js, Bootstrap 4
- AI Integration: OpenAI and Google Gemini APIs (configurable)
- Document processing with FPDF, PHPWord, PDF parser

## Development Commands

### Environment Setup
```bash
# Initial setup (from repository root)
./run_without_sudo.sh  # Preferred: Sets up without requiring sudo
./run.sh               # Alternative: Full setup with system packages

# Manual setup steps (from public_html directory)
cd public_html
php composer.phar install --no-interaction --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan config:cache
php artisan migrate
```

### Running the Application
```bash
cd public_html
php artisan serve              # Start development server at localhost:8000
php artisan serve --port=8080  # Use custom port
```

### Database Operations
```bash
# Run migrations
php artisan migrate

# Reset database with SQL dump
mysql -h $DB_HOST -P $DB_PORT -u$DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < ../brightbr_job.sql
```

### Cache Management
```bash
php artisan config:clear   # Clear config cache
php artisan route:clear    # Clear route cache
php artisan view:clear     # Clear view cache
php artisan cache:clear    # Clear application cache
```

### Asset Compilation
```bash
npm run dev          # Compile assets for development
npm run watch        # Watch and recompile on changes
npm run production   # Compile and minify for production
```

### Testing
```bash
# PHPUnit is configured but tests directory doesn't exist yet
vendor/bin/phpunit                    # Run all tests (when implemented)
vendor/bin/phpunit --filter TestName  # Run specific test
```

## Architecture & Key Components

### AI System Architecture

The application uses a **provider-agnostic AI service pattern** with support for both OpenAI and Google Gemini:

1. **Service Layer** (`app/Services/`):
   - `GeminiAIService.php` - Gemini implementation
   - `OpenAIService.php` - OpenAI implementation
   - `RAGService.php` - Retrieval-Augmented Generation with semantic search
   - All implement `App\Contracts\AIService` interface

2. **AI Configuration** (`app/AiSetting.php`):
   - Centralized AI provider settings with caching
   - Runtime provider switching (OpenAI/Gemini)
   - Model configuration (chat models, embedding models)
   - API key management

3. **Knowledge Base** (`app/AiKnowledge.php`):
   - Stores platform facts (contact info, FAQs, services)
   - Vector embeddings for semantic search
   - Cosine similarity matching
   - Priority-based context building

4. **RAG System** (`RAGService.php`):
   - **Function Calling**: AI can invoke `search_general`, `get_contact_info`, `get_platform_stats`
   - **Chunk-Level Search**: Documents are split into ~2000 char chunks with individual embeddings
   - **Multi-Table Search**: Searches across `jobs`, `news`, `trainings`, `ai_documents` tables
   - **Similarity Threshold**: 0.4 for relevance filtering
   - Uses embeddings stored as JSON in `embedding` columns (LONGTEXT)

5. **Document Processing** (`DocumentController.php`):
   - Supports PDF, DOCX, TXT uploads
   - Extracts text content and generates embeddings asynchronously
   - Chunks large documents for granular semantic search
   - Progress tracking via AJAX polling

### Application Structure

**Controllers** (`app/Http/Controllers/`):
- `IndexController.php` - Main site pages (jobs, trainings, news, blog, contact)
- `AIChatController.php` - Streaming AI chat endpoint with Server-Sent Events
- `CVController.php` - CV/Resume generation
- `DocumentController.php` - AI document upload and management
- `admin/AdminController.php` - Admin panel operations (32KB, main admin logic)
- `admin/AIKnowledgeController.php` - Manage AI knowledge base
- `admin/AISettingsController.php` - Configure AI providers and models

**Models** (`app/`):
- `AiKnowledge.php` - Knowledge base with semantic search
- `AiSetting.php` - AI configuration with caching
- `Jobs.php`, `News.php`, `Trainings.php` - Main content models
- All models use `embedding` column for vector storage

**Views** (`resources/views/`):
- `admin/` - Admin panel templates
- `main2/` - Main site templates
- `pages/` - Static pages
- `inc/` - Shared components (header, footer)

### Database Schema

**Key Tables:**
- `jobs` - Job postings with embeddings
- `news` - News articles with embeddings
- `trainings` - Training programs with embeddings
- `ai_documents` - Uploaded documents with chunked embeddings (LONGTEXT)
- `ai_knowledge` - Platform knowledge base with embeddings
- `ai_settings` - AI provider configuration
- `applications` - Job applications
- `companies` - Company profiles
- `users` - User accounts (job seekers and companies)

**Important:** The `embedding` column format differs:
- Simple tables (jobs, news, trainings): Single embedding array `[0.1, 0.2, ...]`
- Documents: Array of chunk embeddings `[[0.1, 0.2, ...], [0.3, 0.4, ...], ...]`

### Routes Structure

Routes are defined in `routes/web.php`:
- Public routes: job listings, blog, contact, trainings
- User routes: `/cab` (cabinet), `/myapplications`, `/apply/{id}`
- Company routes: `/company-login`, `/postjob`, `/company-profile`
- Admin routes: `/admin/*` (categories, news, jobs, AI management)
- AI routes: `/admin/ai-documents/*`, `/admin/ai-knowledge`, `/admin/ai-settings`

### Authentication & Authorization

- **Job Seekers**: Login via `/login`, access personal cabinet at `/cab`
- **Companies**: Login via `/company-login`, post jobs, view applicants
- **Admin**: Login via `/checkeradmin`, full CRUD access to all entities
- Session-based authentication (no Laravel Passport/Sanctum)

## Important Implementation Notes

### AI Service Provider Binding

The active AI provider is bound in `app/Providers/AppServiceProvider.php` based on `ai_settings` table. The binding is:
```php
$this->app->bind(AIService::class, function ($app) {
    $provider = AiSetting::getProvider(); // 'openai' or 'gemini'
    return $provider === 'openai'
        ? new OpenAIService()
        : new GeminiAIService();
});
```

When switching providers in admin panel, Laravel cache must be cleared for changes to take effect.

### Embedding Generation Workflow

1. Content is saved to database (job, news, document)
2. Text content is extracted
3. AI service generates embedding via `embed($text)` method
4. Embedding stored as JSON string in `embedding` column
5. For documents: content split into chunks, each chunk embedded separately

### RAG Function Calling Flow

1. User sends message to `/api/chat`
2. `AIChatController` calls `AIService->streamChat()`
3. AI service includes tool declarations (functions) in request
4. AI may respond with function call instead of text
5. `RAGService->executeTool()` runs the function
6. Results sent back to AI as context
7. AI generates final response using retrieved data
8. Response streamed to client via Server-Sent Events

### Similarity Search Performance

- Cosine similarity calculated in PHP (no vector DB)
- Searches typically process 50-500 embeddings
- Threshold of 0.4 filters ~80% of irrelevant results
- Top 3-5 results returned to avoid context overflow
- For production scale, consider migrating to pgvector or similar

### File Permissions

Storage and cache directories require write permissions:
```bash
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### Environment Variables

Required in `.env`:
- Database credentials: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- AI API keys (at least one): `GEMINI_API_KEY` or `OPENAI_API_KEY`
- App URL: `APP_URL` (used for generating document/job links in RAG)

## Common Development Workflows

### Adding New AI Knowledge
1. Navigate to `/admin/ai-knowledge`
2. Add entry with category, key, value, description
3. Click "Generate Embedding" or "Generate All Embeddings"
4. Knowledge available immediately to AI chatbot

### Uploading AI Documents
1. Go to `/admin/ai-documents`
2. Upload PDF/DOCX/TXT (max 10MB recommended)
3. System extracts text and chunks it
4. Embeddings generated asynchronously
5. Poll progress at `/admin/ai-documents/progress/{id}`
6. Document searchable when status = "completed"

### Switching AI Providers
1. Go to `/admin/ai-settings`
2. Select provider (OpenAI or Gemini)
3. Configure API key and model
4. Test connection
5. Clear config cache: `php artisan config:clear`

### Debugging AI Responses
- Check Laravel logs at `storage/logs/laravel.log`
- RAG service logs function calls with `Log::info()`
- AI errors logged with full stack trace
- Frontend console shows SSE stream events

## Code Style & Conventions

- **Language**: Mixed Uzbek/Russian in UI, English in code
- **Naming**: Snake_case for database, camelCase for PHP methods
- **Views**: Blade templates in `resources/views/`
- **Routes**: Defined in `routes/web.php`, use `Route::match(['GET','POST'])` pattern
- **Models**: Eloquent ORM, models in `app/` directory
- **Database**: Migrations in `database/migrations/`, SQL dump at `brightbr_job.sql`

## Known Issues & Limitations

- Tests directory not implemented yet (phpunit.xml exists but no test files)
- No queue system for embedding generation (processes synchronously)
- Similarity search is PHP-based (not optimized for large datasets >10k items)
- No API authentication/rate limiting on `/api/chat` endpoint
- Frontend assets not version-hashed (potential cache issues on updates)
