# Implementation Plan: Author Follow Notifications

## Overview

Backend-only implementation of an author-follow system with queued email notifications on article publication. Uses Laravel's event/listener architecture, notification system, and follows the existing patterns established by likes/bookmarks in the codebase. All endpoints are API-only (no Blade views).

## Tasks

- [x] 1. Database migrations and Follow model
  - [x] 1.1 Create migration for `follows` table
    - Create `database/migrations/xxxx_xx_xx_create_follows_table.php`
    - Columns: `id`, `follower_id` (foreign key → users), `author_id` (foreign key → users), `created_at` (timestamp)
    - Add unique constraint on `(follower_id, author_id)`
    - Add indexes on `author_id` and `follower_id`
    - ON DELETE CASCADE for both foreign keys
    - _Requirements: 1.1, 1.5, 2.1_

  - [x] 1.2 Create migration to add `notified_at` column to `articles` table
    - Create `database/migrations/xxxx_xx_xx_add_notified_at_to_articles_table.php`
    - Add nullable `notified_at` timestamp column to articles table
    - _Requirements: 6.7_

  - [x] 1.3 Create Follow model
    - Create `app/Models/Follow.php`
    - Set `$fillable = ['follower_id', 'author_id']`
    - Disable timestamps (`public $timestamps = false`), manage `created_at` manually
    - Cast `created_at` to datetime
    - Define `follower()` and `author()` belongsTo relationships to User
    - Follow the same pattern as `Like` and `Bookmark` models
    - _Requirements: 1.1, 1.5_

  - [x] 1.4 Add `following()` and `followers()` relationships to User model
    - Add `following()`: `belongsToMany(User::class, 'follows', 'follower_id', 'author_id')->withPivot('created_at')`
    - Add `followers()`: `belongsToMany(User::class, 'follows', 'author_id', 'follower_id')->withPivot('created_at')`
    - _Requirements: 3.1, 4.1, 7.1_

  - [x] 1.5 Add `notified_at` to Article model `$fillable` array
    - Update `app/Models/Article.php` to include `notified_at` in fillable
    - Add `notified_at` to the casts array as `datetime`
    - _Requirements: 6.7_

- [x] 2. FollowController and API routes
  - [x] 2.1 Create FollowController with `follow` and `unfollow` methods
    - Create `app/Http/Controllers/Api/FollowController.php`
    - `follow(Request $request, int $authorId)`: validate target is author/admin, not self, create follow via `firstOrCreate`, set `created_at` to `now()`, return author id/name/timestamp
    - `unfollow(Request $request, int $authorId)`: find and delete follow record, return success regardless of existence
    - Handle 404 for non-existent user, 422 for non-author target and self-follow
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 2.1, 2.2, 2.4_

  - [x] 2.2 Add `following`, `followers`, and `checkStatus` methods to FollowController
    - `following(Request $request)`: paginated list (10/page) of followed authors sorted by `created_at` desc, include id/name/email/timestamp
    - `followers(Request $request)`: paginated list (15/page) of followers sorted by `created_at` desc, include id/name/timestamp; require AUTHOR or ADMIN role
    - `checkStatus(Request $request, int $authorId)`: return boolean `is_following` and `followed_at` timestamp (or null)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3_

  - [x] 2.3 Register follow API routes in `routes/api.php`
    - `POST /api/authors/{author}/follow` → `FollowController@follow` (auth:sanctum)
    - `DELETE /api/authors/{author}/follow` → `FollowController@unfollow` (auth:sanctum)
    - `GET /api/me/following` → `FollowController@following` (auth:sanctum)
    - `GET /api/me/followers` → `FollowController@followers` (auth:sanctum, role:AUTHOR,ADMIN)
    - `GET /api/authors/{author}/follow-status` → `FollowController@checkStatus` (auth:sanctum)
    - _Requirements: 1.1, 2.1, 3.1, 4.1, 5.1_

  - [ ]* 2.4 Write unit tests for FollowController follow/unfollow
    - Test follow creates relationship and returns correct response
    - Test follow is idempotent (no duplicate)
    - Test follow self returns 422
    - Test follow non-author returns 422
    - Test follow non-existent user returns 404
    - Test unfollow removes relationship
    - Test unfollow non-existent follow returns success
    - Test unfollow non-existent user returns 404
    - Test unauthenticated access returns 401
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.6, 2.1, 2.2, 2.4_

  - [ ]* 2.5 Write unit tests for FollowController listing and status endpoints
    - Test following list returns paginated results (10/page, desc order)
    - Test following list includes id, name, email, timestamp
    - Test empty following list returns empty paginated response
    - Test followers list returns paginated results (15/page, desc order)
    - Test followers list includes id, name, timestamp
    - Test non-author accessing followers returns 403
    - Test check-status returns true with timestamp when following
    - Test check-status returns false with null when not following
    - Test check-status for non-existent/non-author returns 404
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 4.1, 4.2, 4.3, 4.4, 4.5, 5.1, 5.2, 5.3_

  - [ ]* 2.6 Write property test for follow idempotence
    - **Property 2: Follow is idempotent**
    - **Validates: Requirements 1.2**

  - [ ]* 2.7 Write property test for follow rejects non-author targets
    - **Property 3: Follow rejects non-author targets**
    - **Validates: Requirements 1.3**

  - [ ]* 2.8 Write property test for follow-then-unfollow round trip
    - **Property 4: Follow-then-unfollow round trip**
    - **Validates: Requirements 2.1**

  - [ ]* 2.9 Write property test for unfollow idempotence
    - **Property 5: Unfollow is idempotent**
    - **Validates: Requirements 2.2**

- [x] 3. Checkpoint - Ensure follow CRUD works
  - Ensure all tests pass, ask the user if questions arise.

- [x] 4. Event, Listener, and Notification for article publication
  - [x] 4.1 Create ArticlePublished event
    - Create `app/Events/ArticlePublished.php`
    - Accept and store the `Article` model (with author relationship eager-loaded)
    - Use `SerializesModels` trait
    - Do NOT implement `ShouldBroadcast` (this is internal, not broadcast)
    - _Requirements: 6.1_

  - [x] 4.2 Create NewArticleNotification (queued mail)
    - Create `app/Notifications/NewArticleNotification.php`
    - Implement `ShouldQueue` interface
    - Set `$tries = 3` for retry on failure
    - Accept `Article` in constructor
    - Send via `mail` channel
    - Email content: article title, 150-char plain-text excerpt of content, author name, link constructed from slug
    - _Requirements: 6.2, 6.3, 6.4, 6.8_

  - [x] 4.3 Create SendNewArticleNotifications listener
    - Create `app/Listeners/SendNewArticleNotifications.php`
    - Implement `ShouldQueue` interface (queued listener)
    - Handle `ArticlePublished` event
    - Load all followers of the article's author
    - Chunk followers in batches of 100
    - Send `NewArticleNotification` to each follower
    - Update `notified_at` on the article after dispatching
    - _Requirements: 6.1, 6.4, 6.5, 6.6_

  - [x] 4.4 Register event-listener mapping in EventServiceProvider or event discovery
    - Map `ArticlePublished` → `SendNewArticleNotifications`
    - Use Laravel's event discovery or explicit registration in `AppServiceProvider`
    - _Requirements: 6.1_

  - [x] 4.5 Dispatch ArticlePublished event in article publication flow
    - Modify `ArticleController@submit` to dispatch `ArticlePublished` event when article status changes to PUBLISHED and `notified_at` is null
    - Modify `ArticleController@update` to dispatch `ArticlePublished` event when status changes to PUBLISHED and `notified_at` is null
    - Modify `ArticleController@store` to dispatch `ArticlePublished` event when article is created with PUBLISHED status and `notified_at` is null
    - Guard: only fire if `$article->notified_at === null` (prevents re-notification)
    - _Requirements: 6.1, 6.7_

  - [ ]* 4.6 Write unit tests for notification dispatch
    - Test event is dispatched when article is first published via submit
    - Test event is NOT dispatched when article is re-published (notified_at already set)
    - Test listener sends notification to all followers (using Notification::fake)
    - Test listener skips dispatch when author has no followers
    - Test notification contains correct content (title, excerpt ≤150 chars, author name, slug link)
    - Test notification is queued (implements ShouldQueue)
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.7_

  - [ ]* 4.7 Write property test for publication notifies exactly each follower once
    - **Property 9: Publication notifies exactly each follower once**
    - **Validates: Requirements 6.1, 6.6**

  - [ ]* 4.8 Write property test for notification content completeness
    - **Property 10: Notification content completeness**
    - **Validates: Requirements 6.2, 6.3**

  - [ ]* 4.9 Write property test for re-publication does not re-notify
    - **Property 11: Re-publication does not re-notify**
    - **Validates: Requirements 6.7**

  - [ ]* 4.10 Write property test for unfollow stops future notifications
    - **Property 12: Unfollow stops future notifications**
    - **Validates: Requirements 2.3**

- [x] 5. Checkpoint - Ensure notification flow works end-to-end
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Follower count exposure and integration
  - [x] 6.1 Add `followers_count` to author profile and article detail responses
    - Modify `ArticleController@show` to include `withCount('followers')` on the author relation
    - Add a public endpoint or modify existing author-related responses to include follower count
    - Ensure count is 0 when author has no followers
    - _Requirements: 7.1, 7.2, 7.3_

  - [ ]* 6.2 Write unit tests for follower count exposure
    - Test article detail includes author follower count
    - Test follower count is 0 for author with no followers
    - Test follower count reflects actual follow relationships
    - _Requirements: 7.1, 7.2, 7.3_

  - [ ]* 6.3 Write property test for follower count equals active relationship count
    - **Property 13: Follower count equals active relationship count**
    - **Validates: Requirements 7.1, 7.2, 7.3**

- [x] 7. Final checkpoint - Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties from the design document
- Unit tests validate specific examples and edge cases
- All tests use Pest PHP with `RefreshDatabase` trait
- Use `Notification::fake()` and `Event::fake()` for notification/event assertions
- Use `Mail::fake()` for mail-level assertions where needed
- Follow existing project patterns: `Like`/`Bookmark` models for the `Follow` model, `ArticleEngaged` event for the `ArticlePublished` event

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2"] },
    { "id": 1, "tasks": ["1.3", "1.5"] },
    { "id": 2, "tasks": ["1.4"] },
    { "id": 3, "tasks": ["2.1", "2.2"] },
    { "id": 4, "tasks": ["2.3"] },
    { "id": 5, "tasks": ["2.4", "2.5", "2.6", "2.7", "2.8", "2.9"] },
    { "id": 6, "tasks": ["4.1", "4.2"] },
    { "id": 7, "tasks": ["4.3"] },
    { "id": 8, "tasks": ["4.4", "4.5"] },
    { "id": 9, "tasks": ["4.6", "4.7", "4.8", "4.9", "4.10"] },
    { "id": 10, "tasks": ["6.1"] },
    { "id": 11, "tasks": ["6.2", "6.3"] }
  ]
}
```
