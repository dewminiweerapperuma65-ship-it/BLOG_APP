<?php
/**
 * Homepage - Display all blog posts
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog App - Home</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <h1><a href="index.php">Blog App</a></h1>
            <ul>
                <li><a href="index.php">Home</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="create_blog.php">Create Blog</a></li>
                    <li class="user-info">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        <a href="#" class="logout-btn">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container">
            <div class="page-header">
                <h1>All Blog Posts</h1>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="create_blog.php" class="btn btn-primary">Create New Blog</a>
                <?php endif; ?>
            </div>

            <!-- Blog posts will be loaded here -->
            <div id="blogs-container">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading blogs...</p>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/script.js"></script>
    <script>
        <script src="assets/script.js"></script>
<script>
    // ✅ Always define currentUser safely
    let currentUser = null;

    // ✅ If user is logged in, set the real data
    <?php if (isset($_SESSION['user_id'])): ?>
    currentUser = {
        id: <?php echo (int)$_SESSION['user_id']; ?>,
        username: '<?php echo htmlspecialchars($_SESSION['username']); ?>',
        role: '<?php echo htmlspecialchars($_SESSION['role']); ?>'
    };
    <?php endif; ?>

    // ✅ Load all blogs
    async function loadBlogs() {
        const container = document.getElementById('blogs-container');
        const result = await apiRequest('get_blogs.php');

        if (result.success && result.data.length > 0) {
            const blogsHTML = result.data.map(blog => {
                const isOwner = currentUser && currentUser.id === parseInt(blog.user_id);

                return `
                    <div class="blog-card">
                        <h3><a href="view_blog.php?id=${blog.id}">${escapeHtml(blog.title)}</a></h3>
                        <div class="blog-meta">
                            By ${escapeHtml(blog.author_name)} |
                            ${formatDate(blog.created_at)} |
                            ${blog.comment_count} comment(s)
                        </div>
                        <div class="blog-content">
                            ${escapeHtml(truncateText(blog.content, 200))}
                        </div>
                        <div class="blog-actions">
                            <a href="view_blog.php?id=${blog.id}" class="btn btn-primary">Read More</a>
                            ${isOwner ? `
                                <a href="create_blog.php?id=${blog.id}" class="btn btn-secondary">Edit</a>
                                <button onclick="deleteBlog(${blog.id})" class="btn btn-danger">Delete</button>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');

            container.innerHTML = `<div class="blog-grid">${blogsHTML}</div>`;
        } else if (result.success && result.data.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <p>No blog posts yet.</p>
                    ${currentUser ? '<a href="create_blog.php" class="btn btn-primary">Create the first blog post</a>' : ''}
                </div>
            `;
        } else {
            container.innerHTML = `
                <div class="alert alert-error">
                    ${result.message || 'Failed to load blogs'}
                </div>
            `;
        }
    }

    // ✅ Escape HTML safely
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ✅ Load blogs on page load
    loadBlogs();
</script>

    </script>
</body>
</html>
