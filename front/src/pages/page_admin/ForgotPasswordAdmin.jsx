import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { motion } from 'framer-motion';

const ForgotPasswordAdmin = () => {

    const [email, setEmail] = useState('');

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
                <div className='flex flex-col items-center justify-center'>
                    <p className='text-gray-700 text-5xl font-light'>Réinitialisez votre mot de passe!</p>
                </div>
                <form action="" className='flex-col mt-10 items-center flex space-y-10'>
                    <div className="relative w-[350px]">
                        <input
                            type="email"
                            id="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                            className="peer w-full border-b-2 border-gray-300 focus:border-[#DD646E] rounded-none bg-transparent outline-none placeholder-transparent"
                            placeholder=""
                        />
                        <label
                            htmlFor="email"
                            className="absolute left-0 -top-4 text-gray-600 text-sm transition-all duration-200 ease-in-out peer-placeholder-shown:top-0 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-focus:-top-4 peer-focus:text-sm peer-focus:text-[#DD646E]"
                        >
                            Email
                        </label>
                    </div>
                    <input type="submit" value="Envoyer" className='bg-[#DD646E] text-white py-2 w-[350px] font-semibold rounded-sm cursor-pointer hover:bg-[#cb7c7c] hover:scale-105 duration-500' />
                </form>
                <div className='flex items-center justify-center mt-3 space-x-2 text-sm'>
                    <p className='font-semibold'>Vous avez déjà un compte?</p>
                    <Link to="/admin/login" className='font-semibold text-blue-500 hover:scale-105 duration-500'>Se connecter</Link>
                </div>
            </div>
        </motion.div>
    );
};

export default ForgotPasswordAdmin;