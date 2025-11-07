<?php
/**
 * Create/Edit Blog Post Page
 */
session_start();

// Require authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if editing existing blog
$isEdit = isset($_GET['id']) && !empty($_GET['id']);
$blogId = $isEdit ? (int)$_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Create'; ?> Blog - Blog App</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container">
            <h1><a href="index.php">Blog App</a></h1>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="create_blog.php">Create Blog</a></li>
                <li class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="#" class="logout-btn">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="form-container" style="max-width: 800px;">
            <h2><?php echo $isEdit ? 'Edit' : 'Create'; ?> Blog Post</h2>
            <div id="message"></div>
            
            <!-- Loading indicator for edit mode -->
            <?php if ($isEdit): ?>
            <div id="loading" class="loading">
                <p>Loading blog post...</p>
            </div>
            <?php endif; ?>

            <form id="blogForm" style="<?php echo $isEdit ? 'display: none;' : ''; ?>">
                <input type="hidden" id="blogId" value="<?php echo $blogId; ?>">
                
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required minlength="3" maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="content">Content *</label>
                    <textarea id="content" name="content" required minlength="10" rows="15"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">
                        <?php echo $isEdit ? 'Update' : 'Publish'; ?> Blog
                    </button>
                    <a href="index.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/script.js"></script>
    <script>
        const form = document.getElementById('blogForm');
        const messageDiv = document.getElementById('message');
        const isEdit = <?php echo $isEdit ? 'true' : 'false'; ?>;
        const blogId = <?php echo $blogId ? $blogId : 'null'; ?>;

        // Helper function to escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load blog data if editing
        if (isEdit && blogId) {
            loadBlogForEdit();
        }

        async function loadBlogForEdit() {
            const result = await apiRequest(`get_single_blog.php?id=${blogId}`);
            
            if (result.success) {
                const blog = result.data;
                
                // Check if user owns this blog
                const currentUserId = <?php echo $_SESSION['user_id']; ?>;
                if (blog.user_id !== currentUserId) {
                    messageDiv.innerHTML = '<div class="alert alert-error">You do not have permission to edit this blog post.</div>';
                    document.getElementById('loading').style.display = 'none';
                    return;
                }
                
                // Populate form
                document.getElementById('title').value = blog.title;
                document.getElementById('content').value = blog.content;
                
                // Show form
                document.getElementById('loading').style.display = 'none';
                form.style.display = 'block';
            } else {
                messageDiv.innerHTML = `<div class="alert alert-error">${result.message || 'Failed to load blog post'}</div>`;
                document.getElementById('loading').style.display = 'none';
            }
        }

        // Handle form submission
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const title = document.getElementById('title').value;
            const content = document.getElementById('content').value;

            // Clear previous messages
            messageDiv.innerHTML = '';

            // Prepare data
            const data = {
                title: title,
                content: content
            };

            // Determine endpoint
            let endpoint = 'add_blog.php';
            if (isEdit && blogId) {
                endpoint = 'update_blog.php';
                data.id = blogId;
            }

            // Send request
            const result = await apiRequest(endpoint, 'POST', data);

            if (result.success) {
                messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                
                // Redirect after success
                setTimeout(() => {
                    if (isEdit) {
                        window.location.href = `view_blog.php?id=${blogId}`;
                    } else {
                        window.location.href = `view_blog.php?id=${result.data.id}`;
                    }
                }, 1500);
            } else {
                messageDiv.innerHTML = `<div class="alert alert-error">${result.message}</div>`;
            }
        });
    </script>
</body>
</html>
