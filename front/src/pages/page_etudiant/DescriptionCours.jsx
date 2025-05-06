import React, { useState, useEffect } from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { FaFileAudio, FaFileVideo, FaDownload, FaReply } from 'react-icons/fa6';
import { useParams } from 'react-router-dom';
import { getStudentMentions } from '../../Services/authService';
import { Avatar } from 'primereact/avatar';
import { Toast } from 'primereact/toast';
import { useRef } from 'react';

const DescriptionCours = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentions, setMentions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [comments, setComments] = useState([]);
    const [newComment, setNewComment] = useState('');
    const [replyingTo, setReplyingTo] = useState(null);
    const [replyContent, setReplyContent] = useState('');
    const toast = useRef(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getStudentMentions();
                setMentions(data);
                // Simulation de chargement des commentaires
                setComments([
                    {
                        id: 1,
                        author: 'Enseignant',
                        avatar: 'EN',
                        content: 'N\'oubliez pas de consulter les ressources supplémentaires pour ce cours.',
                        date: '2023-05-20',
                        replies: []
                    },
                    {
                        id: 2,
                        author: 'Étudiant 1',
                        avatar: 'E1',
                        content: 'Je ne comprends pas bien le chapitre 4, pourriez-vous expliquer ?',
                        date: '2023-05-21',
                        replies: [
                            {
                                id: 101,
                                author: 'Enseignant',
                                avatar: 'EN',
                                content: 'Bien sûr, nous reviendrons sur ce point lors du prochain cours.',
                                date: '2023-05-22'
                            }
                        ]
                    }
                ]);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    // Trouver le cours dans la structure
    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestre = mention?.semestres.find((s) => s.id === semestreId);
    let cours = null;

    if (semestre) {
        for (const ue of semestre.ues) {
            const foundCours = ue.cours.find((c) => c.id === parseInt(coursId));
            if (foundCours) {
                cours = foundCours;
                break;
            }
        }
    }

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleDownload = (url) => {
        if (!url) {
            showToast('warn', 'Attention', 'Aucun fichier disponible');
            return;
        }
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        showToast('success', 'Succès', 'Téléchargement commencé');
    };

    const handleAddComment = () => {
        if (!newComment.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire un commentaire');
            return;
        }

        const newCommentObj = {
            id: comments.length + 1,
            author: 'Étudiant', // Dans une vraie app, utiliser le nom réel de l'étudiant
            avatar: 'EU',
            content: newComment,
            date: new Date().toISOString().split('T')[0],
            replies: []
        };

        setComments([...comments, newCommentObj]);
        setNewComment('');
        showToast('success', 'Succès', 'Commentaire ajouté');
    };

    const handleReply = (commentId) => {
        if (!replyContent.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire une réponse');
            return;
        }

        const updatedComments = comments.map(comment => {
            if (comment.id === commentId) {
                const newReply = {
                    id: comment.replies.length + 1,
                    author: 'Étudiant',
                    avatar: 'EU',
                    content: replyContent,
                    date: new Date().toISOString().split('T')[0]
                };
                return {
                    ...comment,
                    replies: [...comment.replies, newReply]
                };
            }
            return comment;
        });

        setComments(updatedComments);
        setReplyingTo(null);
        setReplyContent('');
        showToast('success', 'Succès', 'Réponse ajoutée');
    };

    const showToast = (severity, summary, detail) => {
        toast.current.show({
            severity,
            summary,
            detail,
            life: 3000,
        });
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-end items-center">
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={globalFilterValue}
                        onChange={onGlobalFilterChange}
                        placeholder="Rechercher..."
                        className='custom-input'
                    />
                </IconField>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <Button
                icon={<FaDownload />}
                rounded
                severity="secondary"
                onClick={() => handleDownload(rowData.url)}
                disabled={!rowData.url}
                tooltip="Télécharger"
                tooltipOptions={{ position: 'top' }}
            />
        );
    };

    if (loading) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex items-center justify-center">
                    <div className="spinner-container">
                        <div className="spinner-outer">
                            <div className="spinner-inner"></div>
                        </div>
                    </div>
                </div>
            </Layout>
        );
    }

    if (error) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className='text-xl font-bold'>Erreur lors du chargement des données</p>
                    <p className='text-lg font-semibold'>{error}</p>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <Toast ref={toast} position="bottom-right" />
            <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-3xl font-normal p-3'>
                    {cours ? `Détails du cours - ${cours.titre}` : 'Détails du cours'}
                </h1>

                {/* Section Description */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>
                        {cours?.titre || 'Titre non disponible'}
                    </h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <p className='font-semibold leading-relaxed text-justify'>
                                {cours?.description || 'Aucune description disponible'}
                            </p>
                        </div>
                    </div>
                </div>

                {/* Section Documents */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <IoIosDocument className='text-2xl mr-2' />
                        <h1>Support Document</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'document') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Audio */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileAudio className='text-2xl mr-2' />
                        <h1>Support Audio</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'audio') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Vidéo */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaFileVideo className='text-2xl mr-2' />
                        <h1>Support Vidéo</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'video') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={renderHeader()}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column header="Fichier" body={(row) => row.url ? 'Disponible' : 'Non disponible'} />
                            <Column body={actionBodyTemplate} style={{ width: '100px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Commentaires (nouvelle section) */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>
                        <h1>Discussion sur le cours</h1>
                    </div>
                    <div className='p-5'>
                        {/* Formulaire pour ajouter un nouveau commentaire */}
                        <div className='mb-6'>
                            <label htmlFor="newComment" className='block mb-2 font-medium'>Ajouter un commentaire :</label>
                            <div className='flex gap-2'>
                                <InputText
                                    id="newComment"
                                    value={newComment}
                                    onChange={(e) => setNewComment(e.target.value)}
                                    placeholder="Votre question ou commentaire..."
                                    className='flex-grow'
                                />
                                <Button
                                    label="Envoyer"
                                    icon="pi pi-send"
                                    onClick={handleAddComment}
                                    disabled={!newComment.trim()}
                                />
                            </div>
                        </div>

                        {/* Liste des commentaires */}
                        <div className='space-y-6'>
                            {comments.map((comment) => (
                                <div key={comment.id} className='border-b pb-4'>
                                    <div className='flex items-start gap-3'>
                                        <Avatar
                                            label={comment.avatar}
                                            shape="circle"
                                            className={`${comment.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`}
                                        />
                                        <div className='flex-grow'>
                                            <div className='flex justify-between items-center'>
                                                <span className='font-semibold'>{comment.author}</span>
                                                <span className='text-sm text-gray-500'>{comment.date}</span>
                                            </div>
                                            <p className='mt-1'>{comment.content}</p>

                                            {/* Bouton Répondre seulement pour les commentaires de l'enseignant */}
                                            {comment.author === 'Enseignant' && (
                                                <Button
                                                    label="Répondre"
                                                    icon={<FaReply />}
                                                    className='p-button-text p-button-sm mt-2'
                                                    onClick={() => setReplyingTo(replyingTo === comment.id ? null : comment.id)}
                                                />
                                            )}
                                        </div>
                                    </div>

                                    {/* Formulaire de réponse */}
                                    {replyingTo === comment.id && (
                                        <div className='ml-12 mt-3'>
                                            <div className='flex gap-2'>
                                                <InputText
                                                    value={replyContent}
                                                    onChange={(e) => setReplyContent(e.target.value)}
                                                    placeholder="Votre réponse..."
                                                    className='flex-grow'
                                                />
                                                <Button
                                                    label="Envoyer"
                                                    icon="pi pi-send"
                                                    onClick={() => handleReply(comment.id)}
                                                    disabled={!replyContent.trim()}
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {/* Réponses */}
                                    {comment.replies.length > 0 && (
                                        <div className='ml-12 mt-4 space-y-4'>
                                            {comment.replies.map((reply) => (
                                                <div key={reply.id} className='flex items-start gap-3'>
                                                    <Avatar
                                                        label={reply.avatar}
                                                        shape="circle"
                                                        className={`${reply.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`}
                                                    />
                                                    <div className='flex-grow'>
                                                        <div className='flex justify-between items-center'>
                                                            <span className='font-semibold'>{reply.author}</span>
                                                            <span className='text-sm text-gray-500'>{reply.date}</span>
                                                        </div>
                                                        <p className='mt-1'>{reply.content}</p>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default DescriptionCours;