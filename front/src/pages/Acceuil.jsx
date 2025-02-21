import React from 'react';
import { Link } from 'react-router-dom';
import bandeau from '../assets/svg/bandeau.svg';
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
                transition={{ duration: 1, delay: 1 }}>
                <img src={bandeau} alt="bandeau" className='w-[300px]' />
            </motion.div>
            <motion.div
                className='mt-10 text-white text-center'
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{ duration: 1, delay: 0.5 }}
            >
                <h1 className='text-5xl font-semibold'>Ecole SUpérieure de Management</h1>
                <div className='text-5xl mt-5 space-y-3 font-[Crimson]'>
                    <p>Formation à distance</p>
                    <ReactTyped
                        strings={["\"La défi de la réussite\""]}
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