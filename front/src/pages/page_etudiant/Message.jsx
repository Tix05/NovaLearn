import React, { useState, useEffect, useRef } from 'react';
import { Smile, Circle } from 'lucide-react';
import EmojiPicker from 'emoji-picker-react';
import { format } from 'date-fns';
import Avatar from 'react-avatar';
import Layout from '../../components/Layout';
import { LuSendHorizontal } from "react-icons/lu";
import { motion } from 'framer-motion';

const mockUsers = [
    { id: "user1", name: "Alice", status: "online", avatar: "https://i.pravatar.cc/150?img=1" },
    { id: "user2", name: "Bob", status: "online", avatar: "https://i.pravatar.cc/150?img=2" },
    { id: "user3", name: "Charlie", status: "offline", avatar: "https://i.pravatar.cc/150?img=3" },
    { id: "user4", name: "David", status: "online", avatar: "https://i.pravatar.cc/150?img=4" },
    { id: "user5", name: "Eve", status: "offline", avatar: "https://i.pravatar.cc/150?img=5" },
];

const mockMessages = [
    { id: "1", text: "Salut, tu as vu le dernier épisode de la série ?", timestamp: new Date(), userId: "user2", toId: "user1", userName: "Bob" },
    { id: "2", text: "Oui, incroyable le twist final !", timestamp: new Date(), userId: "user1", toId: "user2", userName: "Alice" },
    { id: "3", text: "As-tu des recommandations pour un bon livre ?", timestamp: new Date(), userId: "user3", toId: "user1", userName: "Charlie" },
    { id: "4", text: "Je te recommande 'Sapiens' de Yuval Noah Harari.", timestamp: new Date(), userId: "user1", toId: "user3", userName: "Alice" },
    { id: "5", text: "Tu viens à la fête ce week-end ?", timestamp: new Date(), userId: "user4", toId: "user1", userName: "David" },
    { id: "6", text: "Oui, je vais passer avec des amis.", timestamp: new Date(), userId: "user1", toId: "user4", userName: "Alice" }
];

const initialGroupMessages = [
    { id: "g1", text: "Bienvenue dans le groupe !", timestamp: new Date(), userId: "user1", userName: "Alice" },
    { id: "g2", text: "Merci !", timestamp: new Date(), userId: "user2", userName: "Bob" },
    { id: "g3", text: "Qui veut organiser une sortie ce week-end ?", timestamp: new Date(), userId: "user3", userName: "Charlie" },
    { id: "g4", text: "Je suis partant !", timestamp: new Date(), userId: "user4", userName: "David" },
    { id: "g5", text: "Moi aussi !", timestamp: new Date(), userId: "user5", userName: "Eve" }
];

function Message() {
    const [messages, setMessages] = useState(mockMessages);
    const [groupMessages, setGroupMessages] = useState(initialGroupMessages);
    const [newMessage, setNewMessage] = useState('');
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [messageSearchTerm, setMessageSearchTerm] = useState('');
    const [selectedUser, setSelectedUser] = useState(null);
    const [isGroupSelected, setIsGroupSelected] = useState(false);
    const messagesEndRef = useRef(null);
    const currentUserId = "user1";

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages, groupMessages]);

    const handleSendMessage = (e) => {
        e.preventDefault();
        if (!newMessage.trim()) return;

        if (isGroupSelected) {
            const newGroupMsg = {
                id: String(Date.now()),
                text: newMessage,
                timestamp: new Date(),
                userId: currentUserId,
                userName: "Alice"
            };
            setGroupMessages([...groupMessages, newGroupMsg]);
        } else if (selectedUser) {
            const newMsg = {
                id: String(Date.now()),
                text: newMessage,
                timestamp: new Date(),
                userId: currentUserId,
                toId: selectedUser.id,
                userName: "Alice"
            };
            setMessages([...messages, newMsg]);
        }

        setNewMessage('');
    };

    const onEmojiClick = (emojiObject) => {
        setNewMessage(prev => prev + emojiObject.emoji);
        setShowEmojiPicker(false);
    };

    const filteredUsers = mockUsers.filter(user =>
        user.name.toLowerCase().includes(searchTerm.toLowerCase()) &&
        user.id !== currentUserId
    );

    const filteredMessages = isGroupSelected
        ? groupMessages.filter(msg => msg.text.toLowerCase().includes(messageSearchTerm.toLowerCase()))
        : messages.filter(
            msg =>
                selectedUser &&
                ((msg.userId === currentUserId && msg.toId === selectedUser.id) ||
                    (msg.userId === selectedUser.id && msg.toId === currentUserId)) &&
                msg.text.toLowerCase().includes(messageSearchTerm.toLowerCase())
        );

    return (
        <Layout>
            <div className="flex bg-white">
                <div className="w-72 bg-white border-r border-gray-500 p-4 custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                    <h2 className="text-gray-800 text-lg font-semibold mb-4">Messages</h2>
                    <input
                        type="text"
                        placeholder="Rechercher..."
                        className="w-full mb-3 p-2 bg-white text-gray-800 rounded border-gray-500 border focus:outline-none focus:border-blue-300 focus:ring-1 focus:ring-blue-300"
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                    />
                    <div className="space-y-2">
                        <button
                            onClick={() => { setSelectedUser(null); setIsGroupSelected(true); }}
                            className={`w-full flex items-center space-x-3 p-3 rounded-lg transition-colors ${isGroupSelected ? 'bg-blue-600 text-white' : 'text-gray-800 hover:bg-gray-300'}`}
                        >
                            <Avatar name="Groupe" size="40" round={true} />
                            <span>Communication</span>
                        </button>
                        {filteredUsers.map(user => (
                            <button
                                key={user.id}
                                onClick={() => { setSelectedUser(user); setIsGroupSelected(false); }}
                                className={`w-full flex items-center space-x-3 p-3 rounded-lg transition-colors ${selectedUser?.id === user.id ? 'bg-blue-600 text-white' : 'text-gray-800 hover:bg-gray-300'}`}
                            >
                                <Avatar src={user.avatar} size="40" round={true} />
                                <span>{user.name}</span>
                                <Circle className={`h-3 w-3 ${user.status === 'online' ? 'text-green-500' : 'text-gray-500'}`} fill="currentColor" />
                            </button>
                        ))}
                    </div>
                </div>

                <div className="flex-1 flex flex-col" style={{ height: 'calc(100vh - 3.5rem)' }}>
                    <div className="flex items-center justify-between bg-[#4CAF50] p-4 font-semibold space-x-2">
                        <span className='text-white text-xl'>{isGroupSelected ? 'Groupe' : selectedUser ? `Message avec ${selectedUser.name}` : 'Sélectionnez un utilisateur'}</span>
                        {(selectedUser || isGroupSelected) && (
                            <input
                                type="text"
                                placeholder="Rechercher des messages..."
                                className="px-5 py-2 rounded-full focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-400 text-gray-700"
                                value={messageSearchTerm}
                                onChange={(e) => setMessageSearchTerm(e.target.value)}
                            />
                        )}
                    </div>

                    <div className="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar" style={{ height: 'calc(100vh - 11rem)' }}>
                        {filteredMessages.length === 0 ? (
                            <div className="text-center text-gray-500 italic text-xl mt-40">
                                Aucun discussion
                            </div>
                        ) : (
                            filteredMessages.map((message) => (
                                <motion.div
                                    key={message.id}
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.3 }}
                                    className={`flex ${message.userId === currentUserId ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div className={`max-w-[70%] rounded-lg px-4 py-2 ${message.userId === currentUserId ? 'bg-blue-600 text-white' : 'bg-gray-700 text-white'}`}>
                                        <div className="font-semibold text-sm mb-1">{message.userId === currentUserId ? "Moi" : message.userName}</div>
                                        <div>{message.text}</div>
                                        <div className="text-xs opacity-70 mt-1">{format(message.timestamp, 'HH:mm')}</div>
                                    </div>
                                </motion.div>
                            ))
                        )}
                        <div ref={messagesEndRef} />
                    </div>
                    <form onSubmit={handleSendMessage} className="p-4 bg-[#BF3037]">
                        <div className="flex items-center space-x-2">
                            <button type="button" onClick={() => setShowEmojiPicker(!showEmojiPicker)} className="text-gray-400 hover:text-gray-300">
                                <Smile className="h-8 w-8" />
                            </button>
                            {showEmojiPicker && <div className="absolute bottom-20 left-5"><EmojiPicker onEmojiClick={onEmojiClick} /></div>}
                            <input
                                type="text"
                                value={newMessage}
                                onChange={(e) => setNewMessage(e.target.value)}
                                placeholder="Tapez un message..."
                                className="flex-1 bg-white text-gray-800 rounded-full px-4 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-semibold"
                                disabled={!selectedUser && !isGroupSelected}
                            />
                            <button type="submit" className={`px-2 py-1 items-center justify-center flex text-white rounded-lg ${newMessage.trim() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 cursor-not-allowed'}`} disabled={!newMessage.trim() || (!selectedUser && !isGroupSelected)}>
                                <LuSendHorizontal className="h-7 w-7" />
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Layout>
    );
}

export default Message;