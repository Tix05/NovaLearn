import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from 'primereact/inputtext';
import { Password } from "primereact/password";
import { Link, useLocation, useNavigate } from 'react-router-dom';

const Inscription2 = () => {
    const location = useLocation();
    const navigate = useNavigate();

    const userData = location.state;
    const [email, setEmail] = useState('');
    const [mdp, setMdp] = useState('');
    const [confirmMdp, setConfirmMdp] = useState('');
    const [errorMessage, setErrorMessage] = useState('');

    useEffect(() => {
        if (!userData) {
            navigate('/etudiant/information');
        }
    }, [userData, navigate]);

    const isFormValid = email && mdp && confirmMdp && mdp === confirmMdp;

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isFormValid) {
            const finalUserData = { ...userData, email, mdp };
            console.log("Données finales envoyées :", finalUserData);
        } else {
            setErrorMessage("Veuillez remplir tous les champs correctement.");
        }
    };

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
                    <h1 className='font-medium text-4xl'>Inscription</h1>
                </div>
                <form className='flex-col mt-10 items-center flex space-y-7' onSubmit={handleSubmit}>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-envelope text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText id="email" className='input-focus' value={email} onChange={(e) => setEmail(e.target.value)} />
                            <label htmlFor="email">Adresse email</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-password-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-lock text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <Password id="mdp" className={`password-focus ${mdp && confirmMdp && mdp !== confirmMdp ? 'p-invalid' : ''}`} value={mdp} onChange={(e) => setMdp(e.target.value)} />
                            <label htmlFor="mdp">Mot de passe</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-password-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-lock text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <Password
                                id="confirmMdp"
                                className={`password-focus ${mdp && confirmMdp && mdp !== confirmMdp ? 'p-invalid' : ''}`}
                                value={confirmMdp}
                                onChange={(e) => setConfirmMdp(e.target.value)}
                            />
                            <label htmlFor="confirmMdp">Confirmer mot de passe</label>
                        </FloatLabel>
                    </div>

                    {mdp && confirmMdp && mdp !== confirmMdp && (
                        <p className="text-red-500">Les mots de passe ne correspondent pas.</p>
                    )}
                    <div className='flex items-center justify-center space-x-10'>
                        <Link to="/etudiant/information" className='text-gray-800 bg-gray-400 py-2 px-5 w-full font-semibold rounded-sm cursor-pointer hover:scale-105 transition duration-500'>Précédent</Link>
                        <button
                            type="submit"
                            disabled={!isFormValid}
                            className={`py-2 px-5 full font-semibold rounded-sm cursor-pointer transition duration-500 
                            ${isFormValid ? 'bg-[#DD646E] text-white hover:bg-[#cb7c7c] hover:scale-105' : 'bg-gray-400 text-gray-200 cursor-not-allowed'}`}
                        >
                            S'inscrire
                        </button>
                    </div>
                </form>
                <div className='flex items-center justify-center mt-3 space-x-2 text-sm'>
                    <p className='font-semibold'>Vous avez déjà un compte?</p>
                    <Link to="/etudiant/login-etudiant" className='font-semibold text-blue-500 hover:scale-105 duration-500'>Se connecter</Link>
                </div>
            </div>
        </motion.div>
    );
};

export default Inscription2;
