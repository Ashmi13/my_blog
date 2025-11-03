// --- CUSTOM MODAL IMPLEMENTATION (Replaces alert() and confirm()) ---

// Function to safely create and show a custom modal box
function showCustomModal(message, isConfirm = false, onConfirm = null) {
    const existingModal = document.getElementById('custom-modal-overlay');
    if (existingModal) existingModal.remove();

    // Create the overlay
    const overlay = document.createElement('div');
    overlay.id = 'custom-modal-overlay';
    overlay.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); display: flex; justify-content: center; align-items: center; z-index: 9999;';

    // Create the modal box
    const modalBox = document.createElement('div');
    modalBox.style.cssText = 'background: white; padding: 30px; border-radius: 8px; max-width: 400px; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-family: sans-serif;';

    // Message content
    const messageP = document.createElement('p');
    messageP.textContent = message;
    messageP.style.cssText = 'font-size: 16px; margin-bottom: 20px;';
    modalBox.appendChild(messageP);
    
    // Button container
    const buttonContainer = document.createElement('div');
    buttonContainer.style.cssText = 'display: flex; justify-content: center; gap: 15px; margin-top: 20px;';

    // OK button (for alerts and confirms)
    const okButton = document.createElement('button');
    okButton.textContent = isConfirm ? 'Yes, Delete' : 'OK';
    okButton.className = 'btn'; // Assuming 'btn' is a basic style class
    okButton.style.cssText = 'background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s;';
    
    okButton.onclick = () => {
        closeCustomModal();
        if (isConfirm && onConfirm) {
            onConfirm(true);
        }
    };
    buttonContainer.appendChild(okButton);
    
    // Cancel button (only for confirms)
    if (isConfirm) {
        const cancelButton = document.createElement('button');
        cancelButton.textContent = 'Cancel';
        cancelButton.className = 'btn secondary';
        cancelButton.style.cssText = 'background: #ccc; color: #333; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s;';
        cancelButton.onclick = () => {
            closeCustomModal();
            if (onConfirm) {
                onConfirm(false); // Call with false if user cancels
            }
        };
        buttonContainer.appendChild(cancelButton);
    }

    modalBox.appendChild(buttonContainer);
    overlay.appendChild(modalBox);
    document.body.appendChild(overlay);
}

// Helper to close the modal
function closeCustomModal() {
    const modal = document.getElementById('custom-modal-overlay');
    if (modal) {
        modal.remove();
    }
}

// Public API for replacement
function showCustomAlert(message) {
    showCustomModal(message, false);
}

// Lightweight toast notification (auto-dismiss). type: 'success'|'error'|'info'
function showToast(message, type = 'success', timeout = 3000) {
    // Remove existing toast container if not present
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'toast-notification ' + type;
    toast.style.cssText = 'min-width: 240px; max-width: 360px; background: ' + (type === 'success' ? '#2ecc71' : type === 'error' ? '#e74c3c' : '#3498db') + '; color: white; padding: 12px 16px; border-radius: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-family: sans-serif; display: flex; justify-content: space-between; align-items: center; gap: 10px;';

    const msg = document.createElement('div');
    msg.style.cssText = 'flex: 1; font-size: 14px;';
    msg.innerHTML = message;

    const closeBtn = document.createElement('button');
    closeBtn.textContent = '×';
    closeBtn.style.cssText = 'background: transparent; border: none; color: rgba(255,255,255,0.9); font-size: 18px; cursor: pointer;';
    closeBtn.onclick = () => {
        toast.remove();
    };

    toast.appendChild(msg);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    // Auto-remove after timeout
    setTimeout(() => {
        if (toast.parentNode) toast.remove();
    }, timeout);
}

// Public API for replacement
function showCustomConfirm(message, onConfirm) {
    showCustomModal(message, true, onConfirm);
}


// --- VIEW MANAGEMENT ---

// Function to show/hide different views (from index.html logic)
function showView(viewId) {
    const viewStyles = {
        'home': 'block',
        'login': 'flex',
        'register': 'flex',
        'post-detail': 'block' 
    };

    // Hide all view sections
    document.querySelectorAll('.view').forEach(view => {
        view.style.display = 'none';
    });
    
    // Show the target view with its correct display style
    // NOTE: The view IDs on the page are 'home-view', 'login-view', 'post-detail-view', etc.
    const targetView = document.getElementById(viewId + '-view');
    if (targetView && viewStyles[viewId]) {
        targetView.style.display = viewStyles[viewId];
    } else if (targetView) {
        // Default to block if style is not explicitly defined (safe fallback)
        targetView.style.display = 'block'; 
    }
    
    // If we switch to the home view, try to load the blogs
    if (viewId === 'home') {
        loadHomeBlogs();
    }
}

// Function used on the dashboard to toggle the Create Post form
function toggleForm(formId) {
    // Find the container element by the provided ID. It may be a <form> or a wrapper <div>.
    const container = document.getElementById(formId);
    if (!container) return;

    // Determine the actual form element inside the container (or the container itself if it's a form)
    let formEl = null;
    if (container.tagName && container.tagName.toUpperCase() === 'FORM') {
        formEl = container;
    } else {
        formEl = container.querySelector('form') || document.getElementById('create-post-form');
    }

    // Toggle the display of the container
    const isHidden = container.style.display === 'none' || container.style.display === '';
    container.style.display = isHidden ? 'block' : 'none';

    // When hiding the form, clear fields and reset button state
    if (!isHidden && formEl) {
        // Only call reset if it's actually a form element
        if (typeof formEl.reset === 'function') {
            formEl.reset();
        }

        const submitBtn = (formEl.querySelector && formEl.querySelector('button[type="submit"]')) || container.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.textContent = 'Publish Post';

        const heading = document.getElementById('post-form-title') || (formEl.querySelector && formEl.querySelector('h3'));
        if (heading) {
            heading.textContent = 'Create New Post';
        }

        // Remove the hidden ID field if it exists to reset to 'create' mode
        const hiddenIdField = document.getElementById('post-id-hidden');
        if (hiddenIdField) {
            hiddenIdField.remove();
        }
    }
}


// --- AUTHENTICATION HANDLERS ---

// Handle Registration Form Submission
async function handleRegistration(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    const password = formData.get('password');
    const confirm = formData.get('confirm');
    
    // Replaced alert() with showCustomAlert()
    if (password !== confirm) {
        showCustomAlert("Error: Passwords do not match!");
        return;
    }
    
    try {
        const response = await fetch('backend/register.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            // Replaced alert() with showCustomAlert()
            showCustomAlert(result.message);
            window.location.hash = '#login';
            showView('login');
            form.reset();
        } else {
            // Replaced alert() with showCustomAlert()
            showCustomAlert("Registration Failed: " + result.message);
        }

    } catch (error) {
        console.error('Registration Fetch Error:', error);
        // Replaced alert() with showCustomAlert()
        showCustomAlert('An unexpected network error occurred.');
    }
}


// Handle Login Form Submission
async function handleLogin(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('backend/login.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Login HTTP Error:', response.status, errorText);
            // Replaced alert() with showCustomAlert()
            showCustomAlert(`Login Failed: Server returned status ${response.status}. Check console.`);
            return;
        }

        const result = await response.json();

        if (result.success) {
            // Replaced alert() with showCustomAlert()
            showCustomAlert("Welcome back!");
            // Redirect to the dashboard page upon successful login
            window.location.href = result.redirect; 
        } else {
            // Replaced alert() with showCustomAlert()
            showCustomAlert("Login Failed: " + result.message);
        }

    } catch (error) {
        console.error('Login Fetch Error:', error);
        // Replaced alert() with showCustomAlert()
        showCustomAlert('An unexpected network error occurred.');
    }
}


// --- BLOG READING (Home Page & Single Post) ---

// Function to display a single blog post
async function showSinglePost(postId) {
    showView('post-detail');
    const detailView = document.getElementById('post-detail-view');
    detailView.innerHTML = '<h2>Loading Article...</h2><p style="text-align:center;">Please wait.</p>';

    try {
        const response = await fetch(`backend/api/get_single_blog.php?id=${postId}`);
        const result = await response.json();

        if (result.success) {
            const post = result.post;
            const date = new Date(post.created_at).toLocaleDateString('en-US', {
                year: 'numeric', month: 'long', day: 'numeric'
            });

            // Replace newlines with <br> for HTML display
            const formattedContent = post.content ? post.content.replace(/\n/g, '<br>') : ''; // Safe check

            detailView.innerHTML = `
                <article class="full-post">
                    <h1>${post.title}</h1>
                    <div class="post-meta" style="margin: 20px 0; padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                        <div style="margin-bottom: 10px;">
                            <strong style="font-size: 1.1em;">Written by: ${post.author_name || 'Unknown Author'}</strong>
                        </div>
                        <div style="color: #666;">
                            Published on: ${post.formatted_date || date}
                            ${post.tags ? ` | Tags: ${post.tags}` : ''}
                        </div>
                    </div>
                    <div class="post-content">
                        <p>${formattedContent}</p>
                    </div>
                    <button class="btn secondary" onclick="window.location.hash=''; showView('home');" style="margin-top: 30px;">
                        ← Back to all articles
                    </button>
                </article>
            `;
        } else {
            detailView.innerHTML = `
                <h2 style="color: red;">Error: Article Not Found</h2>
                <p style="text-align:center;">${result.message || 'The requested article could not be loaded.'}</p>
            `;
        }
    } catch (error) {
        console.error('Single Post Fetch Error:', error);
        detailView.innerHTML = '<h2 style="color: red;">Network Error</h2><p style="text-align:center;">Could not connect to the server to load the article.</p>';
    }
}

// Check the URL hash on load or change
function checkUrlHash() {
    const hash = window.location.hash.substring(1); // Get hash without '#'

    if (hash.startsWith('post-')) {
        const postId = hash.split('-')[1];
        if (postId && !isNaN(postId)) {
            showSinglePost(postId);
            return;
        }
    }

    if (['login', 'register'].includes(hash)) {
        showView(hash);
        return;
    }

    showView('home');
}

async function loadHomeBlogs() {
    const blogGrid = document.querySelector('.blog-grid');
    if (!blogGrid) return;

    blogGrid.innerHTML = '<p style="text-align: center; color: #7f8c8d; padding: 40px;">Loading latest posts...</p>';

    try {
        const response = await fetch('backend/api/get_blogs.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();

        if (result.success && result.blogs.length > 0) {
            blogGrid.innerHTML = '';

            result.blogs.forEach(post => {
                const date = new Date(post.created_at).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });

                // Display summary as the first 150 characters of content
                const summary = (post.content || '').substring(0, 150);

                const cardHTML = `
                    <article class="blog-card">
                        <div class="blog-content">
                            <h3>${post.title}</h3>
                            <div class="blog-meta" style="margin-bottom: 10px;">
                                <span>✒️<strong>${post.author_name || 'Unknown Author'}</strong></span> | 
                                <span> 📅 ${post.formatted_date || date}</span>
                            </div>
                            <p>${summary}...</p>
                            <a href="#post-${post.id}" onclick="showSinglePost(${post.id}); return false;" class="btn secondary" style="margin-top: 15px; display: inline-block;">Read Full Article</a>
                        </div>
                    </article>
                `;
                blogGrid.insertAdjacentHTML('beforeend', cardHTML);
            });
        } else {
            blogGrid.innerHTML = '<p style="text-align: center; color: #7f8c8d; padding: 40px;">No articles published yet. Be the first!</p>';
        }

    } catch (error) {
        console.error('Error fetching blogs:', error);
        blogGrid.innerHTML = '<p style="color: red; text-align: center; padding: 40px;">Failed to load blog posts due to a network error. (Check console for details)</p>';
    }
}


// --- BLOG MANAGEMENT (Dashboard) ---

async function handleCreatePost(event) {
    event.preventDefault();
    const form = event.target;
    // CRITICAL: We now check the hidden field in the DOM
    const hiddenIdField = document.getElementById('post-id-hidden');
    const postId = hiddenIdField ? hiddenIdField.value : null;
    const isUpdate = !!postId;

    // Determine API endpoint and success message based on operation
    const apiEndpoint = isUpdate ? 'backend/api/update_blog.php' : 'backend/api/create_blog.php';
    const successMsg = isUpdate ? 'Blog post updated successfully!' : 'Blog post published successfully!';
    const failureMsg = isUpdate ? 'Update Failed: ' : 'Publishing Failed: ';

    // CRITICAL: Collect form data manually and structure as JSON
    const postData = {
        title: document.getElementById('post-title').value,
        content: document.getElementById('post-content').value,
        tags: document.getElementById('post-tags').value,
        ...(isUpdate && { id: postId }) // Only include ID if updating
    };
    
    // Simple client-side validation for required fields
    if (!postData.title || !postData.content) {
        // Replaced alert() with showCustomAlert()
        showCustomAlert('Title and Content are required fields.');
        return;
    }


    try {
        const response = await fetch(apiEndpoint, {
            method: 'POST',
            // CRITICAL: Send data as JSON and include same-origin credentials so PHP session cookie is sent
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(postData) // Send JSON data
        });
        
        let result;
        try {
            result = await response.json();
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Non-JSON Response:', await response.text());
            // Replaced alert() with showCustomAlert()
            showCustomAlert('Failed to process server response. Check console for raw output.');
            return;
        }

        // Debug: log status and parsed result to help trace intermittent client-side failures
        console.log('Create post response status:', response.status, 'parsed result:', result);

        // Handle successful response with potential application-level failure
        if (response.ok && result.success) {
            // Disable submit button to prevent duplicate requests
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            // Refresh the user's posts list and wait for it to finish so the dashboard shows the new post immediately
            console.log("Post created, refreshing user posts list...");
            const refreshed = await loadUserPosts();

            if (refreshed) {
                // Show concise toast with the post title so UX is clearer
                showToast((isUpdate ? 'Updated: ' : 'Published: ') + postData.title, 'success', 3500);
            } else {
                showToast((isUpdate ? 'Updated: ' : 'Published: ') + postData.title + ' (Created, but failed to refresh dashboard.)', 'success', 5000);
            }

            // Reset the form and UI state
            form.reset();
            // Remove hidden id if present (back to create mode)
            const hiddenIdField = document.getElementById('post-id-hidden');
            if (hiddenIdField) hiddenIdField.remove();
            if (submitBtn) submitBtn.disabled = false;
            // Hide the create form
            toggleForm('create-form');

        } else if (response.ok && !result.success) {
            // Handle expected PHP failure messages (e.g., validation failed)
            // Replaced alert() with showCustomAlert()
            showCustomAlert(failureMsg + result.message);
        } else {
            // Handle non-200 responses (response.ok is false)
            // Replaced alert() with showCustomAlert()
            showCustomAlert(failureMsg + (result.message || `Server error (Status: ${response.status})`));
        }

    } catch (error) {
        // Catch network errors (e.g., DNS error, CORS)
        console.error('Post Fetch Error (Network/Uncaught):', error);
        // Show a clearer toast with the real error message so user can report it
        const msg = error && error.message ? error.message : 'An unexpected network error occurred.';
        showToast('Network error: ' + msg, 'error', 6000);
    }
}


// Handles the deletion of a blog post
async function handleDeletePost(blogId) {
    // CRITICAL FIX: Use a custom modal or window.confirm outside of file generation
    // Now uses showCustomConfirm() instead of confirm()
    showCustomConfirm("Are you sure you want to permanently delete this post?", async (confirmed) => {
        if (!confirmed) { 
            return;
        }

        const deleteData = { id: blogId };

        try {
            const response = await fetch('backend/api/delete_blog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(deleteData)
            });

            // CRITICAL: Check for non-200 responses here too
            if (!response.ok) {
                 const errorResult = await response.json();
                 // Replaced alert() with showCustomAlert()
                 showCustomAlert("Deletion Failed: " + (errorResult.message || `Server error (Status: ${response.status})`));
                 return;
            }

            const result = await response.json();

            if (result.success) {
                // Replaced alert() with showCustomAlert()
                showCustomAlert(result.message);
                // 🔥 FINAL FIX: Stop the full page reload and use a delayed function call.
                console.log("Delete operation successful. Delaying post list load to prevent connection conflict...");
                setTimeout(loadUserPosts, 500);

            } else {
                // Replaced alert() with showCustomAlert()
                showCustomAlert("Deletion Failed: " + result.message);
            }
        } catch (error) {
            console.error('Delete Post Fetch Error:', error);
            // Replaced alert() with showCustomAlert()
            showCustomAlert('An unexpected error occurred while deleting.');
        }
    });
}

// Fetches a single post's content and prepares the form for editing
async function loadPostForEdit(blogId) {
    const form = document.getElementById('create-post-form');
    const formTitle = document.getElementById('post-form-title');

    try {
        const response = await fetch(`backend/api/get_single_blog.php?id=${blogId}`);
        
        // CRITICAL: Check response status for fetching single post
        if (!response.ok) {
             // Attempt to read the error body if it's JSON
             let errorResult;
             try {
                errorResult = await response.json();
             } catch (e) {
                // If it's not JSON (e.g., a server crash page), use the status
                errorResult = { message: `Server error (Status: ${response.status})` };
             }
             // Replaced alert() with showCustomAlert()
             showCustomAlert("Error fetching post for editing: " + (errorResult.message || `Server error (Status: ${response.status})`));
             return;
        }
        
        const result = await response.json();

        if (result.success) {
            const post = result.post;
            
            // 1. Populate the form fields with the post data
            document.getElementById('post-title').value = post.title;
            document.getElementById('post-content').value = post.content;
            document.getElementById('post-tags').value = post.tags || ''; 
            
            // 2. CRITICAL: Add or update the hidden field to store the post ID
            let hiddenIdField = document.getElementById('post-id-hidden');
            if (!hiddenIdField) {
                // The form element itself is often a good place to insert this
                form.insertAdjacentHTML('afterbegin', `<input type="hidden" name="id" id="post-id-hidden">`);
                hiddenIdField = document.getElementById('post-id-hidden');
            }
            hiddenIdField.value = post.id; // Assign the ID

            // 3. Update the form display for editing
            form.querySelector('button[type="submit"]').textContent = 'Update Post';
            formTitle.textContent = 'Edit Existing Post';
            
            // 4. Show the form
            toggleForm('create-form');
            
        } else {
            // Replaced alert() with showCustomAlert()
            showCustomAlert("Error fetching post for editing: " + result.message);
        }

    } catch (error) {
        console.error('Fetch Post for Edit Error:', error);
        // If an error is caught here, it's a network issue before response, which should be logged but not alerted to prevent flash errors
    }
}

// Handles the click events for all action buttons (Edit/Delete) on the dashboard
function handlePostActions(event) {
    let target = event.target;
    // Only proceed if the click was on an action button
    if (target.classList.contains('delete-btn') || target.classList.contains('edit-btn')) {
        const postId = target.dataset.id; 

        if (!postId) {
            // Replaced alert() with showCustomAlert()
            showCustomAlert('Error: Post ID not found on button.');
            return;
        }

        if (target.classList.contains('delete-btn')) {
            // CRITICAL FIX: Call the async delete handler directly
            handleDeletePost(postId); 
        }
        
        if (target.classList.contains('edit-btn')) {
            loadPostForEdit(postId);
        }
    }
}


// Fetches and displays only the logged-in user's posts
async function loadUserPosts() {
    const postList = document.getElementById('user-posts-list');
    const welcomeHeader = document.getElementById('author-welcome');
    if (!postList) return false;

    // Replace the list element to remove old listeners
    const oldPostList = postList.cloneNode(true);
    postList.parentNode.replaceChild(oldPostList, postList);
    const newPostList = document.getElementById('user-posts-list');

    newPostList.innerHTML = '<li>Loading your posts...</li>';

    try {
        const response = await fetch('backend/api/get_user_blogs.php', { credentials: 'same-origin' });

        if (!response.ok) {
            if (response.status === 403) {
                newPostList.innerHTML = '<li><p style="color: red;">Authentication Failed. Please log out and log in again.</p></li>';
            } else {
                newPostList.innerHTML = `<li><p style="color: red;">Failed to load posts (Status: ${response.status}).</p></li>`;
            }
            return false;
        }

        const result = await response.json();

        if (!result.success) {
            newPostList.innerHTML = '<li><p style="color: red;">Failed to load your blog posts: ' + (result.message || 'Unknown error.') + '</p></li>';
            return false;
        }

        if (welcomeHeader && result.username) {
            welcomeHeader.textContent = `Welcome back, ${result.username}!`;
        }

        if (result.blogs && result.blogs.length > 0) {
            newPostList.innerHTML = '';
            result.blogs.forEach(post => {
                const date = new Date(post.created_at).toLocaleDateString('en-US');
                const postItem = `
                    <li class="dashboard-post-item">
                        <div class="post-details">
                            <h4>${post.title}</h4>
                            <div class="post-meta">
                                <span class="post-author">Author: ${result.username || 'You'}</span> | 
                                <span class="post-date">Published: ${date}</span>
                            </div>
                        </div>
                        <div class="post-actions">
                            <button class="btn secondary edit-btn" data-id="${post.id}">Edit</button>
                            <button class="btn delete-btn" data-id="${post.id}">Delete</button>
                        </div>
                    </li>
                `;
                newPostList.insertAdjacentHTML('beforeend', postItem);
            });
            newPostList.addEventListener('click', handlePostActions);
            return true;
        } else {
            newPostList.innerHTML = '<p id="empty-message" style="color: #7f8c8d; text-align: center; padding: 30px; border: 1px dashed #ccc; border-radius: 5px;">You haven\'t published any posts yet. Click "Create New Post" to get started!</p>';
            return true;
        }

    } catch (error) {
        console.error('Error fetching user blogs:', error);
        newPostList.innerHTML = '<li><p style="color: red;">Failed to load your blog posts.</p></li>';
        return false;
    }
}


// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', () => {
    // 1. Attach event listener to the Register form
    const regForm = document.getElementById('register-form');
    if (regForm) {
        regForm.addEventListener('submit', handleRegistration);
    }

    // 2. Attach event listener to the Login form
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // 3. Attach listener for the Create Post form (only present on dashboard.html)
    const createForm = document.getElementById('create-post-form');
    if (createForm) {
        createForm.addEventListener('submit', handleCreatePost);
        
        // Load posts only if we are on the dashboard
        if (document.getElementById('user-posts-list')) {
            // CRITICAL FIX (STAGE 3): Delay the loading of user posts. 
            // This gives the aggressive local server configuration time to
            // close the prior network connection after the forced reload.
            console.log("Delaying loadUserPosts to avoid local server network conflict...");
            setTimeout(loadUserPosts, 500); 
        }
    }
    
    // 4. Attach listener for the "Create New Post" button to toggle the form
    const createNewPostBtn = document.getElementById('create-new-post-btn');
    if (createNewPostBtn) {
        createNewPostBtn.addEventListener('click', () => toggleForm('create-form'));
    }

    // 5. Initial view logic (Home, Login, Register, or Single Post based on URL hash)
    window.addEventListener('hashchange', checkUrlHash);
    checkUrlHash(); // Run on initial page load
});
