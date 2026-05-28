import React, { useState, useEffect } from 'react';
import Sidebar from '../components/SidebarEnseignant';
import Navbar from '../components/NavbarEnseignant';

const LayoutEnseignant = ({ children }) => {
    const [isMobile, setIsMobile] = useState(false);
    const [isSidebarVisible, setIsSidebarVisible] = useState(false);

    useEffect(() => {
        const handleResize = () => {
            setIsMobile(window.innerWidth < 768);
        };

        handleResize();
        window.addEventListener('resize', handleResize);

        return () => window.removeEventListener('resize', handleResize);
    }, []);

    const toggleSidebar = () => {
        setIsSidebarVisible(!isSidebarVisible);
    };
    if (!isMobile) {
        return (
            <div className="flex flex-col h-screen">
                <Navbar isMobile={false} toggleSidebar={toggleSidebar} />
                <div className="flex flex-1 overflow-hidden">
                    <Sidebar
                        isMobile={false}
                        isSidebarVisible={true}
                        toggleSidebar={toggleSidebar}
                    />
                    <main className="flex-1 overflow-y-auto custom-scrollbar bg-white">
                        {children}
                    </main>
                </div>
            </div>
        );
    }

    return (
        <div className="flex h-screen relative">
            <Sidebar
                isMobile={true}
                isSidebarVisible={isSidebarVisible}
                toggleSidebar={toggleSidebar}
            />
            <div className="flex-1 flex flex-col overflow-hidden">
                <Navbar isMobile={true} toggleSidebar={toggleSidebar} />
                <main className="flex-1 overflow-y-auto custom-scrollbar bg-white">
                    {children}
                </main>
            </div>

            {isSidebarVisible && (
                <div
                    className="fixed inset-0 bg-black bg-opacity-50 z-40"
                    onClick={toggleSidebar}
                />
            )}
        </div>
    );
};

export default LayoutEnseignant;