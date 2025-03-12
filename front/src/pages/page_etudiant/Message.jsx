// src/components/Chat.js
import React, { useState, useEffect } from 'react';
import { firestore } from '../../firebase';

const Chat = ({ groupId, currentUser }) => {
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');

    // Charger les messages du groupe
    useEffect(() => {
        const unsubscribe = firestore
            .collection('groups')
            .doc(groupId)
            .collection('messages')
            .orderBy('createdAt')
            .onSnapshot((snapshot) => {
                const messages = snapshot.docs.map((doc) => ({
                    id: doc.id,
                    ...doc.data(),
                }));
                setMessages(messages);
            });

        return () => unsubscribe();
    }, [groupId]);

    // Envoyer un message
    const handleSendMessage = async () => {
        if (newMessage.trim() === '') return;

        await firestore
            .collection('groups')
            .doc(groupId)
            .collection('messages')
            .add({
                text: newMessage,
                createdAt: firebase.firestore.FieldValue.serverTimestamp(),
                senderId: currentUser.id, // ID de l'utilisateur provenant de Symfony
                senderName: currentUser.name, // Nom de l'utilisateur provenant de Symfony
            });

        setNewMessage('');
    };

    return (
        <div className="flex flex-col h-screen p-4">
            <div className="flex-1 overflow-y-auto">
                {messages.map((message) => (
                    <div key={message.id} className="mb-2">
                        <strong>{message.senderName}: </strong>
                        {message.text}
                    </div>
                ))}
            </div>
            <div className="flex">
                <input
                    type="text"
                    value={newMessage}
                    onChange={(e) => setNewMessage(e.target.value)}
                    className="flex-1 p-2 border rounded"
                />
                <button onClick={handleSendMessage} className="ml-2 p-2 bg-blue-500 text-white rounded">
                    Send
                </button>
            </div>
        </div>
    );
};

export default Chat;