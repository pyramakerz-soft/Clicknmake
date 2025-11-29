<template>
    <div class="chat-container">
        <div class="messages">
            <div
                v-for="message in messages"
                :key="message.id"
                :class="{ 'my-message': message.sender_id === user.id }"
            >
                <p>{{ message.message }}</p>
            </div>
        </div>
        <div class="input-container">
            <input
                type="text"
                v-model="newMessage"
                @keyup.enter="sendMessage"
                placeholder="Type a message..."
            />
            <button @click="sendMessage">Send</button>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import Echo from "laravel-echo";

export default {
    props: ["user"],
    data() {
        return {
            messages: [],
            newMessage: "",
            receiver_id: 2, // Example receiver_id (Replace with dynamic value)
        };
    },
    mounted() {
        this.fetchMessages();

        Echo.private(`chat.${this.user.id}`).listen("MessageSent", (e) => {
            this.messages.push(e.message);
        });
    },
    methods: {
        fetchMessages() {
            axios
                .get("/messages", { params: { receiver_id: this.receiver_id } })
                .then((response) => {
                    this.messages = response.data;
                });
        },
        sendMessage() {
            if (this.newMessage.trim() === "") return;

            axios
                .post("/messages", {
                    receiver_id: this.receiver_id,
                    message: this.newMessage,
                })
                .then((response) => {
                    this.messages.push(response.data);
                    this.newMessage = "";
                });
        },
    },
};
</script>

<style>
/* Add your styles here */
</style>
