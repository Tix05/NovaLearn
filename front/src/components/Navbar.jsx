import React, { useState } from 'react';
import { Avatar } from 'primereact/avatar';
import { motion, AnimatePresence } from 'framer-motion';
import { MdOutlineSettings } from 'react-icons/md';
import { TbLogout2 } from 'react-icons/tb';
import { Divider } from 'primereact/divider';

const Navbar = () => {
    const [showProfileDropdown, setShowProfileDropdown] = useState(false);

    return (
        <div className='w-full h-14 border-b-[1px] border-gray-500 shadow-xl justify-end flex items-center px-10'>
            <div className='relative'>
                <Avatar
                    icon="pi pi-user"
                    shape="circle"
                    className='hover:cursor-pointer'
                    onClick={() => setShowProfileDropdown(!showProfileDropdown)}
                />
                <AnimatePresence>
                    {showProfileDropdown && (
                        <motion.div
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            transition={{ duration: 0.2 }}
                            className="absolute py-2 w-[150px] bg-white text-gray-700 font-semibold rounded-sm drop-shadow-lg border-[1px] right-0 mt-1"
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