import React, { useState } from 'react';
import { FloatLabel } from "primereact/floatlabel"
import { InputText } from 'primereact/inputtext'
import { Password } from "primereact/password"
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';

const LoginEtudiant = () => {

    const [email, setEmail] = useState('');
    const [mdp, setMdp] = useState('');

    return (
        <motion.div
            className='w-full min-h-screen flex'
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 1 }}>
            <div className='w-1/2 flex flex-col items-center justify-center bg-black image-login'>
            </div>
            <div
                className='w-1/2 flex flex-col justify-center bg-white'>
                <div className='flex flex-col items-center justify-center space-y-3'>
                    <h1 className='font-bold text-2xl'>ESPACE ETUDIANT</h1>
                    <p className='text-gray-700 font-semibold'>Connectez-vous à votre compte pour continuer</p>
                </div>
                <form action="" className='flex-col mt-10 items-center flex space-y-10'>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-green-400">
                            <i className="pi pi-envelope text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText id="email" className='input-focus' value={email} onChange={(e) => setEmail(e.target.value)} />
                            <label htmlFor="email">Adresse email</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-password-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-green-400">
                            <i className="pi pi-lock text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <Password id="mdp" className='password-focus' value={mdp} onChange={(e) => setMdp(e.target.value)} />
                            <label htmlFor="mdp">Mot de passe</label>
                        </FloatLabel>
                    </div>
                    <input type="submit" value="Se connecter" className='bg-[#DD646E] text-white py-2 w-[350px] font-semibold rounded-sm cursor-pointer hover:bg-[#cb7c7c] hover:scale-105 duration-500' />
                </form>
                <div className='flex flex-col items-center mt-3 space-y-3'>
                    <Link to="/etudiant/forgot-password" className='font-semibold hover:scale-105 duration-500 text-sm text-gray-700'>Mot de passe oublié ?</Link>
                    <Link to="/etudiant/information" className='bg-[#64883E] text-center text-white shadow-2xl py-2 w-[350px] font-semibold rounded-sm cursor-pointer hover:bg-[#3e8842] hover:scale-105 duration-500'>S'inscrire</Link>
                </div>
            </div>
        </motion.div>
    );
};

export default LoginEtudiant;