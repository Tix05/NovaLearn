import React, { useState, useEffect } from 'react';
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from 'primereact/inputtext';
import { Password } from "primereact/password";
import { Link, useNavigate } from 'react-router-dom';
import { motion } from 'framer-motion';
import { login, getCurrentUser } from '../../Services/authService';
import Loading from '../Loading';
import { MdErrorOutline } from "react-icons/md";

const LoginEtudiant = () => {
    const [email, setEmail] = useState('');
    const [mdp, setMdp] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const navigate = useNavigate();
    const [authSuccess, setAuthSuccess] = useState(false);

    useEffect(() => {
        const user = getCurrentUser();
        if (user && user.roles && user.roles.includes('ROLE_ETUDIANT')) {
            navigate('/etudiant/dashboard');
        }
    }, [navigate]);

    const handleSubmit = async (e) => {
        e.preventDefault();

        setLoading(true);
        setError(null);

        try {
            const response = await login(email, mdp);

            if (response.roles && response.roles.includes('ROLE_ETUDIANT')) {
                setAuthSuccess(true);
                setTimeout(() => {
                    navigate('/etudiant/dashboard');
                }, 2000);
            } else {
                logout();
                setError("Vous n'avez pas accès à l'espace étudiant");
            }
        } catch (err) {
            console.error('Erreur de connexion:', err);
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };
    if (authSuccess) {
        return <Loading />;
    }
    return (
        <motion.div
            className='w-full min-h-screen flex'
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 1 }}>
            <div className='w-1/2 flex flex-col items-center justify-center bg-black image-login'>
            </div>
            <div className='w-1/2 flex flex-col justify-center bg-white'>
                <div className='flex flex-col items-center justify-center space-y-3'>
                    <h1 className='font-bold text-2xl'>ESPACE ETUDIANT</h1>
                    <p className='text-gray-700 font-semibold'>Connectez-vous à votre compte pour continuer</p>
                </div>
                <form onSubmit={handleSubmit} className='flex-col mt-10 items-center flex space-y-10'>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-green-400">
                            <i className="pi pi-envelope text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText
                                id="email"
                                className='input-focus'
                                value={email}
                                onChange={(e) => setEmail(e.target.value)}
                                required
                                type="email"
                                disabled={loading}
                            />
                            <label htmlFor="email">Adresse email</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-password-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-green-400">
                            <i className="pi pi-lock text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <Password
                                id="mdp"
                                className='password-focus'
                                value={mdp}
                                onChange={(e) => setMdp(e.target.value)}
                                required
                                feedback={false}
                            />
                            <label htmlFor="mdp">Mot de passe</label>
                        </FloatLabel>
                    </div>
                    <button
                        type="submit"
                        className={`bg-[#DD646E] text-white py-2 w-[350px] font-semibold rounded-sm hover:bg-[#cb7c7c] duration-500 disabled:opacity-50 ${!loading && 'hover:scale-105 cursor-pointer'
                            }`}
                        disabled={loading}
                    >
                        {loading ? (
                            <>
                                <i className="pi pi-spin pi-spinner mr-2"></i>
                                Connexion en cours...
                            </>
                        ) : 'Se connecter'}
                    </button>

                </form>
                {error && (
                    <div className="flex justify-center items-center mt-2 text-red-500 font-semibold space-x-1">
                        <MdErrorOutline size={20} />
                        <p >{error}</p>

                    </div>
                )}
                <div className='flex flex-col items-center mt-3 space-y-3'>
                    <Link
                        to="/etudiant/forgot-password"
                        className='font-semibold hover:scale-105 duration-500 text-sm text-gray-700 border-b-2 border-gray-700'
                    >
                        Mot de passe oublié ?
                    </Link>
                    {/* <Link
                        to="/etudiant/inscription-etape-1"
                        className='bg-[#64883E] text-center text-white shadow-2xl py-2 w-[350px] font-semibold rounded-sm cursor-pointer hover:bg-[#3e8842] hover:scale-105 duration-500'
                    >
                        S'inscrire
                    </Link> */}
                </div>
            </div>
        </motion.div>
    );
};

export default LoginEtudiant;