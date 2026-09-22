<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<style>

/* =====================================================
   CHATBOT BUTTON
===================================================== */

#chat-toggle {
    position: fixed;
    right: 25px;
    bottom: 25px;
    width: 62px;
    height: 62px;
    border-radius: 50%;
    border: none;
    background-color: #1c3a66;
    color: white;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    z-index: 9999;
    transition: transform 0.2s ease;
}

#chat-toggle:hover {
    transform: scale(1.05);
    background-color: #274c87;
}


/* =====================================================
   CHAT WINDOW
===================================================== */

#chat-container {
    position: fixed;
    right: 25px;
    bottom: 100px;
    width: 370px;
    height: 530px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.25);
    display: none;
    flex-direction: column;
    overflow: hidden;
    z-index: 9998;
}


/* =====================================================
   CHAT HEADER
===================================================== */

#chat-header {
    background-color: #1c3a66;
    color: white;
    padding: 15px;
    font-size: 16px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#chat-close {
    background: none;
    border: none;
    color: white;
    font-size: 22px;
    cursor: pointer;
}


/* =====================================================
   CHAT BODY
===================================================== */

#chat-body {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    background-color: #f4f6f8;
}


/* =====================================================
   MESSAGES
===================================================== */

.chat-message {
    max-width: 85%;
    padding: 10px 12px;
    margin-bottom: 10px;
    border-radius: 10px;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
}

.chat-message.bot {
    background-color: white;
    border: 1px solid #ddd;
    color: #333;
    margin-right: auto;
}

.chat-message.user {
    background-color: #1c3a66;
    color: white;
    margin-left: auto;
}


/* =====================================================
   CHAT FOOTER
===================================================== */

#chat-footer {
    display: flex;
    padding: 10px;
    border-top: 1px solid #ddd;
    background: white;
    gap: 8px;
}

#chat-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 7px;
    outline: none;
    font-size: 14px;
}

#chat-input:focus {
    border-color: #1c3a66;
}

#chat-send-btn {
    padding: 10px 15px;
    background-color: #1c3a66;
    color: white;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    font-weight: bold;
}

#chat-send-btn:hover {
    background-color: #274c87;
}

#chat-send-btn:disabled {
    background-color: #999;
    cursor: not-allowed;
}


/* =====================================================
   RECOMMENDATION CARD
===================================================== */

.chat-recommendation-card {
    background: white;
    border: 1px solid #d5d5d5;
    border-left: 4px solid #1c3a66;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
}

.chat-recommendation-card h3 {
    margin: 0 0 8px 0;
    color: #1c3a66;
    font-size: 16px;
}

.chat-recommendation-card p {
    margin: 5px 0;
    font-size: 13px;
}

.chat-match-score {
    font-weight: bold;
    color: #1c3a66;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    #chat-container {
        right: 10px;
        bottom: 85px;
        width: calc(100% - 20px);
        height: 500px;
    }

    #chat-toggle {
        right: 15px;
        bottom: 15px;
    }

}

</style>


<!-- =====================================================
     CHATBOT BUTTON
===================================================== -->

<button
    id="chat-toggle"
    type="button"
    title="College Recommendation Assistant"
>
    💬
</button>


<!-- =====================================================
     CHATBOT WINDOW
===================================================== -->

<div id="chat-container">

    <div id="chat-header">

        <span>
            College Recommendation Assistant
        </span>

        <button
            id="chat-close"
            type="button"
        >
            ×
        </button>

    </div>


    <div id="chat-body">

        <div class="chat-message bot">

            Hello! 👋

            <br><br>

            I can help you find suitable colleges.

            <br><br>

            Type <strong>hi</strong> to start.

        </div>

    </div>


    <div id="chat-footer">

        <input
            type="text"
            id="chat-input"
            placeholder="Type your message..."
            autocomplete="off"
        >

        <button
            id="chat-send-btn"
            type="button"
        >
            Send
        </button>

    </div>

</div>


<script>

(function () {

    /* =====================================================
       ELEMENTS
    ===================================================== */

    const chatToggle =
        document.getElementById("chat-toggle");

    const chatContainer =
        document.getElementById("chat-container");

    const chatClose =
        document.getElementById("chat-close");

    const chatInput =
        document.getElementById("chat-input");

    const chatSendBtn =
        document.getElementById("chat-send-btn");

    const chatBody =
        document.getElementById("chat-body");


    /* =====================================================
       OPEN / CLOSE CHAT
    ===================================================== */

    chatToggle.addEventListener(
        "click",
        function () {

            if (
                chatContainer.style.display === "none" ||
                chatContainer.style.display === ""
            ) {

                chatContainer.style.display = "flex";

                chatInput.focus();

            } else {

                chatContainer.style.display = "none";

            }

        }
    );


    chatClose.addEventListener(
        "click",
        function () {

            chatContainer.style.display = "none";

        }
    );


    /* =====================================================
       ESCAPE HTML
    ===================================================== */

    function escapeHTML(text) {

        const div =
            document.createElement("div");

        div.textContent =
            text ?? "";

        return div.innerHTML;

    }


    /* =====================================================
       ADD MESSAGE
    ===================================================== */

    function addMessage(
        message,
        sender
    ) {

        const messageDiv =
            document.createElement("div");

        messageDiv.className =
            "chat-message " + sender;

        messageDiv.innerHTML =
            message;

        chatBody.appendChild(
            messageDiv
        );

        chatBody.scrollTop =
            chatBody.scrollHeight;

    }


    /* =====================================================
       SHOW RECOMMENDATIONS
    ===================================================== */

    function showRecommendations(
        recommendations
    ) {

        if (
            !recommendations ||
            recommendations.length === 0
        ) {

            return;

        }


        recommendations.forEach(
            function (college) {

                const card =
                    document.createElement("div");

                card.className =
                    "chat-recommendation-card";


                card.innerHTML = `

                    <h3>
                        ${escapeHTML(
                            college.name
                        )}
                    </h3>

                    <p>
                        <strong>Course:</strong>
                        ${escapeHTML(
                            college.course
                        )}
                    </p>

                    <p>
                        <strong>University:</strong>
                        ${escapeHTML(
                            college.university
                        )}
                    </p>

                    <p>
                        <strong>Location:</strong>
                        ${escapeHTML(
                            college.location
                        )}
                    </p>

                    <p>
                        <strong>Duration:</strong>
                        ${escapeHTML(
                            college.duration || "N/A"
                        )}
                    </p>

                    <p>
                        <strong>Scholarship:</strong>
                        ${escapeHTML(
                            college.scholarships || "N/A"
                        )}
                    </p>

                    <p class="chat-match-score">
                        Match Score:
                        ${escapeHTML(
                            String(college.score)
                        )}%
                    </p>

                `;


                chatBody.appendChild(
                    card
                );

            }
        );


        chatBody.scrollTop =
            chatBody.scrollHeight;

    }


    /* =====================================================
       SEND MESSAGE
    ===================================================== */

    function sendMessage() {

        const message =
            chatInput.value.trim();


        if (
            message === ""
        ) {

            return;

        }


        /* USER MESSAGE */

        addMessage(
            escapeHTML(message),
            "user"
        );


        chatInput.value =
            "";


        chatSendBtn.disabled =
            true;


        /* =================================================
           SEND TO chatbot.php
        ================================================= */

        fetch(
            "chatbot.php",
            {
                method: "POST",

                headers: {
                    "Content-Type":
                        "application/x-www-form-urlencoded"
                },

                body:
                    "message=" +
                    encodeURIComponent(message)
            }
        )

        .then(
            function (response) {

                if (
                    !response.ok
                ) {

                    throw new Error(
                        "Server error"
                    );

                }

                return response.json();

            }
        )

        .then(
            function (data) {

                if (
                    data.reply
                ) {

                    addMessage(
                        data.reply,
                        "bot"
                    );

                }


                showRecommendations(
                    data.recommendations
                );

            }
        )

        .catch(
            function (error) {

                console.error(
                    "Chatbot error:",
                    error
                );


                addMessage(
                    "Sorry, I could not process your message. Please try again.",
                    "bot"
                );

            }
        )

        .finally(
            function () {

                chatSendBtn.disabled =
                    false;

                chatInput.focus();

            }
        );

    }


    /* =====================================================
       SEND BUTTON
    ===================================================== */

    chatSendBtn.addEventListener(
        "click",
        sendMessage
    );


    /* =====================================================
       ENTER KEY
    ===================================================== */

    chatInput.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Enter"
            ) {

                event.preventDefault();

                sendMessage();

            }

        }
    );

})();

</script>