import React, { useState, useEffect } from 'react';
import { Avatar } from 'primereact/avatar';
import { motion, AnimatePresence } from 'framer-motion';
import { MdOutlineSettings } from 'react-icons/md';
import { TbLogout2 } from 'react-icons/tb';
import { Divider } from 'primereact/divider';
import { Badge } from 'primereact/badge';
import { Link } from 'react-router-dom';
import { FaMessage, FaRegNewspaper } from 'react-icons/fa6';
import { formatDistanceToNow } from 'date-fns';
import { fr } from 'date-fns/locale';

const Navbar = () => {
    const [showProfileDropdown, setShowProfileDropdown] = useState(false);
    const [showNotificationDropdown, setShowNotificationDropdown] = useState(false);
    const [showMessageDropdown, setShowMessageDropdown] = useState(false);

    const [unreadNewsCount, setUnreadNewsCount] = useState(4);
    const [unreadMessagesCount, setUnreadMessagesCount] = useState(5);
    const [currentTime, setCurrentTime] = useState(new Date());

    useEffect(() => {
        const interval = setInterval(() => {
            setCurrentTime(new Date());
        }, 60000);

        return () => clearInterval(interval);
    }, []);

    const [messages, setMessages] = useState([
        { id: 1, user: 'Koto', group: 'Gestion L1', content: 'Bonjour à tous', time: new Date(Date.now() - 3 * 60 * 1000) },
        { id: 2, user: 'Randria', group: 'Prof', content: 'Réunion à 15h', time: new Date(Date.now() - 10 * 60 * 1000) },
        { id: 3, user: 'Zaka', group: 'Gestion L1', content: 'Rapport à envoyer', time: new Date(Date.now() - 8 * 60 * 1000) },
    ]);

    const formatTimeElapsed = (date) => {
        const now = new Date();
        const diffInDays = Math.floor((now - date) / (1000 * 60 * 60 * 24));

        if (diffInDays > 7) {
            return '7 jours et plus';
        } else {
            return formatDistanceToNow(date, { addSuffix: true, locale: fr });
        }
    };

    const handleDropdownToggle = (dropdown) => {
        setShowProfileDropdown(dropdown === 'profile' ? !showProfileDropdown : false);
        setShowNotificationDropdown(dropdown === 'notification' ? !showNotificationDropdown : false);
        setShowMessageDropdown(dropdown === 'message' ? !showMessageDropdown : false);
    };

    return (
        <div className='w-full h-14 border-b-[1px] border-gray-500 shadow-xl justify-end flex items-center px-10 gap-x-3'>
            <div className='relative'>
                <Avatar
                    icon="pi pi-envelope"
                    shape="circle"
                    className='hover:cursor-pointer p-overlay-badge'
                    onClick={() => handleDropdownToggle('message')}
                >
                    <Badge value={unreadMessagesCount} severity="danger" className="custom-badge" />
                </Avatar>
                <AnimatePresence>
                    {showMessageDropdown && (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            transition={{ duration: 0.2 }}
                            className="absolute py-2 w-[250px] bg-white text-gray-700 font-semibold rounded-sm drop-shadow-lg border-[1px] right-0 mt-1 z-50"
                        >
                            <h1 className='text-center text-lg'>Messages</h1>
                            <Divider />
                            {messages.slice(0, 3).map(message => (
                                <div key={message.id} className='flex items-center hover:bg-gray-200 hover:cursor-pointer p-2 gap-2'>
                                    <div className='flex'>
                                        <Avatar shape='circle' icon='pi pi-user' />
                                    </div>
                                    <div className='flex flex-col items-start gap-y-1'>
                                        <h1>{message.user} ({message.group})</h1>
                                        <p className='text-xs'>{message.content}</p>
                                        <i className='pi pi-clock text-xs flex gap-x-2'>
                                            <p>{formatTimeElapsed(message.time)}</p>
                                        </i>
                                    </div>
                                </div>
                            ))}
                            <Divider />
                            <Link to="#" className='items-center justify-center flex hover:bg-gray-200 text-xs p-2'>
                                Afficher plus ...
                            </Link>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            <div className='relative'>
                <Avatar
                    icon="pi pi-bell"
                    shape="circle"
                    className='hover:cursor-pointer p-overlay-badge'
                    onClick={() => handleDropdownToggle('notification')}
                >
                    <Badge value={unreadNewsCount + unreadMessagesCount} severity="danger" className="custom-badge" />
                </Avatar>
                <AnimatePresence>
                    {showNotificationDropdown && (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            transition={{ duration: 0.2 }}
                            className="absolute py-2 w-[220px] bg-white text-gray-700 font-semibold rounded-sm drop-shadow-lg border-[1px] right-0 mt-1 z-50"
                        >
                            <h1 className='text-center text-lg'>Notifications</h1>
                            <Divider />
                            <ul className='text-left'>
                                <div className='flex items-center hover:bg-gray-200 p-2'>
                                    <FaRegNewspaper className='text-xl mr-2' />
                                    <li className='cursor-pointer'>{unreadNewsCount} nouveaux actualités</li>
                                </div>
                                <div className='flex items-center hover:bg-gray-200 p-2'>
                                    <FaMessage className='text-xl mr-2' />
                                    <li className='cursor-pointer'>{unreadMessagesCount} messages non lus</li>
                                </div>
                            </ul>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            <div className='relative'>
                <Avatar
                    icon="pi pi-user"
                    shape="circle"
                    className='hover:cursor-pointer'
                    onClick={() => handleDropdownToggle('profile')}
                />
                <AnimatePresence>
                    {showProfileDropdown && (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            transition={{ duration: 0.2 }}
                            className="absolute py-2 w-[150px] bg-white text-gray-700 font-semibold rounded-sm drop-shadow-lg border-[1px] right-0 mt-1 z-50"
                        >
                            <h1 className='text-center text-lg'>Profil</h1>
                            <Divider />
                            <ul className='text-left'>
                                <div className='flex items-center hover:bg-gray-200 p-2'>
                                    <MdOutlineSettings className='text-xl mr-2' />
                                    <li className='cursor-pointer'>Paramètres</li>
                                </div>
                                <div className='flex items-center hover:bg-red-200 p-2'>
                                    <TbLogout2 className='text-xl mr-2' />
                                    <li className='cursor-pointer'>Déconnexion</li>
                                </div>
                            </ul>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
};

export default Navbar;