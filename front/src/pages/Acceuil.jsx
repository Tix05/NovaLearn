import React from 'react';
import { Link } from 'react-router-dom';
import bandeau from '../assets/images/logo.png';
import { ReactTyped } from 'react-typed';
import { motion } from 'framer-motion';


const Acceuil = () => {
    return (
        <motion.div
            className='h-screen w-full flex flex-col items-center bg-black image-bg'
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 1 }}
        >
            <motion.div
                initial={{ y: -50, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                transition={{ duration: 1, delay: 1 }}
                className='bg-white p-10 rounded-b-[130px]'
                style={{
                    clipPath: 'polygon(0% 0%, 100% 0%, calc(100% - 50px) 100%, 50px 100%)',
                }}
            >
                <img src={bandeau} alt="bandeau" className='w-[250px]' />
            </motion.div>
            <motion.div
                className='mt-10 text-white text-center'
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{ duration: 1, delay: 0.5 }}
            >
                <h1 className='text-5xl font-semibold'>Innovation Learning</h1>
                <div className='text-5xl mt-5 font-[Crimson]'>
                    <p className='mb-3'>Formation à distance</p>
                    <ReactTyped
                        strings={["\"L'ambition de la réussite\""]}
                        typeSpeed={50}
                    />
                </div>
            </motion.div>
            <motion.div
                className='text-white text-xl mt-20 space-x-10'
                initial={{ y: 50, opacity: 0 }}
                animate={{ y: 0, opacity: 1 }}
                transition={{ duration: 1, delay: 1 }}>
                <Link to="/etudiant/login-etudiant" className='button-scale'>Espace étudiant</Link>
                <Link to="/enseignant/login-enseignant" className='button-scale'>Espace enseignant</Link>
            </motion.div>
        </motion.div>
    );
};

export default Acceuil;