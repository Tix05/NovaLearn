import React, { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import logo from '../assets/images/logo-principale.png';
import { Avatar } from 'primereact/avatar';
import { MdMenuOpen } from "react-icons/md";
import { PiStudent, PiPencilSimpleLineDuotone } from "react-icons/pi";
import { MdOutlineLibraryBooks, MdOutlineViewAgenda, MdHelpOutline } from "react-icons/md";
import { IoLibraryOutline } from "react-icons/io5";
import { FaRegNewspaper } from "react-icons/fa6";

const menuItems = [
    {
        to: '/etudiant/teacher',
        icons: <PiStudent size={30} />,
        label: 'Mes enseignant'
    },
    {
        to: '/mentions',
        icons: <MdOutlineLibraryBooks size={30} />,
        label: 'Mes mentions'
    },
    {
        to: '/inscription',
        icons: <PiPencilSimpleLineDuotone size={30} />,
        label: 'S\'inscrire'
    },
    {
        to: '/bibliotheque',
        icons: <IoLibraryOutline size={30} />,
        label: 'Bibliothèque'
    },
    {
        to: '/agenda',
        icons: <MdOutlineViewAgenda size={30} />,
        label: 'Mon agenda'
    },
    {
        to: '/actualites',
        icons: <FaRegNewspaper size={30} />,
        label: 'Actualités'
    },
    {
        to: '/aides',
        icons: <MdHelpOutline size={30} />,
        label: 'Aides'
    }
];

export default function Sidebar() {
    const [open, setOpen] = useState(true);
    const location = useLocation();

    return (
        <nav className={`h-screen p-2 flex flex-col justify-between duration-300 bg-white text-gray-700 shadow-2xl border-r-[1px] border-gray-500 ${open ? 'w-60' : 'w-16'}`}>
            <div className="px-2 h-20 space-y-5 flex flex-col justify-between items-center">
                <div
                    className={`duration-500 cursor-pointer ${!open ? 'mx-auto' : 'ml-auto'} rotate-${!open ? '180' : '0'}`}
                    onClick={() => setOpen(!open)}
                >
                    <MdMenuOpen size={34} />
                </div>
                <img src={logo} alt="Logo" className={`${open ? 'w-[200px]' : 'w-0'} rounded-md`} />
            </div>

            <ul>
                {
                    menuItems.map((item, index) => {
                        const isActive = location.pathname === item.to;

                        return (
                            <li key={index} className={`px-2 py-2 my-2 rounded-md duration-100 cursor-pointer flex gap-2 items-center relative group ${isActive ? 'bg-green-500 text-white' : 'hover:bg-[#39B54A] hover:text-white'}`}>
                                <Link to={item.to} className="flex gap-2 items-center w-full">
                                    <div>{item.icons}</div>
                                    <p className={`${!open && 'w-0 translate-x-24'} duration-500 overflow-hidden text-nowrap`}>{item.label}</p>
                                </Link>
                                <p className={`${open && 'hidden'} absolute left-32 shadow-md rounded-md w-0 p-0 text-black bg-white duration-100 overflow-hidden group-hover:w-fit group-hover:p-2 group-hover:left-16 text-nowrap`}>{item.label}</p>
                            </li>
                        )
                    })
                }
            </ul>
            <div className={`flex items-center py-2 ${!open ? 'space-x-0 px-0' : 'space-x-5 px-2'}`}>
                <Avatar icon="pi pi-user" size="large" shape="circle" />
                <div className={`leading-5 ${!open && 'w-0 translate-x-24'} duration-500 overflow-hidden`}>
                    <p>Tiavina</p>
                    <span className='text-xs'>Tiavina@gmail.com</span>
                </div>
            </div>
        </nav>
    );
}
