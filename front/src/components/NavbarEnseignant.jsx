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
import { User, Plus, X } from 'lucide-react';
import { BiMenuAltLeft } from 'react-icons/bi';

const ProfileDialog = ({ show, onClose, formData, setFormData, handleSubmit, handleFileChange }) => {
    const [activeTab, setActiveTab] = React.useState('informations');

    const renderContent = () => {
        switch (activeTab) {
            case 'password':
                return (
                    <form className="p-2 md:p-4" onSubmit={(e) => e.preventDefault()}>
                        <h2 className="text-xl font-semibold mb-4">Changement de Mot de passe</h2>
                        <div className="space-y-3">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Ancien mot de passe
                                </label>
                                <input
                                    type="password"
                                    className="w-full py-2 border border-gray-300 rounded-md"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Nouveau mot de passe
                                </label>
                                <input
                                    type="password"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Confirmer le nouveau mot de passe
                                </label>
                                <input
                                    type="password"
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>
                            <button
                                type="submit"
                                className="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors"
                            >
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                );
            default:
                return (
                    <form onSubmit={handleSubmit} className="w-full p-4 md:p-5 gap-y-4">
                        <div className='gap-4 grid grid-cols-2 w-full items-center justify-center'>
                            <div className="w-60">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Telephone
                                </label>
                                <input
                                    type="text"
                                    value={formData.telephone}
                                    onChange={(e) => setFormData({ ...formData, telephone: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>

                            <div className="w-60">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Nom
                                </label>
                                <input
                                    type="text"
                                    value={formData.nom}
                                    onChange={(e) => setFormData({ ...formData, nom: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>

                            <div className="w-60">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    value={formData.email}
                                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>

                            <div className="w-60">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Ville
                                </label>
                                <input
                                    type="text"
                                    value={formData.ville}
                                    onChange={(e) => setFormData({ ...formData, ville: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>

                            <div className="w-60">
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Adresse
                                </label>
                                <input
                                    type="text"
                                    value={formData.adresse}
                                    onChange={(e) => setFormData({ ...formData, adresse: e.target.value })}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-md"
                                />
                            </div>
                        </div>

                        <div className="col-span-2 mt-6">
                            <button
                                type="submit"
                                className="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 transition-colors w-full"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>
                );
        }
    };

    return (
        <AnimatePresence>
            {show && (
                <motion.div
                    className="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                >
                    <button
                        onClick={onClose}
                        className="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-10 bg-white rounded-full p-1 shadow-md flex items-center justify-center"
                    >
                        <X className="w-8 h-8" />
                    </button>
                    <motion.div
                        className="bg-white rounded-lg shadow-lg w-full max-w-5xl max-h-[90vh] overflow-y-auto custom-scrollbar"
                        initial={{ opacity: 0, scale: 0.95 }}
                        animate={{ opacity: 1, scale: 1 }}
                        exit={{ opacity: 0, scale: 0.95 }}
                        transition={{ duration: 0.2 }}
                    >
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-800 p-6 pb-0">Mon profil</h1>

                            <div className="flex flex-col md:flex-row sm:p-4 p-1 gap-6">
                                <div className="bg-gray-50 p-6 rounded-lg w-full md:w-1/4">
                                    <div className="flex flex-col items-center">
                                        <div className="relative">
                                            {formData.photo ? (
                                                <img
                                                    src={formData.photo}
                                                    alt="Profil"
                                                    className="w-24 h-24 rounded-full mb-4 object-cover"
                                                />
                                            ) : (
                                                <div className="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                                                    <User className="w-12 h-12 text-gray-400" />
                                                </div>
                                            )}
                                            <label className="absolute right-0 bottom-4 text-gray-500 hover:text-gray-700 z-10 bg-white rounded-full p-1 shadow-md flex items-center justify-center">
                                                <Plus className="w-7 h-7 text-gray-800" />
                                                <input type="file" className="hidden" onChange={handleFileChange} />
                                            </label>
                                        </div>
                                        <h2 className="text-lg font-medium text-gray-900 mb-2 text-center">{formData.nom}</h2>
                                        <p className="text-gray-600 text-center">Etudiant</p>
                                    </div>
                                </div>

                                <div className="w-full md:w-3/4">
                                    <div className="border-b border-gray-200">
                                        <nav className="flex md:w-full">
                                            <button
                                                onClick={() => setActiveTab('informations')}
                                                className={`${activeTab === 'informations'
                                                    ? 'border-red-800 text-red-800'
                                                    : 'text-gray-500 hover:text-gray-700'
                                                    } whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm sm:text-base`}
                                            >
                                                Mes informations
                                            </button>
                                            <button
                                                onClick={() => setActiveTab('password')}
                                                className={`${activeTab === 'password'
                                                    ? 'border-red-800 text-red-800'
                                                    : 'text-gray-500 hover:text-gray-700'
                                                    } whitespace-nowrap py-4 px-4 sm:px-6 border-b-2 font-medium text-sm sm:text-base`}
                                            >
                                                Mot de passe
                                            </button>
                                        </nav>
                                    </div>
                                    {renderContent()}
                                </div>
                            </div>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
};

const NavbarEnseignant = ({ isMobile, toggleSidebar }) => {
    const [showProfileDropdown, setShowProfileDropdown] = useState(false);
    const [showNotificationDropdown, setShowNotificationDropdown] = useState(false);
    const [showMessageDropdown, setShowMessageDropdown] = useState(false);
    const [showProfileDialog, setShowProfileDialog] = useState(false);
    const [unreadNewsCount, setUnreadNewsCount] = useState(4);
    const [unreadMessagesCount, setUnreadMessagesCount] = useState(5);
    const [currentTime, setCurrentTime] = useState(new Date());

    const [formData, setFormData] = useState({
        nom: 'Jean Dupont',
        email: 'jean.dupont@example.com',
        telephone: '06 12 34 56 78',
        poste: 'Professeur de Mathématiques',
        departement: 'Sciences',
        photo: ''
    });

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

    const handleSubmit = (e) => {
        e.preventDefault();
        console.log('Données sauvegardées:', formData);
        setShowProfileDialog(false);
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                setFormData({ ...formData, photo: reader.result });
            };
            reader.readAsDataURL(file);
        }
    };

    return (
        <>
            <header className="w-full h-14 border-b-[1px] border-gray-500 shadow-lg flex items-center justify-between md:justify-end px-4 sm:px-6 lg:px-8 bg-white sticky top-0 z-40">

                {isMobile && (
                    <button
                        onClick={toggleSidebar}
                        className="mr-2 text-gray-700 hover:text-green-500 transition-colors"
                    >
                        <BiMenuAltLeft size={24} />
                    </button>
                )}

                <div className="flex items-center gap-x-3">
                    <div className='relative'>
                        <Avatar icon="pi pi-envelope" shape="circle" className='hover:cursor-pointer p-overlay-badge' onClick={() => handleDropdownToggle('message')} >
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
                        <Avatar icon="pi pi-bell" shape="circle" className='hover:cursor-pointer p-overlay-badge' onClick={() => handleDropdownToggle('notification')} >
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
                        <Avatar icon="pi pi-user" shape="circle" className='hover:cursor-pointer' onClick={() => handleDropdownToggle('profile')} />
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
                                        <div
                                            className='flex items-center hover:bg-gray-200 p-2'
                                            onClick={() => {
                                                setShowProfileDialog(true);
                                                setShowProfileDropdown(false);
                                            }}
                                        >
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
            </header>

            <ProfileDialog
                show={showProfileDialog}
                onClose={() => setShowProfileDialog(false)}
                formData={formData}
                setFormData={setFormData}
                handleSubmit={handleSubmit}
                handleFileChange={handleFileChange}
            />
        </>
    );
};

export default NavbarEnseignant;