import React, { useState, useEffect, useRef } from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { FaFileAudio, FaFileVideo, FaDownload, FaReply, FaEye, FaLink } from 'react-icons/fa6';
import { useParams } from 'react-router-dom';
import { getStudentMentions } from '../../Services/authService';
import { Avatar } from 'primereact/avatar';
import { Toast } from 'primereact/toast';
import { MdErrorOutline } from 'react-icons/md';
import { Dialog } from 'primereact/dialog';
import { Button } from 'primereact/button';
import { ProgressSpinner } from 'primereact/progressspinner';
import 'react-quill/dist/quill.snow.css';

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
    const [cours, setCours] = useState(null);
    const [selectedSupport, setSelectedSupport] = useState(null);
    const [showPreview, setShowPreview] = useState(false);
    const toast = useRef(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getStudentMentions();
                setMentions(data);
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
                setGlobalFilterValue('');
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    useEffect(() => {
        if (!mentions.length || !mentionId || !semestreId || !coursId) {
            setCours(null);
            return;
        }

        const mention = mentions.find((m) => m.id == mentionId);
        const semestre = mention?.semestres.find((s) => s.id == semestreId);
        let foundCours = null;

        if (semestre) {
            for (const ue of semestre.ues) {
                const c = ue.cours.find((c) => c.id == coursId);
                if (c) {
                    foundCours = c;
                    break;
                }
            }
        }

        setCours(foundCours);
    }, [mentions, mentionId, semestreId, coursId]);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const getProxyUrl = (support, isPreview = false) => {
        try {
            if (!support || !support.url) {
                return null;
            }
            if (support.type === 'lien') {
                return support.url;
            }
            const filename = support.url.split('/').pop().split('?')[0];
            const staticUrl = `http://localhost:8000/uploads/supports/${encodeURIComponent(filename)}${isPreview ? '?disposition=inline' : ''}`;
            return staticUrl;
        } catch (error) {
            return null;
        }
    };

    const handleDownload = (support, e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!support || !support.url) {
            showToast('warn', 'Attention', 'Aucun fichier disponible');
            return;
        }
        try {
            if (support.type === 'lien') {
                window.open(support.url, '_blank');
                showToast('info', 'Info', 'Lien ouvert dans un nouvel onglet');
                return;
            }
            const staticUrl = getProxyUrl(support);
            if (!staticUrl) {
                showToast('error', 'Erreur', 'URL de téléchargement invalide');
                return;
            }
            const link = document.createElement('a');
            link.href = staticUrl;
            link.download = support.url.split('/').pop().split('?')[0];
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast('success', 'Succès', 'Téléchargement commencé');
        } catch (error) {
            showToast('error', 'Erreur', 'Échec du téléchargement');
        }
    };

    const handlePreview = (support) => {
        if (!support || !support.url) {
            showToast('error', 'Erreur', 'Aucun fichier disponible pour la visualisation');
            return;
        }

        if (support.type === 'lien') {
            window.open(support.url, '_blank');
            return;
        }

        setSelectedSupport(support);
        setShowPreview(true);
    };

    const handleAddComment = () => {
        if (!newComment.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire un commentaire');
            return;
        }

        const newCommentObj = {
            id: comments.length + 1,
            author: 'Étudiant',
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
        if (toast.current) {
            toast.current.show({
                severity,
                summary,
                detail,
                life: 3000,
            });
        }
    };

    const renderHeader = (title) => {
        return (
            <div className="flex justify-between items-center">
                <h2 className="text-lg font-semibold">{title}</h2>
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
            <div className="flex gap-2" onClick={(e) => e.stopPropagation()}>
                <button
                    className="p-button p-button-rounded p-2 p-button-info"
                    onClick={() => handlePreview(rowData)}
                    disabled={!rowData.url}
                    title={rowData.type === 'lien' ? 'Ouvrir le lien' : 'Visualiser'}
                    type="button"
                >
                    {rowData.type === 'lien' ? <FaLink /> : <FaEye />}
                </button>
                {rowData.type !== 'lien' && (
                    <button
                        className="p-button p-button-rounded p-2 p-button-secondary"
                        onClick={(e) => handleDownload(rowData, e)}
                        disabled={!rowData.url}
                        title="Télécharger"
                        type="button"
                    >
                        <FaDownload />
                    </button>
                )}
            </div>
        );
    };

    const renderPreviewContent = (support) => {
        const staticUrl = getProxyUrl(support, true);
        if (!staticUrl) {
            showToast('error', 'Erreur', 'URL du fichier invalide');
            return <p>URL invalide</p>;
        }

        const videoMimeTypes = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'ogg': 'video/ogg'
        };
        const audioMimeTypes = {
            'mp3': 'audio/mpeg',
            'wav': 'audio/wav',
            'ogg': 'audio/ogg'
        };

        const extension = support.url.split('.').pop().toLowerCase();
        const mimeType = support.mimeType || (
            support.type === 'video' ? videoMimeTypes[extension] :
                support.type === 'audio' ? audioMimeTypes[extension] : null
        );

        switch (support.type) {
            case 'document':
                return (
                    <div className="w-full h-full overflow-auto">
                        <iframe
                            src={staticUrl}
                            title={support.titre}
                            className="w-full h-full border-none"
                            onError={() => showToast('error', 'Erreur', 'Impossible de charger le document')}
                        />
                    </div>
                );

            case 'video':
                return (
                    <div className="w-full h-full">
                        <video
                            controls
                            className="w-full h-full object-contain"
                            autoPlay={false}
                        >
                            <source src={staticUrl} type={mimeType || 'video/mp4'} />
                            Votre navigateur ne supporte pas cette vidéo
                        </video>
                    </div>
                );

            case 'audio':
                return (
                    <div className="w-full h-full flex items-center justify-center p-4">
                        <audio
                            controls
                            className="w-full max-w-md"
                            autoPlay={false}
                        >
                            <source src={staticUrl} type={mimeType || 'audio/mpeg'} />
                            Votre navigateur ne supporte pas cet audio
                        </audio>
                    </div>
                );

            default:
                showToast('error', 'Erreur', 'Type de fichier non supporté');
                return <p>Type de fichier non supporté</p>;
        }
    };

    if (loading) {
        return (
            <Layout>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
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
            <Dialog
                header={selectedSupport?.titre || 'Visualisation'}
                visible={showPreview}
                maximized={true}
                onHide={() => {
                    setShowPreview(false);
                    setSelectedSupport(null);
                }}
                maximizable
                style={{ height: '100vh' }}
                contentStyle={{ padding: 0 }}
            >
                {selectedSupport && renderPreviewContent(selectedSupport)}
            </Dialog>
            <div
                className='w-full text-gray-800 custom-scrollbar'
                style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}
            >
                <h1 className='text-3xl font-normal p-3'>
                    {cours ? `Détails du cours - ${cours.titre}` : 'Détails du cours'}
                </h1>

                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>
                        {cours?.titre || 'Titre non disponible'}
                    </h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <div className='ql-snow'>
                                <div
                                    className='ql-editor'
                                    dangerouslySetInnerHTML={{
                                        __html: cours?.description || '<p>Aucune description disponible</p>'
                                    }}
                                    style={{ minHeight: '100px', padding: '0' }}
                                />
                            </div>
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
                            header={() => renderHeader('Support Document')}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column
                                header="Fichier"
                                body={(row) => (
                                    row.url ? (
                                        <span className="text-blue-500 cursor-pointer" onClick={() => handlePreview(row)}>
                                            {row.titre}
                                        </span>
                                    ) : (
                                        'Non disponible'
                                    )
                                )}
                            />
                            <Column body={actionBodyTemplate} style={{ width: '150px' }} />
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
                            header={() => renderHeader('Support Audio')}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column
                                header="Fichier"
                                body={(row) => (
                                    row.url ? (
                                        <span className="text-blue-500 cursor-pointer" onClick={() => handlePreview(row)}>
                                            {row.titre}
                                        </span>
                                    ) : (
                                        'Non disponible'
                                    )
                                )}
                            />
                            <Column body={actionBodyTemplate} style={{ width: '150px' }} />
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
                            header={() => renderHeader('Support Vidéo')}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column
                                header="Fichier"
                                body={(row) => (
                                    row.url ? (
                                        <span className="text-blue-500 cursor-pointer" onClick={() => handlePreview(row)}>
                                            {row.titre}
                                        </span>
                                    ) : (
                                        'Non disponible'
                                    )
                                )}
                            />
                            <Column body={actionBodyTemplate} style={{ width: '150px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Liens */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center'>
                        <FaLink className='text-2xl mr-2' />
                        <h1>Support Lien</h1>
                    </div>
                    <div>
                        <DataTable
                            value={cours?.supports?.filter(s => s.type === 'lien') || []}
                            paginator
                            rows={5}
                            dataKey="id"
                            globalFilter={globalFilterValue}
                            header={() => renderHeader('Support Lien')}
                            emptyMessage="Aucune donnée trouvée"
                        >
                            <Column field="titre" header="Titre" sortable />
                            <Column
                                header="Lien"
                                body={(row) => (
                                    row.url ? (
                                        <span className="text-blue-500 cursor-pointer" onClick={() => handlePreview(row)}>
                                            {row.titre}
                                        </span>
                                    ) : (
                                        'Non disponible'
                                    )
                                )}
                            />
                            <Column body={actionBodyTemplate} style={{ width: '150px' }} />
                        </DataTable>
                    </div>
                </div>

                {/* Section Commentaires */}
                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>
                        <h1>Discussion sur le cours</h1>
                    </div>
                    <div className='p-5'>
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
                                    type="button"
                                />
                            </div>
                        </div>
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
                                            {comment.author === 'Enseignant' && (
                                                <Button
                                                    label="Répondre"
                                                    icon={<FaReply />}
                                                    className='p-button-text p-button-sm mt-2'
                                                    onClick={() => setReplyingTo(replyingTo === comment.id ? null : comment.id)}
                                                    type="button"
                                                />
                                            )}
                                        </div>
                                    </div>
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
                                                    type="button"
                                                />
                                            </div>
                                        </div>
                                    )}
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
