document.addEventListener('DOMContentLoaded', () => {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message');
    const messagesContainer = document.getElementById('messages');
    let currentChannelId = null;
    let currentServerId = null;
    let ws = null;

    // Load initial data
    loadServers();

    // Initialize WebSocket connection
    function initWebSocket() {
        try {
            console.log('Attempting to connect to WebSocket server...');
            ws = new WebSocket('ws://localhost:8081');

            ws.onopen = () => {
                console.log('WebSocket connected successfully');
                // Join the current channel if any
                if (currentChannelId) {
                    console.log('Joining channel:', currentChannelId);
                    ws.send(JSON.stringify({
                        type: 'join',
                        channel_id: currentChannelId
                    }));
                }
            };

            ws.onmessage = (event) => {
                console.log('Raw message received:', event.data);
                try {
                    const data = JSON.parse(event.data);
                    console.log('Parsed message:', data);
                    
                    if (data.type === 'new-message') {
                        const message = data.message;
                        const messageElement = document.createElement('div');
                        messageElement.className = 'message';
                        messageElement.dataset.messageId = message.id;
                        messageElement.innerHTML = `
                            <div class="message-header">
                                <span class="message-author">${message.username}</span>
                                <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                            </div>
                            <div class="message-content">${message.content}</div>
                        `;
                        
                        messagesContainer.appendChild(messageElement);
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                } catch (error) {
                    console.error('Error processing message:', error);
                }
            };

            ws.onclose = (event) => {
                console.log('WebSocket disconnected:', event.code, event.reason);
                // Attempt to reconnect after a delay
                setTimeout(initWebSocket, 5000);
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        } catch (error) {
            console.error('Error initializing WebSocket:', error);
        }
    }

    // Handle server creation
    document.addEventListener('click', async (e) => {
        if (e.target.closest('.server-icon') && !e.target.closest('.server-icon').dataset.serverId) {
            e.preventDefault();
            const serverName = prompt('Enter server name:');
            if (!serverName) return;

            try {
                const response = await fetch('api/servers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ name: serverName })
                });

                if (response.ok) {
                    const server = await response.json();
                    await loadServers();
                    // Select the newly created server
                    const serverElement = document.querySelector(`[data-server-id="${server.id}"]`);
                    if (serverElement) {
                        serverElement.click();
                    }
                }
            } catch (error) {
                console.error('Error creating server:', error);
            }
        }
    });

    // Handle server selection using event delegation
    document.addEventListener('click', async (e) => {
        const serverIcon = e.target.closest('.server-icon');
        if (serverIcon && serverIcon.dataset.serverId) {
            e.preventDefault();
            const serverId = serverIcon.dataset.serverId;
            currentServerId = serverId;
            
            // Update active state
            document.querySelectorAll('.server-item').forEach(si => si.classList.remove('active'));
            serverIcon.closest('.server-item').classList.add('active');
            
            // Load channels for selected server
            await loadChannels(serverId);
            // Load members for selected server
            await loadMembers(serverId);
        }
    });

    // Handle channel selection using event delegation
    document.addEventListener('click', async (e) => {
        const channelLink = e.target.closest('.channel-link');
        if (channelLink && channelLink.dataset.channelId) {
            e.preventDefault();
            const channelId = channelLink.dataset.channelId;
            
            // Leave previous channel if any
            if (currentChannelId && ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'leave',
                    channel_id: currentChannelId
                }));
            }
            
            currentChannelId = channelId;
            
            // Update active state
            document.querySelectorAll('.channel-link').forEach(cl => cl.classList.remove('active'));
            channelLink.classList.add('active');
            
            // Load messages for selected channel
            await loadMessages(channelId);
            
            // Join new channel
            if (ws && ws.readyState === WebSocket.OPEN) {
                ws.send(JSON.stringify({
                    type: 'join',
                    channel_id: channelId
                }));
            }
        }
    });

    // Handle message submission
    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentChannelId || !messageInput.value.trim()) {
            console.log('Cannot send message: No channel selected or empty message');
            return;
        }

        const message = messageInput.value.trim();
        const userId = document.body.dataset.userId;
        
        if (!userId) {
            console.error('User ID not found in page data');
            return;
        }

        if (!ws || ws.readyState !== WebSocket.OPEN) {
            console.error('WebSocket is not connected. Current state:', ws ? ws.readyState : 'null');
            // Try to reconnect
            initWebSocket();
            return;
        }

        try {
            const messageData = {
                type: 'message',
                channel_id: currentChannelId,
                content: message,
                user_id: userId
            };
            
            console.log('Sending message:', messageData);
            ws.send(JSON.stringify(messageData));
            
            // Clear input immediately for better UX
            messageInput.value = '';
            
            // Reload messages to ensure we have the latest
            await loadMessages(currentChannelId);
        } catch (error) {
            console.error('Error sending message:', error);
        }
    });

    // Load user's servers
    async function loadServers() {
        try {
            const response = await fetch('api/servers.php');
            const servers = await response.json();
            
            const serverList = document.querySelector('.server-list');
            const serverItems = servers.map(server => `
                <div class="server-item">
                    <a href="#" class="server-icon" data-server-id="${server.id}">
                        ${server.name.charAt(0).toUpperCase()}
                    </a>
                </div>
            `).join('');
            
            serverList.innerHTML = `
                <div class="server-item">
                    <a href="#" class="server-icon">+</a>
                </div>
                ${serverItems}
            `;

            // If no server is selected, select the first one
            if (!currentServerId && servers.length > 0) {
                const firstServer = document.querySelector('.server-icon[data-server-id]');
                if (firstServer) {
                    firstServer.click();
                }
            }
        } catch (error) {
            console.error('Error loading servers:', error);
        }
    }

    // Load channels for a server
    async function loadChannels(serverId) {
        try {
            const response = await fetch(`api/channels.php?server_id=${serverId}`);
            const channels = await response.json();
            
            const channelList = document.querySelector('.channel-list');
            const channelItems = channels.map(channel => `
                <div class="channel-item">
                    <a href="#" class="channel-link" data-channel-id="${channel.id}">
                        # ${channel.name}
                    </a>
                </div>
            `).join('');
            
            channelList.innerHTML = `
                <div class="server-header">
                    <h2>${channels[0]?.server_name || 'No Server Selected'}</h2>
                </div>
                ${channelItems}
            `;

            // If no channel is selected, select the first one
            if (!currentChannelId && channels.length > 0) {
                const firstChannel = document.querySelector('.channel-link');
                if (firstChannel) {
                    firstChannel.click();
                }
            }
        } catch (error) {
            console.error('Error loading channels:', error);
        }
    }

    // Load messages for a channel
    async function loadMessages(channelId) {
        try {
            const response = await fetch(`api/messages.php?channel_id=${channelId}`);
            const messages = await response.json();
            
            messagesContainer.innerHTML = messages.map(message => `
                <div class="message" data-message-id="${message.id}">
                    <div class="message-header">
                        <span class="message-author">${message.username}</span>
                        <span class="message-time">${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                    <div class="message-content">${message.content}</div>
                </div>
            `).join('');
            
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } catch (error) {
            console.error('Error loading messages:', error);
        }
    }

    // Load members for a server
    async function loadMembers(serverId) {
        try {
            const response = await fetch(`api/members.php?server_id=${serverId}`);
            const members = await response.json();
            
            const onlineMembers = members.filter(member => member.is_online);
            const offlineMembers = members.filter(member => !member.is_online);
            
            // Update counts
            document.getElementById('online-count').textContent = onlineMembers.length;
            document.getElementById('offline-count').textContent = offlineMembers.length;
            
            // Render online members
            const onlineContainer = document.getElementById('online-members');
            onlineContainer.innerHTML = onlineMembers.map(member => `
                <div class="member-item">
                    <span class="member-status"></span>
                    <div class="member-avatar">${member.username.charAt(0).toUpperCase()}</div>
                    <div class="member-info">
                        <div class="member-name">${member.username}</div>
                        <div class="member-role">${member.role}</div>
                    </div>
                </div>
            `).join('');
            
            // Render offline members
            const offlineContainer = document.getElementById('offline-members');
            offlineContainer.innerHTML = offlineMembers.map(member => `
                <div class="member-item">
                    <span class="member-status offline"></span>
                    <div class="member-avatar">${member.username.charAt(0).toUpperCase()}</div>
                    <div class="member-info">
                        <div class="member-name">${member.username}</div>
                        <div class="member-role">${member.role}</div>
                    </div>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading members:', error);
        }
    }

    // Update user's last activity
    function updateLastActivity() {
        fetch('api/update_activity.php', {
            method: 'POST'
        });
    }

    // Update activity every 5 minutes
    setInterval(updateLastActivity, 300000);

    // Initialize WebSocket connection
    initWebSocket();
}); 