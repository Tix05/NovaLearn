import React, { useState, useEffect, useRef } from 'react';
import { Smile, Circle } from 'lucide-react';
import EmojiPicker from 'emoji-picker-react';
import { format } from 'date-fns';
import Avatar from 'react-avatar';
import Layout from '../../components/Layout';
import { LuSendHorizontal } from 'react-icons/lu';
import { motion } from 'framer-motion';
import { getConversations, getMessages, sendMessage, markMessageAsRead } from '../../Services/messagingService';
import { getCurrentUser } from '../../Services/authService';
import { ProgressSpinner } from 'primereact/progressspinner';

function Message() {
    const [conversations, setConversations] = useState([]);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [showEmojiPicker, setShowEmojiPicker] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [messageSearchTerm, setMessageSearchTerm] = useState('');
    const [selectedConversation, setSelectedConversation] = useState(null);
    const [error, setError] = useState(null);
    const [loading, setLoading] = useState(true);
    const messagesEndRef = useRef(null);
    const currentUser = getCurrentUser();

    useEffect(() => {
        return () => {
            setConversations([]);
            setMessages([]);
            setSelectedConversation(null);
            setError(null);
            setLoading(true);
        };
    }, []);

    useEffect(() => {
        const fetchConversations = async () => {
            if (!currentUser) {
                setError('Utilisateur non authentifié');
                setLoading(false);
                return;
            }
            try {
                const data = await getConversations();
                console.log('Conversations fetched:', JSON.stringify(data, null, 2));
                const sortedConversations = data.sort((a, b) => {
                    const dateA = a.lastMessage?.date ? new Date(a.lastMessage.date) : new Date(0);
                    const dateB = b.lastMessage?.date ? new Date(b.lastMessage.date) : new Date(0);
                    return dateB - dateA;
                });
                setConversations(sortedConversations);
                // Ne définir selectedConversation que si elle n'est pas déjà définie
                if (sortedConversations.length > 0 && !selectedConversation) {
                    setSelectedConversation(sortedConversations[0]);
                }
                setLoading(false);
            } catch (error) {
                setError('Échec du chargement des conversations');
                setLoading(false);
                console.error('Error fetching conversations:', error);
            }
        };

        fetchConversations();

        const interval = setInterval(fetchConversations, 60000);
        return () => clearInterval(interval);
    }, [currentUser?.id]);

    useEffect(() => {
        if (!selectedConversation || !selectedConversation.id || !currentUser) {
            setMessages([]);
            return;
        }

        const fetchMessages = async () => {
            try {
                const data = await getMessages(selectedConversation.id);
                setMessages(data);
                for (const message of data) {
                    if (!message.lu && message.expediteur.id !== currentUser.id) {
                        await markMessageAsRead(message.id);
                    }
                }
            } catch (error) {
                setError('Échec du chargement des messages');
                console.error('Error fetching messages:', error);
            }
        };

        fetchMessages();

        const interval = setInterval(fetchMessages, 10000);
        return () => clearInterval(interval);
    }, [selectedConversation?.id, currentUser?.id]);

    // Faire défiler vers le dernier message
    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || !selectedConversation || !currentUser) {
            setError('Veuillez sélectionner une conversation et écrire un message');
            return;
        }

        try {
            const message = await sendMessage(
                selectedConversation.id,
                selectedConversation.type === 'PRIVEE' && !selectedConversation.id ? selectedConversation.participants[0].id : null,
                selectedConversation.type === 'GROUPE_FILIERE' && !selectedConversation.id ? selectedConversation.parcoursId || selectedConversation.participants[0].id : null,
                newMessage
            );
            setMessages([...messages, message]);
            setNewMessage('');
            if (!selectedConversation.id && message.conversationId) {
                setSelectedConversation({ ...selectedConversation, id: message.conversationId });
            }
            const updatedConversations = await getConversations({ t: Date.now() });
            // Trier à nouveau après mise à jour
            const sortedConversations = updatedConversations.sort((a, b) => {
                const dateA = a.lastMessage?.date ? new Date(a.lastMessage.date) : new Date(0);
                const dateB = b.lastMessage?.date ? new Date(b.lastMessage.date) : new Date(0);
                return dateB - dateA;
            });
            setConversations(sortedConversations);
            setError(null);
        } catch (error) {
            setError('Échec de l\'envoi du message');
        }
    };

    const onEmojiClick = (emojiObject) => {
        setNewMessage(prev => prev + emojiObject.emoji);
        setShowEmojiPicker(false);
    };

    const filteredConversations = conversations.filter(conv =>
        conv.sujet?.toLowerCase().includes(searchTerm.toLowerCase()) ||
        conv.participants.some(p => p.name.toLowerCase().includes(searchTerm.toLowerCase()))
    );

    const filteredMessages = messages.filter(msg =>
        msg.contenu.toLowerCase().includes(messageSearchTerm.toLowerCase())
    );

    const getConversationTitle = (conv) => {
        if (conv.type === 'GROUPE_FILIERE') {
            return `Groupe ${conv.sujet || 'Filière'}`;
        }
        if (conv.type === 'ADMIN') {
            return 'Administration';
        }
        const otherParticipants = conv.participants.filter(p => currentUser && p.id !== currentUser.id);
        return otherParticipants.length > 0
            ? otherParticipants.map(p => p.name).join(', ')
            : 'Conversation';
    };

    if (loading) {
        return (
            <Layout>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </Layout>
        );
    }

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
                        {filteredConversations.map(conv => (
                            <button
                                key={`${conv.type}-${conv.participants[0].id}`}
                                onClick={() => setSelectedConversation(conv)}
                                className={`w-full flex items-center space-x-3 p-2 rounded-md transition-colors relative ${selectedConversation?.participants[0].id === conv.participants[0].id &&
                                    selectedConversation?.type === conv.type
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-800 hover:bg-gray-300'
                                    }`}
                            >
                                <Avatar
                                    name={getConversationTitle(conv)}
                                    src={conv.participants.find(p => currentUser && p.id !== currentUser.id)?.avatar}
                                    size="40"
                                    round={true}
                                />
                                <div className="flex-1 text-left">
                                    <span>{getConversationTitle(conv)}</span>
                                    <div className="text-xs text-gray-500">
                                        {conv.lastMessage ? conv.lastMessage.contenu.substring(0, 20) + '...' : ''}
                                    </div>
                                </div>
                                {conv.unreadCount > 0 && (
                                    <span className="absolute right-2 top-2 h-3 w-3 bg-red-500 rounded-full"></span>
                                )}
                                <Circle
                                    className={`h-3 w-3 ${conv.participants.find(p => currentUser && p.id !== currentUser.id)?.onlineStatus === 'ONLINE' ? 'text-green-500' : 'text-gray-500'}`}
                                    fill="currentColor"
                                />
                            </button>
                        ))}
                    </div>
                </div>

                <div className="flex-1 flex flex-col" style={{ height: 'calc(100vh - 3.5rem)' }}>
                    <div className="flex items-center justify-between bg-[#4CAF50] p-4 font-semibold space-x-2">
                        <span className='text-white text-xl'>
                            {selectedConversation ? getConversationTitle(selectedConversation) : 'Sélectionnez une conversation'}
                        </span>
                        {selectedConversation && (
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
                        {error && <div className="text-red-500 text-center">{error}</div>}
                        {filteredMessages.length === 0 ? (
                            <div className="text-center text-gray-500 italic text-xl mt-40">
                                Aucun message
                            </div>
                        ) : (
                            filteredMessages.map((message) => (
                                <motion.div
                                    key={message.id}
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ duration: 0.3 }}
                                    className={`flex ${message.expediteur.id === currentUser.id ? 'justify-end' : 'justify-start'}`}
                                >
                                    <div className={`max-w-[70%] rounded-lg px-4 py-2 ${message.expediteur.id === currentUser.id ? 'bg-blue-600 text-white' : 'bg-gray-700 text-white'}`}>
                                        <div className="font-semibold text-sm mb-1">
                                            {message.expediteur.id === currentUser.id ? 'Moi' : message.expediteur.name}
                                        </div>
                                        <div>{message.contenu}</div>
                                        <div className="text-xs opacity-70 mt-1">
                                            {format(new Date(message.date), 'HH:mm')}
                                        </div>
                                    </div>
                                </motion.div>
                            ))
                        )}
                        <div ref={messagesEndRef} />
                    </div>
                    <form onSubmit={handleSendMessage} className="p-4">
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
                                className="flex-1 bg-white text-gray-800 rounded-full px-4 py-2 focus:outline-none focus:border-gray-500 focus:ring-[1px] focus:ring-gray-500 font-semibold border-2"
                                disabled={!selectedConversation}
                            />
                            <button
                                type="submit"
                                className={`px-2 py-1 items-center justify-center flex text-white rounded-lg ${newMessage.trim() ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-600 cursor-not-allowed'}`}
                                disabled={!newMessage.trim() || !selectedConversation}
                            >
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