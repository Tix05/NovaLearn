import React from 'react';
import Sidebar from '../components/SidebarAdmin';
import Navbar from '../components/NavbarAdmin';

const LayoutAdmin = ({ children }) => {
    return (
        <div className="flex h-screen">
            <Sidebar />
            <div className="flex-1 flex flex-col overflow-hidden">
                <Navbar />
                <main className="flex-1 overflow-hidden h-screen custom-scrollbar bg-white">
                    {children}
                </main>
            </div>
        </div>
    );
};

export default LayoutAdmin;