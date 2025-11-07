<?php
/**
 * View Single Blog Post with Comments
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Blog - Blog App</title>
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
            <!-- Blog post will be loaded here -->
            <div id="blog-container">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading blog post...</p>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="comments-section" id="comments-section" style="display: none;">
                <h2>Comments</h2>
                
                <!-- Comment form for logged-in users -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div id="comment-form-container">
                    <form id="commentForm">
                        <div class="form-group">
                            <textarea id="commentContent" name="content" placeholder="Write your comment..." rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Post Comment</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="login.php">login</a> to post comments.
                </div>
                <?php endif; ?>

                <!-- Comments list -->
                <div id="comments-container" style="margin-top: 2rem;">
                    <div class="loading">
                        <p>Loading comments...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/script.js"></script>
    <script>
        // ✅ Always define currentUser safely
        let currentUser = null;

        // ✅ Inject session data if user is logged in
        <?php if (isset($_SESSION['user_id'])): ?>
        currentUser = {
            id: <?php echo (int)$_SESSION['user_id']; ?>,
            username: '<?php echo htmlspecialchars($_SESSION['username']); ?>',
            role: '<?php echo htmlspecialchars($_SESSION['role']); ?>'
        };
        <?php endif; ?>

        // ✅ Get blog ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const blogId = urlParams.get('id');

        if (!blogId) {
            window.location.href = 'index.php';
        }

        // ✅ Escape HTML helper
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // ✅ Load blog post
        async function loadBlog() {
            const container = document.getElementById('blog-container');
            const result = await apiRequest(`get_single_blog.php?id=${blogId}`);

            if (result.success && result.data) {
                const blog = result.data;
                const isOwner = currentUser && currentUser.id === parseInt(blog.user_id);

                container.innerHTML = `
                    <div class="blog-detail">
                        <h1>${escapeHtml(blog.title)}</h1>
                        <div class="blog-meta">
                            By ${escapeHtml(blog.author_name)} | 
                            ${formatDate(blog.created_at)}
                            ${blog.updated_at !== blog.created_at ? ` | Updated: ${formatDate(blog.updated_at)}` : ''}
                        </div>
                        <div class="blog-content">${escapeHtml(blog.content)}</div>
                        <div class="blog-actions">
                            <a href="index.php" class="btn btn-secondary">Back to Home</a>
                            ${isOwner ? `
                                <a href="create_blog.php?id=${blog.id}" class="btn btn-primary">Edit</a>
                                <button onclick="deleteBlog(${blog.id})" class="btn btn-danger">Delete</button>
                            ` : ''}
                        </div>
                    </div>
                `;

                // Show comments section
                document.getElementById('comments-section').style.display = 'block';
                loadComments(blogId);
            } else {
                container.innerHTML = `
                    <div class="alert alert-error">
                        ${result.message || 'Failed to load blog post.'}
                    </div>
                    <a href="index.php" class="btn btn-secondary">Back to Home</a>
                `;
            }
        }

        // ✅ Load comments
        async function loadComments(blogId) {
            const container = document.getElementById('comments-container');
            const result = await apiRequest(`get_comments.php?blog_id=${blogId}`);

            if (result.success && result.data.length > 0) {
                container.innerHTML = result.data.map(comment => {
                    const isOwner = currentUser && currentUser.id === parseInt(comment.user_id);
                    return `
                        <div class="comment">
                            <div class="comment-header">
                                <span class="comment-author">${escapeHtml(comment.author_name)}</span>
                                <span class="comment-date">${formatDate(comment.created_at)}</span>
                            </div>
                            <div class="comment-content">${escapeHtml(comment.content)}</div>
                            ${isOwner ? `
                                <div class="comment-actions">
                                    <button onclick="deleteComment(${comment.id}, ${blogId})" class="btn btn-danger" style="padding:0.4rem 0.8rem;font-size:0.9rem;">Delete</button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
            } else if (result.success && result.data.length === 0) {
                container.innerHTML = '<p style="color:#7f8c8d;">No comments yet. Be the first to comment!</p>';
            } else {
                container.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to load comments.'}</div>`;
            }
        }

        // ✅ Handle comment submission
        <?php if (isset($_SESSION['user_id'])): ?>
        const commentForm = document.getElementById('commentForm');
        commentForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = document.getElementById('commentContent').value;

            const result = await apiRequest('add_comment.php', 'POST', {
                blog_id: blogId,
                content: content
            });

            if (result.success) {
                showAlert(result.message, 'success');
                document.getElementById('commentContent').value = '';
                loadComments(blogId);
            } else {
                showAlert(result.message, 'error');
            }
        });
        <?php endif; ?>

        // ✅ Load blog post on page load
        loadBlog();
    </script>
</body>
</html>
