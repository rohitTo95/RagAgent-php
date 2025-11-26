<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dim">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>RAG Chat - AI Document Assistant</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom scrollbar for a premium feel */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent; 
        }
        ::-webkit-scrollbar-thumb {
            background: oklch(var(--bc) / 0.2); 
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: oklch(var(--bc) / 0.4); 
        }
        
        /* Glassmorphism utilities */
        .glass-panel {
            background: oklch(var(--b1) / 0.7);
            backdrop-filter: blur(10px);
        }
        
        /* Bootstrap Icons sizing */
        .chat-image i {
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body class="h-screen overflow-hidden bg-base-300 flex flex-col font-sans antialiased selection:bg-primary selection:text-primary-content">
    
    <!-- Navbar -->
    <div class="navbar bg-base-100/80 backdrop-blur-md shadow-sm z-20 h-16 flex-none px-6 border-b border-base-content/5">
        <div class="flex gap-3 items-center justify-between w-full">
            <div class="flex gap-2 items-center">
                <div class="bg-primary/10 p-2 rounded-lg text-primary">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                 <span class="font-bold text-xl tracking-tight">RAG<span class="text-primary">Agent</span></span>
            </div>
           
        </div>
        <div class="flex-none">
            <!-- Theme Toggle Button -->
            <button id="theme-toggle" class="btn btn-ghost btn-circle btn-sm" aria-label="Toggle theme">
                <!-- Sun icon (visible in dark mode) -->
                <svg id="theme-icon-sun" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <!-- Moon icon (visible in light mode) -->
                <svg id="theme-icon-moon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden container mx-auto p-4 gap-6 max-w-7xl">
        
        <!-- Sidebar (Tabs: Upload / History) -->
        <aside class="w-80 flex flex-col bg-base-100 rounded-2xl shadow-xl overflow-hidden flex-none border border-base-content/5">
            <!-- Tabs Header -->
            <div role="tablist" class="tabs tabs-bordered grid grid-cols-2 bg-base-100">
                <a role="tab" class="tab tab-active h-12 font-medium transition-colors" id="tab-upload" onclick="switchTab('upload')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Documents
                </a>
                <a role="tab" class="tab h-12 font-medium transition-colors" id="tab-history" onclick="switchTab('history')">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    History
                </a>
            </div>

            <!-- Tab Content: Upload -->
            <div id="content-upload" class="flex-1 overflow-y-auto p-4 flex flex-col gap-4">
                <!-- Upload Form -->
                <div class="card bg-base-200/50 border border-base-300 shadow-sm">
                    <div class="card-body p-4 gap-3">
                        <h3 class="card-title text-sm opacity-70 uppercase tracking-wider">Add Knowledge</h3>
                        
                        <div class="form-control w-full">
                            <input type="file" id="document-input" accept=".pdf" class="file-input file-input-bordered file-input-primary file-input-sm w-full" />
                        </div>

                        <button id="upload-btn" class="btn btn-primary btn-sm w-full">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            Upload PDF
                        </button>
                        <div id="upload-status" class="text-xs"></div>
                    </div>
                </div>
                
                <!-- Document List -->
                <div class="flex-1 flex flex-col">
                    <h3 class="text-xs font-bold opacity-50 uppercase tracking-wider mb-3 px-1">Available Documents</h3>
                    <div id="documents-list" class="space-y-2 flex-1 overflow-y-auto pr-1">
                        <div class="skeleton h-10 w-full rounded-lg"></div>
                        <div class="skeleton h-10 w-full rounded-lg"></div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: History -->
            <div id="content-history" class="flex-1 overflow-y-auto p-4 hidden flex flex-col gap-4">
                <button onclick="startNewChat()" class="btn btn-outline btn-primary btn-sm w-full gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Chat
                </button>
                <div id="history-list" class="flex-1 flex flex-col gap-2">
                    <!-- History items will go here -->
                </div>
                <div id="history-empty" class="flex flex-col items-center justify-center h-full text-base-content/50 gap-2 hidden">
                    <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm">No chat history yet.</p>
                </div>
            </div>
        </aside>

        <!-- Chat Area -->
        <main class="flex-1 flex flex-col bg-base-100 rounded-2xl shadow-xl overflow-hidden relative border border-base-content/5">
            <!-- Chat Header (Optional, for context) -->
            <div class="h-14 border-b border-base-200 flex items-center px-6 bg-base-100/50 backdrop-blur-sm z-10">
                <div class="flex items-center gap-2">
                    <div class="badge badge-success badge-xs"></div>
                    <span class="font-medium text-sm">AI Assistant Online</span>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" class="flex-1 overflow-y-auto p-6 space-y-6 scroll-smooth">
                <!-- Empty State -->
                <div id="empty-state" class="flex flex-col items-center justify-center h-full text-base-content/30 gap-6 transition-opacity duration-500">
                    <div class="p-6 bg-base-200/50 rounded-full">
                        <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold opacity-70">What's on your mind today?</h2>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 bg-base-100 border-t border-base-200">
                <form id="chat-form" class="relative">
                    <div class="join w-full shadow-sm">
                        <input 
                            type="text" 
                            id="chat-input" 
                            placeholder="Ask a question about your documents..." 
                            class="input input-bordered join-item flex-1 focus:outline-none focus:border-primary" 
                            autocomplete="off"
                        />
                        <button type="submit" id="send-btn" class="btn btn-primary join-item px-6">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                        </button>
                    </div>
                    <div id="chat-status" class="absolute -top-8 left-0 text-xs text-base-content/50 pl-2"></div>
                </form>
            </div>
        </main>

    </div>
    <script>

// Tab Switching Logic
function switchTab(tabName) {
    // Reset tabs
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('tab-active'));
    document.getElementById('tab-' + tabName).classList.add('tab-active');

    // Hide all content
    document.getElementById('content-upload').classList.add('hidden');
    document.getElementById('content-history').classList.add('hidden');

    // Show selected content
    document.getElementById('content-' + tabName).classList.remove('hidden');
}

// CSRF Token setup
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Theme Toggle System
const themeToggleBtn = document.getElementById('theme-toggle');
const sunIcon = document.getElementById('theme-icon-sun');
const moonIcon = document.getElementById('theme-icon-moon');
const htmlElement = document.documentElement;

// Get saved theme or default to 'dim'
let currentTheme = localStorage.getItem('theme') || 'dim';

// Function to apply theme
function applyTheme(theme) {
    htmlElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    currentTheme = theme;

    // Update icon visibility
    if (theme === 'light') {
        sunIcon.classList.add('hidden');
        moonIcon.classList.remove('hidden');
    } else {
        sunIcon.classList.remove('hidden');
        moonIcon.classList.add('hidden');
    }
}

// Apply saved theme on load
applyTheme(currentTheme);

// Toggle theme on button click
themeToggleBtn.addEventListener('click', () => {
    const newTheme = currentTheme === 'light' ? 'dim' : 'light';
    applyTheme(newTheme);
});

// Document Upload
const documentInput = document.getElementById('document-input');
const uploadBtn = document.getElementById('upload-btn');
const uploadStatus = document.getElementById('upload-status');
const documentsList = document.getElementById('documents-list');

uploadBtn.addEventListener('click', async () => {
    const file = documentInput.files[0];
    if (!file) {
        showUploadStatus('Please select a file', 'error');
        return;
    }

    // Check if it's a PDF
    if (!file.name.toLowerCase().endsWith('.pdf')) {
        showUploadStatus('Please select a PDF file', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('document', file);

    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Uploading...';

    // Create progress bar
    uploadStatus.innerHTML = `
                <div class="w-full bg-base-300 rounded-full h-2 mt-2">
                    <div id="upload-progress-bar" class="bg-primary h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <div id="upload-progress-text" class="text-xs mt-1 text-center">Starting upload...</div>
            `;

    try {
        const response = await fetch('/upload-document', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            buffer += chunk;
            const lines = buffer.split('\n');

            buffer = lines.pop() || '';

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));

                        const progressBar = document.getElementById('upload-progress-bar');
                        const progressText = document.getElementById('upload-progress-text');

                        if (data.progress) {
                            progressBar.style.width = data.progress + '%';
                        }

                        if (data.message) {
                            progressText.textContent = data.message;
                        }

                        if (data.success) {
                            showUploadStatus(`✓ ${data.message} (${data.chunks} chunks)`, 'success');
                            documentInput.value = '';
                            loadDocuments();
                        }

                        if (data.error) {
                            showUploadStatus('✗ ' + data.message, 'error');
                        }
                    } catch (e) {
                        // Ignore JSON parse errors
                    }
                }
            }
        }
    } catch (error) {
        showUploadStatus('Upload failed: ' + error.message, 'error');
    } finally {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> Upload PDF';
    }
});

function showUploadStatus(message, type) {
    const colorClass = type === 'success' ? 'text-success' : 'text-error';
    uploadStatus.className = `text-xs mt-2 ${colorClass}`;
    uploadStatus.textContent = message;
    setTimeout(() => {
        uploadStatus.textContent = '';
    }, 5000);
}

async function loadDocuments() {
    try {
        const response = await fetch('/documents');
        const data = await response.json();

        if (data.success && data.documents.length > 0) {
            documentsList.innerHTML = data.documents.map(doc => `
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 hover:bg-base-200 rounded-lg transition-colors group cursor-pointer border border-transparent hover:border-base-300">
                            <div class="p-2 bg-base-100 rounded-md text-primary group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium truncate flex-1 opacity-80 group-hover:opacity-100">${doc.name}</span>
                        </div>
                    `).join('');
        } else {
            documentsList.innerHTML = '<div class="text-xs text-center opacity-50 py-4">No documents uploaded</div>';
        }
    } catch (error) {
        console.error('Failed to load documents:', error);
    }
}

// Chat functionality
const chatForm = document.getElementById('chat-form');
const chatInput = document.getElementById('chat-input');
const chatMessages = document.getElementById('chat-messages');
const sendBtn = document.getElementById('send-btn');
const chatStatus = document.getElementById('chat-status');
const emptyState = document.getElementById('empty-state');

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const message = chatInput.value.trim();
    if (!message) return;

    // Remove empty state if it exists
    if (emptyState && emptyState.parentNode) {
        emptyState.remove();
    }
    const dynamicEmptyState = document.getElementById('empty-state');
    if (dynamicEmptyState) dynamicEmptyState.remove();

    // Add user message to chat
    addMessage(message, 'user');
    chatInput.value = '';

    // Disable input while processing
    chatInput.disabled = true;
    sendBtn.disabled = true;

    // Create AI message bubble with thinking indicator
    const aiMessageDiv = addMessage('<span class="loading loading-dots loading-sm"></span> Thinking...', 'ai');
    const aiMessageContent = aiMessageDiv.querySelector('.chat-bubble');

    try {
        const body = { message };
        if (currentThreadId) {
            body.thread_id = currentThreadId;
        }

        const response = await fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(body)
        });

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let fullResponse = '';
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;

            const chunk = decoder.decode(value, { stream: true });
            buffer += chunk;
            const lines = buffer.split('\n');

            // Keep the last incomplete line in buffer
            buffer = lines.pop() || '';

            for (const line of lines) {
                if (line.startsWith('data: ')) {
                    try {
                        const data = JSON.parse(line.slice(6));

                        if (data.thread_id) {
                            currentThreadId = data.thread_id;
                        }

                        if (data.title) {
                            loadHistory();
                        }

                        if (data.chunk) {
                            // Clear thinking message on first chunk
                            if (fullResponse === '') {
                                aiMessageContent.textContent = '';
                            }
                            // Stream character by character for smooth effect
                            for (let char of data.chunk) {
                                fullResponse += char;
                                aiMessageContent.textContent = fullResponse;
                                scrollToBottom();
                                // Small delay for streaming effect
                                await new Promise(resolve => setTimeout(resolve, 10));
                            }
                        }

                        if (data.done) {
                            chatStatus.innerHTML = '';
                            loadHistory(); // Refresh history to update timestamp
                        }

                        if (data.error) {
                            aiMessageContent.textContent = 'Error: ' + data.message;
                            aiMessageDiv.classList.add('chat-error');
                        }
                    } catch (e) {

                    }
                }
            }
        }
    } catch (error) {
        aiMessageContent.textContent = 'Failed to get response: ' + error.message;
        aiMessageDiv.classList.add('chat-error');
    } finally {
        chatInput.disabled = false;
        sendBtn.disabled = false;
        chatStatus.innerHTML = '';
        chatInput.focus();
        scrollToBottom();
    }
});

function addMessage(text, sender) {
    const messageDiv = document.createElement('div');
    messageDiv.className = sender === 'user' ? 'chat chat-end' : 'chat chat-start';

    const bubbleClass = sender === 'user' ? 'chat-bubble-accent' : 'chat-bubble-primary';

    // Bot Icon (Robot)
    const botIcon = `
                <div class="chat-image avatar">
                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                        <i class="bi bi-robot"></i>
                    </div>
                </div>`;

    // User Icon (Person)
    const userIcon = `
                <div class="chat-image avatar">
                    <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center text-accent">
                        <i class="bi bi-person"></i>
                    </div>
                </div>`;

    const avatar = sender === 'user' ? userIcon : botIcon;

    messageDiv.innerHTML = `
                ${avatar}
                <div class="chat-bubble ${bubbleClass} shadow-md">${text}</div>
            `;

    chatMessages.appendChild(messageDiv);
    scrollToBottom();

    return messageDiv;
}

function scrollToBottom() {
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

let currentThreadId = null;

async function loadHistory() {
    try {
        const response = await fetch('/chat/history');
        const data = await response.json();

        const historyList = document.getElementById('history-list');
        const historyEmpty = document.getElementById('history-empty');

        if (data.success && data.history.length > 0) {
            historyEmpty.classList.add('hidden');
            historyList.innerHTML = data.history.map(chat => `
                        <div onclick="loadChat('${chat.thread_id}')" class="p-3 bg-base-200/50 hover:bg-base-200 rounded-lg cursor-pointer transition-colors border border-transparent hover:border-base-300 ${currentThreadId === chat.thread_id ? 'border-primary bg-base-200' : ''}">
                            <div class="font-medium text-sm truncate">${chat.title || 'New Conversation'}</div>
                            <div class="text-xs opacity-50 mt-1">${new Date(chat.updated_at).toLocaleDateString()}</div>
                        </div>
                    `).join('');
        } else {
            historyList.innerHTML = '';
            historyEmpty.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Failed to load history:', error);
    }
}

async function loadChat(threadId) {
    if (currentThreadId === threadId) return;

    try {
        chatMessages.innerHTML = '<div class="flex justify-center items-center h-full"><span class="loading loading-spinner"></span></div>';

        const response = await fetch(`/chat/history/${threadId}`);
        const data = await response.json();

        if (data.success) {
            currentThreadId = threadId;
            chatMessages.innerHTML = '';

            if (data.messages && Array.isArray(data.messages)) {
                data.messages.forEach(msg => {
                    // Handle NeuronAI message format or standard format
                    // Assuming msg has 'content' and 'role' or similar
                    // If it's just text, it might be an issue, but let's assume object
                    const content = msg.content || msg.text || JSON.stringify(msg);
                    // Determine sender. If role is missing, try to infer or default to user?
                    // NeuronAI stores: {"role": "user", "content": "..."} usually.
                    let sender = 'user';
                    if (msg.role === 'assistant' || msg.role === 'system' || msg.role === 'ai') {
                        sender = 'ai';
                    } else if (msg.type && msg.type.includes('Assistant')) {
                        sender = 'ai';
                    }

                    addMessage(content, sender);
                });
            }

            scrollToBottom();
            loadHistory(); // Refresh selection highlight
        }
    } catch (error) {
        console.error('Failed to load chat:', error);
        chatMessages.innerHTML = '<div class="text-error text-center p-4">Failed to load conversation</div>';
    }
}

function startNewChat() {
    currentThreadId = null;
    chatMessages.innerHTML = '';

    const emptyStateDiv = document.createElement('div');
    emptyStateDiv.id = 'empty-state';
    emptyStateDiv.className = 'flex flex-col items-center justify-center h-full text-base-content/30 gap-6 transition-opacity duration-500';
    emptyStateDiv.innerHTML = `
                <div class="p-6 bg-base-200/50 rounded-full">
                    <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-semibold opacity-70">What's on your mind today?</h2>
            `;
    chatMessages.appendChild(emptyStateDiv);
    loadHistory();
}

loadDocuments();
loadHistory();
    </script>
</body>
</html>
