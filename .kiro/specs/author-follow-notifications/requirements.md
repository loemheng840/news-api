# Requirements Document

## Introduction

This feature adds an "Author Follow" system to the news API. Authenticated users can follow authors they are interested in. When a followed author publishes a new article, all followers receive an email notification. The feature includes API endpoints for managing follow relationships and querying followed authors.

## Glossary

- **System**: The Laravel news API application
- **Follower**: An authenticated user who has chosen to follow an author
- **Author**: A user with the AUTHOR or ADMIN role who publishes articles
- **Follow_Relationship**: A record linking a Follower to an Author they follow
- **Notification_Service**: The component responsible for dispatching email notifications to followers
- **Follow_Controller**: The API controller handling follow and unfollow operations
- **Article_Publication**: The event of an article's status transitioning to PUBLISHED

## Requirements

### Requirement 1: Follow an Author

**User Story:** As an authenticated user, I want to follow an author, so that I can receive updates when the author publishes new articles.

#### Acceptance Criteria

1. WHEN an authenticated user sends a follow request for an author, THE System SHALL create a Follow_Relationship between the Follower and the Author and return a successful response containing the Author's id, name, and the follow timestamp
2. WHEN an authenticated user sends a follow request for an author the user already follows, THE System SHALL return a successful response containing the existing Follow_Relationship data without creating a duplicate Follow_Relationship
3. IF the target user does not have the AUTHOR or ADMIN role, THEN THE System SHALL return a 422 validation error indicating the target user is not an author
4. IF the authenticated user attempts to follow themselves, THEN THE System SHALL return a 422 validation error indicating a user cannot follow themselves
5. WHEN a Follow_Relationship is created, THE System SHALL record the timestamp of the follow action in UTC
6. IF the target user ID does not exist, THEN THE System SHALL return a 404 error indicating the user was not found

### Requirement 2: Unfollow an Author

**User Story:** As an authenticated user, I want to unfollow an author, so that I stop receiving notifications about their new articles.

#### Acceptance Criteria

1. WHEN an authenticated user sends an unfollow request for an author they currently follow, THE System SHALL remove the Follow_Relationship between the Follower and the Author and return a successful response within 500 milliseconds
2. WHEN an authenticated user sends an unfollow request for an author they do not follow, THE System SHALL return a successful response without error, identical in structure to a successful unfollow
3. WHEN a Follow_Relationship is removed, THE System SHALL stop sending Article_Publication notifications from that Author to the Follower, excluding any notifications already dispatched to the queue prior to removal
4. IF the target user_id does not correspond to an existing user, THEN THE System SHALL return an error response indicating the user was not found

### Requirement 3: List Followed Authors

**User Story:** As an authenticated user, I want to see a list of authors I follow, so that I can manage my subscriptions.

#### Acceptance Criteria

1. WHEN an authenticated user requests their followed authors list, THE System SHALL return a paginated list of authors the user follows, with a default page size of 10 results per page, sorted by follow timestamp in descending order (most recent first)
2. THE System SHALL include each followed Author's id, name, and email in the response
3. THE System SHALL include the follow timestamp for each Follow_Relationship in the response
4. WHEN the user follows no authors, THE System SHALL return an empty paginated list with a total count of 0
5. IF an unauthenticated user requests the followed authors list, THEN THE System SHALL return a 401 unauthorized error without exposing any follow data

### Requirement 4: List Author Followers

**User Story:** As an author, I want to see a list of users who follow me, so that I can understand my audience.

#### Acceptance Criteria

1. WHEN an author requests their followers list, THE System SHALL return a paginated list of users who follow that Author, with a default page size of 15 results, ordered by follow timestamp descending (most recent followers first)
2. THE System SHALL include each Follower's id and name in the response
3. THE System SHALL include the follow timestamp for each Follow_Relationship in the response
4. WHEN the author has no followers, THE System SHALL return an empty paginated list
5. IF the requesting user does not have the AUTHOR or ADMIN role, THEN THE System SHALL return a 403 error indicating the user is not authorized to view a followers list

### Requirement 5: Check Follow Status

**User Story:** As an authenticated user, I want to check if I follow a specific author, so that I can display the correct follow/unfollow button in the UI.

#### Acceptance Criteria

1. WHEN an authenticated user requests the follow status for a specific author, THE System SHALL return a boolean indicating whether the user follows that Author and, if the Follow_Relationship exists, include the follow timestamp
2. IF the Follow_Relationship does not exist, THEN THE System SHALL return the boolean as false and the follow timestamp as null
3. IF the specified author does not exist or does not have the AUTHOR or ADMIN role, THEN THE System SHALL return a 404 error indicating the author was not found

### Requirement 6: Email Notification on Article Publication

**User Story:** As a follower, I want to receive an email notification when an author I follow publishes a new article, so that I stay informed about new content.

#### Acceptance Criteria

1. WHEN an Article_Publication event occurs for the first time for a given Article, THE Notification_Service SHALL send an email notification to each Follower of the Article's Author
2. THE Notification_Service SHALL include the article title, a plain-text excerpt of the article content limited to 150 characters, and the author name in the email
3. THE Notification_Service SHALL include a link to the published article constructed from the article's slug in the email
4. THE Notification_Service SHALL dispatch email notifications via the queue to avoid blocking the publication request
5. IF the Author has no followers, THEN THE Notification_Service SHALL skip notification dispatch without error
6. THE Notification_Service SHALL send one email per Follower per Article_Publication event (no duplicate emails)
7. IF an Article's status transitions from PUBLISHED to DRAFT and back to PUBLISHED, THEN THE Notification_Service SHALL NOT send a second notification for that Article
8. IF email delivery to a Follower fails, THEN THE Notification_Service SHALL retry delivery up to 3 attempts before marking the notification as failed without affecting other Followers' notifications

### Requirement 7: Follower Count on Author Profile

**User Story:** As a user, I want to see how many followers an author has, so that I can gauge the author's popularity.

#### Acceptance Criteria

1. WHEN any user (authenticated or unauthenticated) requests an author's profile or article detail, THE System SHALL include the total follower count for that Author as a non-negative integer
2. THE System SHALL compute the follower count from existing Follow_Relationships only (a Follow_Relationship is considered active if it has not been removed via an unfollow action)
3. IF the Author has no Follow_Relationships, THEN THE System SHALL return a follower count of 0
