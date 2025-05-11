import React, { useState, useEffect } from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { Link } from 'react-router-dom';
import { FaRegEye } from 'react-icons/fa';
import { getStudentMentions } from '../../Services/authService';
import { ProgressSpinner } from 'primereact/progressspinner';

const defaultIcon = 'data:image/svg+xml;base64,' + btoa(`
<svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
</svg>
`);

const Mention = () => {
    const [mentions, setMentions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchMentions = async () => {
            try {
                const data = await getStudentMentions();
                setMentions(data);
            } catch (err) {
                setError(err.message);
            } finally {
                setLoading(false);
            }
        };

        fetchMentions();
    }, []);

    if (loading) {
        return (
            <Layout>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </Layout>
        );
    }
    if (error) return <Layout><div className="h-[90vh] w-full font-semibold items-center justify-center flex text-lg text-red-500">{error}</div></Layout>;

    return (
        <Layout>
            <div className='w-full text-gray-800 overflow-x-hidden custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='p-4 md:text-3xl text-xl font-normal'>Mes mentions</h1>
                <div className='grid grid-cols-2 gap-5 p-5 justify-center'>
                    {mentions.map((mention) => (
                        <div key={mention.id} className='bg-white shadow-md rounded-lg border-[1px] w-[500px] flex flex-col'>
                            <div className='flex items-center p-3 space-x-5'>
                                <img
                                    src={mention.icon || defaultIcon} // Use inline SVG as fallback
                                    alt="icon-mention"
                                    className='w-[30px]'
                                    onError={(e) => { e.target.src = defaultIcon; }} // Fallback to inline SVG on error
                                />
                                <h1 className='font-bold text-md'>{mention.nom}</h1>
                            </div>
                            <Divider />
                            <div className='flex flex-col p-3 text-xs font-semibold flex-grow'>
                                <p>Vous êtes inscris en {mention.niveau}</p>
                                <Divider />
                                <p>Parcours {mention.parcours}</p>
                                <Divider />
                                <p>Matricule</p>
                                <div className='flex p-5 justify-between items-center mt-auto'>
                                    <p>{mention.matricule}</p>
                                    <Link
                                        to={`/etudiant/cours/${mention.id}`}
                                        className='flex items-center justify-center space-x-1 text-white text-sm bg-[#39B54A] px-2 py-1 rounded-lg hover:scale-105 duration-500 hover:bg-[#257630]'
                                    >
                                        <FaRegEye />
                                        <p>Voir les cours</p>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </Layout>
    );
};

export default Mention;