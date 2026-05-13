# News API

A RESTful news platform API built with Laravel 12. Supports article management, user authentication, engagement features, comments, and author follow notifications.

## Requirements

- PHP 8.2+
- Composer
- PostgreSQL
- Node.js & npm

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd news-api
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env`

Update the following values in your `.env` file:

```dotenv
APP_NAME=NewsAPI
APP_URL=http://localhost:8000

# Database (PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=news_api
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Queue (required for email notifications)
QUEUE_CONNECTION=database

# Mail (configure for email notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

> For local development, you can use `MAIL_MAILER=log` to log emails instead of sending them.

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Build frontend assets (optional)

```bash
npm run build
```

### 7. Start the development server

```bash
# Option A: Start all services (server + queue + logs + vite)
composer dev

# Option B: Start individually
php artisan serve
php artisan queue:listen  # Required for email notifications
```

## Authentication

This API uses **Laravel Sanctum** for token-based authentication. Include the token in the `Authorization` header:

```
Authorization: Bearer <your-token>
```

## User Roles

| Role | Description |
|------|-------------|
| `ADMIN` | Full access: manage users, categories, tags, articles, comments |
| `AUTHOR` | Create/edit/publish articles, view followers |
| `READER` | Default role: read articles, comment, like, bookmark, follow authors |

---

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Register a new user | No |
| POST | `/api/login` | Login and get token | No |
| POST | `/api/logout` | Logout (revoke token) | Yes |
| GET | `/api/me` | Get current user profile | Yes |

### Articles (Author/Admin)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/articles` | Create article | AUTHOR, ADMIN |
| PUT | `/api/articles/{id}` | Update article | AUTHOR, ADMIN |
| DELETE | `/api/articles/{id}` | Delete article | AUTHOR, ADMIN |
| POST | `/api/articles/{id}/submit` | Publish article | AUTHOR, ADMIN |
| POST | `/api/articles/{id}/meta` | Update article metadata | AUTHOR, ADMIN |
| GET | `/api/articles/me` | My articles | AUTHOR, ADMIN |
| GET | `/api/editor/articles` | My articles (editor view) | AUTHOR, ADMIN |

### Articles (Public)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/articles` | All published articles | No |
| GET | `/api/articles/latest` | Latest articles (paginated) | No |
| GET | `/api/articles/trending` | Trending articles | No |
| GET | `/api/articles/featured` | Featured articles | No |
| GET | `/api/articles/search?q=` | Search articles | No |
| GET | `/api/articles/category/{slug}` | Articles by category | No |
| GET | `/api/articles/tag/{slug}` | Articles by tag | No |
| GET | `/api/articles/date?from=&to=` | Articles by date range | No |
| GET | `/api/articles/{slug}` | Article detail | No |
| GET | `/api/articles/{id}/related` | Related articles | No |

### Categories

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/categories` | List all categories | No |
| GET | `/api/categories/{slug}` | Category detail | No |
| GET | `/api/categories/{slug}/articles` | Articles in category | No |
| POST | `/api/categories` | Create category | ADMIN |
| PUT | `/api/categories/{id}` | Update category | ADMIN |
| DELETE | `/api/categories/{id}` | Delete category | ADMIN |

### Tags

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/tags` | List all tags | No |
| GET | `/api/tags/{slug}/articles` | Articles by tag | No |
| POST | `/api/tags` | Create tag | ADMIN |

### Comments

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/articles/{id}/comments` | List article comments | No |
| GET | `/api/articles/{id}/comments/{parent}/replies` | List comment replies | No |
| POST | `/api/comments` | Create comment | Yes |
| PUT | `/api/comments/{id}` | Update comment | Yes |
| DELETE | `/api/comments/{id}` | Delete comment | Yes |
| POST | `/api/comments/{id}/like` | Like a comment | Yes |
| DELETE | `/api/comments/{id}/like` | Unlike a comment | Yes |
| PATCH | `/api/comments/{id}/moderate` | Moderate comment | ADMIN |

### Engagement

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/articles/{id}/like` | Like an article | AUTHOR, ADMIN |
| DELETE | `/api/articles/{id}/like` | Unlike an article | AUTHOR, ADMIN |
| POST | `/api/articles/{id}/bookmark` | Bookmark an article | Yes |
| DELETE | `/api/articles/{id}/bookmark` | Remove bookmark | Yes |
| POST | `/api/articles/{id}/view` | Record article view | Yes |
| GET | `/api/me/bookmarks` | My bookmarks | Yes |

### Follow System

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/authors/{id}/follow` | Follow an author | Yes |
| DELETE | `/api/authors/{id}/follow` | Unfollow an author | Yes |
| GET | `/api/me/following` | List authors I follow (10/page) | Yes |
| GET | `/api/me/followers` | List my followers (15/page) | AUTHOR, ADMIN |
| GET | `/api/authors/{id}/follow-status` | Check if I follow an author | Yes |

### User Management (Admin)

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/users` | List all users | ADMIN |
| POST | `/api/users` | Create user | ADMIN |
| GET | `/api/users/{id}` | User detail | ADMIN |
| PATCH | `/api/users/{id}/role` | Update user role | ADMIN |
| DELETE | `/api/users/{id}` | Delete user | ADMIN |

---

## Features

### Article Management
- Create, update, delete articles with draft/published/archived status
- Slug-based article URLs
- Category and tag assignment
- Thumbnail upload
- Article submission workflow (draft → published)

### Article Discovery
- Latest, trending, and featured articles
- Search by title
- Filter by category, tag, or date range
- Related articles based on category and tags

### Engagement
- Like/unlike articles and comments
- Bookmark/unbookmark articles
- Article view tracking
- View counts for trending calculation

### Comments
- Nested comments (replies)
- Like/unlike comments
- Admin moderation (approve/reject)

### Author Follow & Email Notifications
- Follow/unfollow authors
- Check follow status
- List followed authors and followers (paginated)
- Automatic email notification to all followers when an author publishes a new article
- Notifications are queued for performance
- Duplicate notification prevention (re-publishing won't re-notify)
- Retry logic (3 attempts) for failed email delivery
- Follower count displayed on article detail

### User Management
- Role-based access control (ADMIN, AUTHOR, READER)
- Admin can manage users and assign roles
- Token-based authentication via Laravel Sanctum

---

## Queue Configuration

Email notifications are dispatched via the queue. Make sure the queue worker is running:

```bash
php artisan queue:listen
```

Or for production:

```bash
php artisan queue:work --tries=3
```

## Testing

```bash
php artisan test
# or
composer test
```

## Tech Stack

- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: PostgreSQL
- **Auth**: Laravel Sanctum (token-based)
- **Queue**: Database driver (configurable)
- **WebSocket**: Laravel Reverb
- **Testing**: Pest PHP
- **Code Style**: Laravel Pint
