#API (Prototype)

**Objects**
```php
$user = new User(<facebook_id>);
$user->sync(); // Sync all user informations
$user->fetch(); // Get user information from facebook but not sync
$user->get(); // Get user information from local database
$user = User::find(<facebook_id>);
$user = User::findOrSync(<facebook_id>);
$user = User::findOrFetch(<facebook_id>);
```

**Edges**
```php
$pages = $user->accounts; // Get all user's pages from local database
$user->accounts()->sync(); // Sync user's pages from facebook

$page = $pages[0]; // Return Page instance
$page->sync(); // All object api is the same
$page->posts; // Get all page's posts from local database
$page->posts()->sync(); // Sync page's posts from facebook
```

**Action**
```php
// Comment
$user->leave($message)->on($post); // Leave comment
$user->leave($message)->on($comment); // Leave comment

// Like & Unline
$user->like($comment);
$user->like($post);
$user->unlike($comment);

// Hide & Unhide
$user->hide($comment);
$user->unhide($comment);

// Delete
$user->delete($comment);
$user->delete($post);

// Publish
$user->publish($message)->on($page); // Publish Post

// Schedule
$user->schedule($post, $published_at);
```

**Advanced Query**
```php
$post->page; // Get page of post
```

**Supported Edges**
```php
$user->accounts
```

```php
$post->likes
$post->comments
```

```php
$comment->likes
$comment->comments
```

# Graph
```
(user:User)-[r:MANAGE]->(page:Page)
             r.access_token

(user:User)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)
(user:User)-[:LEAVE]->(comment:Comment)-[:ON]->(comment:Comment)
(page:Page)-[:LEAVE]->(comment:Comment)-[:ON]->(post:Post)
(page:Page)-[:LEAVE]->(comment:Comment)-[:ON]->(comment:Comment)

(user:User)-[r:LIKE]->(post:Post)
             r.created_time
(user:User)-[r:LIKE]->(comment:Comment)
             r.created_time
(page:Page)-[r:LIKE]->(post:Post)
             r.created_time
(page:Page)-[r:LIKE]->(comment:Comment)
             r.created_time

(user:User)-[:PUBLISH]->(post:Post)-[:ON]->(page:Page)
(app:Application)-[:PUBLISH]->(post:Post)-[:ON]->(page:Page)
```

# RDBS

