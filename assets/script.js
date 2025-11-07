/* ============================================================
   script.js — Main frontend logic for Blog App
   ============================================================ */

const API_BASE = window.location.origin + '/backend/';
// Example: https://learnblog.infinityfreeapp.com/backend/

console.log('API Base →', API_BASE);

// ---------- Generic API Request Helper ----------
async function apiRequest(endpoint, method = 'GET', data = null) {
    const url = API_BASE + endpoint;
    console.log('API Request →', method, url);

    const options = {
        method,
        headers: { 'Content-Type': 'application/json' },
    };

    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, options);

        if (!response.ok) {
            const text = await response.text();
            console.error(`❌ HTTP ${response.status} ${response.statusText}`, text);
            return { success: false, message: `Server returned ${response.status}` };
        }

        const text = await response.text();

        try {
            const json = JSON.parse(text);
            console.log('✅ API Response:', json);
            return json;
        } catch (e) {
            console.error('⚠️ Invalid JSON response from', url, '\nResponse body:\n', text);
            return { success: false, message: 'Invalid JSON response from server.' };
        }

    } catch (error) {
        console.error('🌐 Network error for', url, error);
        return { success: false, message: 'Network error. Please try again later.' };
    }
}

// ---------- Helper Functions ----------
function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function truncateText(text, maxLength) {
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

// ---------- Logout Handling ----------
document.addEventListener('click', async (e) => {
    if (e.target.classList.contains('logout-btn')) {
        e.preventDefault();

        const result = await apiRequest('logout.php', 'POST');

        if (result.success) {
            alert('You have been logged out.');
            window.location.href = 'index.php';
        } else {
            alert(result.message || 'Logout failed.');
        }
    }
});

// ---------- Delete Blog ----------
async function deleteBlog(blogId) {
    if (!confirm('Are you sure you want to delete this blog post?')) return;

    const result = await apiRequest('delete_blog.php', 'POST', { id: blogId });

    if (result.success) {
        alert('Blog deleted successfully!');
        window.location.reload();
    } else {
        alert(result.message || 'Failed to delete blog.');
    }
}

// ---------- Load Blogs (used by index.php) ----------
async function loadBlogs() {
    const container = document.getElementById('blogs-container');
    if (!container) return;

    container.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p>Loading blogs...</p>
        </div>
    `;

    const result = await apiRequest('get_blogs.php');

    if (result.success && Array.isArray(result.data) && result.data.length > 0) {
        const blogsHTML = result.data.map(blog => {
            const isOwner = typeof currentUser !== 'undefined' && currentUser.id === parseInt(blog.user_id);

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
                ${typeof currentUser !== 'undefined'
                    ? '<a href="create_blog.php" class="btn btn-primary">Create the first blog post</a>'
                    : ''}
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="alert alert-error">
                ${result.message || 'Failed to load blogs.'}
            </div>
        `;
    }
}

// ---------- Escape HTML ----------
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

