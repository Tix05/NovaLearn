import React, { useState, useEffect } from 'react';
import { Link, useLocation } from 'react-router-dom';
import logo from '../assets/images/logo-principale.png';
import { Avatar } from 'primereact/avatar';
import { RiDashboard2Line } from "react-icons/ri";
import { BiMenuAltLeft } from "react-icons/bi";
import { PiStudent } from 'react-icons/pi';
import { MdOutlineLibraryBooks, MdOutlineViewAgenda, MdOutlineMessage } from 'react-icons/md';
import { IoLibraryOutline } from 'react-icons/io5';


const menuItems = [
    {
        to: '/enseignant/dashboard',
        icons: <RiDashboard2Line size={25} />,
        label: 'Tableau de bord'
    },
    {
        to: '/enseignant/etudiant',
        icons: <PiStudent size={25} />,
        label: 'Mes étudiants'
    },
    {
        to: ['/enseignant/mention', '/enseignant/examen', '/enseignant/coursEnseignant/:mentionId', '/enseignant/coursEnseignant/:mentionId/:semestreId', '/enseignant/coursEnseignant/:mentionId/:semestreId/:coursId'],
        icons: <MdOutlineLibraryBooks size={25} />,
        label: 'Mes mentions'
    },
    {
        to: '/enseignant/bibliotheque',
        icons: <IoLibraryOutline size={25} />,
        label: 'Bibliothèque'
    },
    {
        to: '/enseignant/agenda',
        icons: <MdOutlineViewAgenda size={25} />,
        label: 'Mon agenda'
    },
    {
        to: '/enseignant/message',
        icons: <MdOutlineMessage size={25} />,
        label: 'Message'
    }


];

export default function SidebarEnseignant({ toggleSidebar, isSidebarVisible, isMobile }) {
    const [open, setOpen] = useState(true);
    const [user, setUser] = useState(null)
    const location = useLocation();

    useEffect(() => {

        const userData = JSON.parse(sessionStorage.getItem('teacher'));

        const defaultUser = {
            name: 'Utilisateur',
            email: 'utilisateur@example.com',
            avatar: null,
            ...userData
        };

        setUser(defaultUser);

        if (userData) {
            setUser(userData);
        }
        if (isMobile) {
            setOpen(true);
        }
    }, [isMobile]);

    return (
        <nav className={`
            h-screen p-2 flex flex-col justify-between duration-300 bg-white text-gray-700 shadow-2xl border-r-[1px] border-gray-500
            ${open ? 'w-60' : 'w-14'}
            ${isMobile ? 'fixed z-50 transition-transform duration-300' : 'relative w-0'}
            ${isMobile && !isSidebarVisible ? '-translate-x-full' : 'translate-x-0'}
        `}>
            <div className="px-2 h-20 space-y-5 flex flex-col justify-between items-center">
                <div
                    className={`duration-500 cursor-pointer ${!open ? 'mx-auto' : 'ml-auto'}`}
                    onClick={() => {
                        if (isMobile) {
                            toggleSidebar();
                        } else {
                            setOpen(!open);
                        }
                    }}
                >
                    <BiMenuAltLeft
                        size={34}
                        className={`transition-transform duration-500 ${open ? '-scale-x-100' : 'scale-x-100'}`}
                    />
                </div>
                <img src={logo} alt="Logo" className={`${open ? 'w-[200px]' : 'w-0'} rounded-md`} />
            </div>

            <ul>
                {
                    menuItems.map((item, index) => {
                        const isActive = Array.isArray(item.to)
                            ? item.to.some((path) => location.pathname.startsWith(path.replace(/:\w+/g, '')))
                            : location.pathname.startsWith(item.to);

                        return (
                            <li
                                key={index}
                                className={`px-2 py-2 my-2 rounded-md text-sm duration-100 cursor-pointer flex gap-2 items-center relative group 
                    ${isActive ? 'bg-green-500 text-white' : 'hover:bg-gray-300'}`}
                            >
                                <Link to={Array.isArray(item.to) ? item.to[0] : item.to} className="flex gap-2 items-center w-full">
                                    <div>{item.icons}</div>
                                    <p className={`${!open && 'w-0 translate-x-24'} duration-500 overflow-hidden text-nowrap`}>{item.label}</p>
                                </Link>
                                <p className={`${open && 'hidden'} absolute left-32 shadow-md rounded-md w-0 p-0 text-black bg-white duration-100 overflow-hidden group-hover:w-fit group-hover:p-2 group-hover:left-16 text-nowrap z-50`}>{item.label}</p>
                            </li>
                        );
                    })
                }
            </ul>

            {user && (
                <div className={`flex items-center py-2 transition-all duration-500 ${!open ? 'space-x-0 px-0' : 'space-x-5 px-2'}`}>
                    <Avatar
                        image={user.avatar ? `/uploads/avatars/${user.avatar}` : null}
                        icon={!user.avatar ? "pi pi-user" : null}
                        size="large"
                        shape="circle"
                        className={`transition-all duration-500 ${!open ? 'opacity-0 scale-0' : 'opacity-100 scale-100'}`}
                    />
                    <div className={`leading-5 ${!open && 'w-0 translate-x-24'} duration-500 overflow-hidden`}>
                        <p>{user.name}</p>
                        <span className='text-xs'>{user.email}</span>
                    </div>
                </div>
            )}

        </nav>
    );
}
