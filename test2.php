<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Messaging System</title>
    <script src="https://cdn.ably.io/lib/ably.min-1.js"></script>
    <link href="https://fonts.cdnfonts.com/css/comic-sans" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/comic-sans" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/favicon/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/favicon/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">
    <meta name="description" content="A real-time global messaging system with emoji support and customizable nicknames">
    <meta name="keywords" content="chat, messaging, real-time, global chat, emoji">
    <meta name="author" content="Bruce Rodriguez">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="Simple Messaging System">
    <meta property="og:description" content="Join our global chat room with real-time messaging">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://messageing-app-450617.firebaseapp.com/chat.html">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Simple Messaging System">
    <meta name="twitter:description" content="Join our global chat room with real-time messaging">
    
    <style>
        * {
            font-family: 'Comic Sans MS', 'Comic Sans', cursive;
        }
    </style>
    <style>
        body {
            display: flex;
            position: fixed;
            width: 100%;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: lightgrey;
            
        }



        .message-container {
            width: 80%;
            max-width: 500px;
            margin: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            height: 300px;
            overflow-y: auto;
            background-color: whitesmoke;
            border-radius: 15px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .message {
            margin: 5px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .input-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 80%;
            max-width: 500px;
        }
        #messageInput {
            width: 100%;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        #userCount {
            position: fixed;
            top: 10px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .emoticons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center;
        }


        
        .emoticons button {
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            padding: 5px 10px;
            border: none;
            cursor: pointer;
        }

        .emoticons button:hover {
            background-color: #dfdfdf;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transform: translateY(2px);
            transition: all 1s ease;
        }

        .send {
            background-color: #4CAF50;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .send:hover {
            background-color: #45a049;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transform: translateY(2px);
            transition: all 1s ease;
        }

        .changeN {
            margin: 20px;
            background-color: #4ca7af;
            border-radius: 5px;
            border-color: #ccc;
            color: white;
            left: 50%;
        

        }

        .changeN:hover {
            background-color: #d00a0a;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 1s ease;
        }

        .idk {
            background-color: #4c99af;
            color: white;
            padding: 4px;
            border-radius: 4px;
            cursor: pointer;
        }

        .idk:hover {
            background-color: #368bed;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 1s ease;
        }

        .idk2 {
            background-color: #bc0000;
            color: white;
            padding: 4px;
            border-radius: 4px;
            cursor: pointer;
        }

        .idk2:hover {
            background-color: #d00a0a;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 1s ease;
        }

        #newColor {
            margin-left: 10px;
            border-radius: 5px;
            width: 20%;
            height: 20px;
            
        }

        footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #f5f5f5;
            padding: 10px 0;
            text-align: center;
            border-top: 1px solid #ccc;
        }

        footer p {
            font-size: 12px;
            color: #333;
            margin: 0;
        }

        footer a {
            color: #4CAF50;
            text-decoration: none;
            margin: 0 5px;
        }

        footer a:hover {
            text-decoration: underline;
        }





        @media screen and (max-width: 600px) {
            footer {
                padding: 5px 0;
            }

            footer p {
                font-size: 10px;
                display: hidden;
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }

            footer a {
                margin: 2px 0;
            }


            .changeN{
                margin: 0px;
            }


            body{
                height: 90vh;
            }
        }

        @media screen and (min-width: 601px) and (max-width: 1024px) {
            footer {
                padding: 0;
                font-size: 10px;
            }

            footer p {
                font-size: 7px;
            }

            .changeN {
                margin-bottom: 60px;
            }
        }


        @media screen and (max-width: 600px) {
            .emoticons {
            display: none;
            }

        }
    </style>
</head>
<body>
    
    <p id="userCount">Online: 0</p>
    <h2>Global Messaging System</h2>
    <div class="message-container" id="messageContainer"></div>

    

    <!-- rest of your existing body content -->
    <div style="margin: 15px;" class="input-container">
        <input type="text" id="messageInput" placeholder="Type your message here..." style="width: 300px;">
        <button class="send" onclick="sendMessage()">Send</button>

    </div>

    <div class="emoticons">
        <button onclick="sendEmoticon('😊')">😊</button>
        <button onclick="sendEmoticon('☹️')">☹️</button>
        <button onclick="sendEmoticon('👍')">👍</button>
        <button onclick="sendEmoticon('❤️')">❤️</button>
        <button onclick="sendEmoticon('😢')">😢</button>
        <button onclick="sendEmoticon('😭')">😭</button>
        <button onclick="sendEmoticon('💀')">💀</button>
    </div>

 
        <button class="changeN" onclick="openNicknameForm()">Change Nickname</button>
   

    <div id="nicknameModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); 
        background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.5);">
        <h3>Change Your Settings</h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <input type="text" id="newNickname" placeholder="New Nickname (optional)" style="padding: 5px;">
            <div>
                <label for="newColor">Bubble Color:</label>
                <input type="color" id="newColor" value="#e9ecef" title="Must set nickname first then resave color choice">
                
            </div>
            <div>
                <button class="idk" onclick="updateSettings()">Save</button>
                <button class="idk2" onclick="document.getElementById('nicknameModal').style.display='none'">Cancel</button>
            </div>
        </div>
    </div>
    


   

<!-- Replace the existing script section for settings with this: -->
<script>
    function openNicknameForm() {
        document.getElementById('nicknameModal').style.display = 'block';
    }

    async function updateSettings() {
        const newNickname = document.getElementById('newNickname').value.trim();
        const newColor = document.getElementById('newColor').value;
        const channel = window.ably.channels.get("get-started");
        const presence = channel.presence;

        // Check if user wants to change nickname or already has custom nickname
        if (newNickname || window.clientId !== 'Anonymous') {
            // If new nickname provided, check if it's available
            if (newNickname) {
                // Check if nickname is same as current
                if (newNickname === window.clientId) {
                    // Allow color change with same nickname
                    window.userColor = newColor;
                    channel.publish("colorUpdate", {
                        clientId: window.clientId,
                        color: newColor
                    });
                    updateExistingMessages(window.clientId, newColor);
                    document.getElementById('nicknameModal').style.display = 'none';
                    return;
                }

                // Check if nickname is already in use
                const exists = await new Promise((resolve) => {
                    presence.get((err, members) => {
                        if (err) {
                            console.error('Error checking members:', err);
                            resolve(false);
                            return;
                        }
                        resolve(members.some(member => member.clientId === newNickname));
                    });
                });

                if (exists) {
                    alert('This nickname is already in use. Please choose another one.');
                    return;
                }

                // Update nickname and color
                window.clientId = newNickname;
                window.userColor = newColor;
                
                // Publish color change and reconnect
                channel.publish("colorUpdate", {
                    clientId: newNickname,
                    color: newColor
                });
                
                if (window.ably) {
                    window.ably.close();
                    connectToAbly(newNickname);
                }
            } else {
                // Just update color for existing nickname
                window.userColor = newColor;
                channel.publish("colorUpdate", {
                    clientId: window.clientId,
                    color: newColor
                });
                updateExistingMessages(window.clientId, newColor);
            }
            
            document.getElementById('nicknameModal').style.display = 'none';
        } else {
            alert('Please set a nickname before changing the color.');
        }
    }

    function updateExistingMessages(clientId, color) {
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            if (msg.getAttribute('data-sender') === clientId) {
                msg.style.backgroundColor = color;
            }
        });
    }

</script>

<!-- Replace the main Ably connection script with this: -->
<script>
    window.clientId = 'Anonymous';
    window.userColor = document.getElementById('newColor')?.value || '#e9ecef';
    window.messageContainerColor = '#f5f5f5';
    window.userColors = new Map(); // Store colors for different users

    // Initialize notifications setup
    
    document.querySelector('.message-container').style.backgroundColor = window.messageContainerColor;

    function connectToAbly(clientId) {
        window.ably = new Ably.Realtime({
            key: 'uE35AQ.T0PV0Q:SnHkqJ0CG5bONd5MUuJyf5oezCFq9rq3peER3TwWcSc',
            clientId: clientId || window.clientId
        });

        const channel = window.ably.channels.get("get-started");
        channel.subscribe("colorUpdate", (message) => {
            window.userColors.set(message.data.clientId, message.data.color);
            updateExistingMessages(message.data.clientId, message.data.color);
        });

        channel.subscribe("message", (message) => {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message';
            messageDiv.setAttribute('data-sender', message.clientId); // Add sender identifier
            
            const messageContent = document.createElement('span');
            const sender = message.clientId || 'Anonymous';
            messageContent.textContent = `${sender}: ${message.data}`;
            messageContent.style.color = '#000000';
            
            // Use stored color for user if available
            const userColor = window.userColors.get(message.clientId) || '#e9ecef';
            messageDiv.style.backgroundColor = userColor;

            const messageContainer = document.getElementById('messageContainer');
            if (message.extras && message.extras.containerColor) {
                messageContainer.style.backgroundColor = message.extras.containerColor;
            }
            
            messageDiv.appendChild(messageContent);
            messageContainer.appendChild(messageDiv);
            messageContainer.scrollTop = messageContainer.scrollHeight;
        });
        
        const presence = channel.presence;
        presence.enter("user", (err) => {
            if (err) console.error('Error entering presence:', err);
        });

        presence.subscribe((presenceMessage) => {
            presence.get((err, members) => {
                if (err) {
                    console.error('Error getting members:', err);
                    return;
                }
                document.getElementById('userCount').textContent = `Online: ${members.length}`;
            });
        });
    }

    connectToAbly(window.clientId);

    function sendMessage() {
        const input = document.getElementById('messageInput');
        
        if (input.value.trim() !== '') {
            const channel = window.ably.channels.get("get-started");
            channel.publish("message", input.value, { 
                extras: { 
                    containerColor: window.messageContainerColor 
                }
            });
            input.value = '';
        }
    }

    document.getElementById('messageInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function sendEmoticon(emoticon) {
        document.getElementById('messageInput').value += emoticon;
    }

    
</script>

<footer>
    <p>&copy; 2025 Global Messaging System || Designed and Devoloped by Bruce Rodriguez || <a href="https://www.instagram.com/brucerodbr/">Instagram</a> || <a href="tel:+14805018522">Contact</a> || <a href="mailto:brucerod2040269@gmail.com">Email</a></p>
</footer>

</body>
</html>