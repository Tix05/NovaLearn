import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { Link, useNavigate } from 'react-router-dom';
import { FloatLabel } from "primereact/floatlabel";
import { InputText } from 'primereact/inputtext';
import { Dropdown } from 'primereact/dropdown';

const Inscription = () => {
    const [name, setName] = useState('');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [adresse, setAdresse] = useState('');
    const [selectedRegion, setSelectedRegion] = useState(null);

    const navigate = useNavigate();

    const regions = [
        { name: 'Antananarivo' },
        { name: 'Toamasina' },
        { name: 'Antsirabe' },
        { name: 'Fianarantsoa' },
        { name: 'Mahajanga' },
        { name: 'Toliara' },
        { name: 'Antsiranana' },
        { name: 'Morondava' },
        { name: 'Manakara' },
        { name: 'Ambositra' },
        { name: 'Sambava' },
        { name: 'Farafangana' },
        { name: 'Ambatondrazaka' },
        { name: 'Maintirano' },
        { name: 'Amboasary' },
        { name: 'Andapa' },
        { name: 'Mandritsara' },
        { name: 'Ambanja' },
        { name: 'Ihosy' },
        { name: 'Mananjary' },
        { name: 'Marovoay' },
        { name: 'Tsiroanomandidy' }
    ];

    const isFormValid = name && phoneNumber && adresse && selectedRegion;

    const handleNext = (e) => {
        e.preventDefault();
        navigate('/etudiant/inscription-etape-2', {
            state: {
                name,
                phoneNumber,
                adresse,
                selectedRegion
            }
        });
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
                <form className='flex-col mt-10 items-center flex space-y-7' onSubmit={handleNext}>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-user text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText id="name" className='input-focus' value={name} onChange={(e) => setName(e.target.value)} />
                            <label htmlFor="name">Nom</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-phone text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText id="phoneNumber" className='input-focus' value={phoneNumber} onChange={(e) => setPhoneNumber(e.target.value)} />
                            <label htmlFor="phoneNumber">Téléphone</label>
                        </FloatLabel>
                    </div>
                    <div className="custom-dropdown-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-map-marker text-white"></i>
                        </span>
                        <Dropdown
                            value={selectedRegion}
                            onChange={(e) => setSelectedRegion(e.value)}
                            options={regions}
                            optionLabel="name"
                            placeholder="Sélectionner la région"
                            className="custom-dropdown p-dropdown-focus"
                        />
                    </div>
                    <div className="custom-float-label-container flex shadow-2xl">
                        <span className="p-inputgroup-addon bg-emerald-400">
                            <i className="pi pi-home text-white"></i>
                        </span>
                        <FloatLabel className="custom-float-label">
                            <InputText id="adresse" className='input-focus' value={adresse} onChange={(e) => setAdresse(e.target.value)} />
                            <label htmlFor="adresse">Adresse</label>
                        </FloatLabel>
                    </div>

                    <button
                        type="submit"
                        disabled={!isFormValid}
                        className={`py-2 w-[350px] font-semibold rounded-sm cursor-pointer transition duration-500 
                            ${isFormValid ? 'bg-[#DD646E] text-white hover:bg-[#cb7c7c] hover:scale-105 cursor-pointer' : 'bg-gray-400 text-gray-200 cursor-default'}`}
                    >
                        Suivant
                    </button>
                </form>
                <div className='flex items-center justify-center mt-3 space-x-2 text-sm'>
                    <p className='font-semibold'>Vous avez déjà un compte?</p>
                    <Link to="/etudiant/login-etudiant" className='font-semibold text-blue-500 hover:scale-105 duration-500'>Se connecter</Link>
                </div>
            </div>
        </motion.div>
    );
};

export default Inscription;
