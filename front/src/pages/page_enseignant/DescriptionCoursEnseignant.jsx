import React, { useState, useRef, useEffect } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { FaFileAudio, FaFileVideo, FaTrash, FaDownload, FaReply, FaEdit, FaTimes } from 'react-icons/fa';
import { useParams } from 'react-router-dom';
import { mentions } from '../../../public/constants/data2';
import { Toast } from 'primereact/toast';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';
import { Avatar } from 'primereact/avatar';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';

const DescriptionCoursEnseignant = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [description, setDescription] = useState('');
    const [isSaving, setIsSaving] = useState(false);
    const [comments, setComments] = useState([]);
    const [newComment, setNewComment] = useState('');
    const [replyingTo, setReplyingTo] = useState(null);
    const [replyContent, setReplyContent] = useState('');
    const [editingComment, setEditingComment] = useState(null);
    const [editContent, setEditContent] = useState('');
    const toast = useRef(null);

    // Trouver le cours dans la structure avec UE
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

    useEffect(() => {
        if (cours?.description) {
            setDescription(cours.description);
        }
        // Charger les commentaires existants (simulation)
        setComments([
            {
                id: 1,
                author: 'Étudiant 1',
                avatar: 'E1',
                content: 'Ce cours est très intéressant, mais j\'ai une question sur le chapitre 3.',
                date: '2023-05-15',
                replies: [
                    {
                        id: 101,
                        author: 'Enseignant',
                        avatar: 'EN',
                        content: 'Quelle est votre question précisément sur le chapitre 3 ?',
                        date: '2023-05-16'
                    }
                ]
            },
            {
                id: 2,
                author: 'Étudiant 2',
                avatar: 'E2',
                content: 'Quand sera disponible le support du cours ?',
                date: '2023-05-17',
                replies: []
            },
            {
                id: 3,
                author: 'Enseignant',
                avatar: 'EN',
                content: 'N\'oubliez pas de consulter les ressources supplémentaires dans la section documents.',
                date: '2023-05-18',
                replies: []
            }
        ]);
    }, [cours]);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const handleDownload = (filename) => {
        showToast('success', 'Succès', 'Téléchargement commencé');
    };

    const handleDelete = (support) => {
        showToast('success', 'Succès', 'Support supprimé avec succès');
    };

    const handleSaveDescription = () => {
        setIsSaving(true);
        setTimeout(() => {
            setIsSaving(false);
            showToast('success', 'Succès', 'Description enregistrée');
        }, 500);
    };

    const handleAddComment = () => {
        if (!newComment.trim()) return;

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
        if (!replyContent.trim()) return;

        const updatedComments = comments.map(comment => {
            if (comment.id === commentId) {
                const newReply = {
                    id: comment.replies.length + 1,
                    author: 'Enseignant',
                    avatar: 'EN',
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

    const handleEditComment = (commentId, content, isReply = false) => {
        setEditingComment({ id: commentId, isReply });
        setEditContent(content);
    };

    const handleUpdateComment = () => {
        if (!editContent.trim()) return;

        if (editingComment.isReply) {
            // Mettre à jour une réponse
            const updatedComments = comments.map(comment => {
                const updatedReplies = comment.replies.map(reply => {
                    if (reply.id === editingComment.id) {
                        return { ...reply, content: editContent };
                    }
                    return reply;
                });
                return { ...comment, replies: updatedReplies };
            });
            setComments(updatedComments);
        } else {
            // Mettre à jour un commentaire principal
            const updatedComments = comments.map(comment => {
                if (comment.id === editingComment.id) {
                    return { ...comment, content: editContent };
                }
                return comment;
            });
            setComments(updatedComments);
        }

        setEditingComment(null);
        setEditContent('');
        showToast('success', 'Succès', 'Commentaire mis à jour');
    };

    const confirmDelete = (commentId, isReply = false) => {
        confirmDialog({
            message: 'Voulez-vous vraiment supprimer ce commentaire ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: () => handleDeleteComment(commentId, isReply)
        });
    };

    const handleDeleteComment = (commentId, isReply = false) => {
        if (isReply) {
            // Supprimer une réponse
            const updatedComments = comments.map(comment => {
                const filteredReplies = comment.replies.filter(reply => reply.id !== commentId);
                return { ...comment, replies: filteredReplies };
            });
            setComments(updatedComments);
        } else {
            // Supprimer un commentaire principal
            const filteredComments = comments.filter(comment => comment.id !== commentId);
            setComments(filteredComments);
        }

        showToast('success', 'Succès', 'Commentaire supprimé');
    };

    const showToast = (severity, summary, detail) => {
        toast.current.show({
            severity,
            summary,
            detail,
            life: 3000,
        });
    };

    const renderHeader = (type) => {
        return (
            <div className="flex justify-between items-center">
                <span className="text-xl font-bold">Supports {type}</span>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={globalFilterValue}
                        onChange={onGlobalFilterChange}
                        placeholder="Rechercher..."
                    />
                </IconField>
            </div>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className="flex gap-2">
                <Button
                    icon={<FaDownload />}
                    rounded
                    severity="info"
                    onClick={() => handleDownload(rowData.nom)}
                    tooltip="Télécharger"
                    tooltipOptions={{ position: 'top' }}
                />
                <Button
                    icon={<FaTrash />}
                    rounded
                    severity="danger"
                    onClick={() => handleDelete(rowData)}
                    tooltip="Supprimer"
                    tooltipOptions={{ position: 'top' }}
                />
            </div>
        );
    };

    if (!cours) {
        return (
            <LayoutEnseignant>
                <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                    <h1 className='text-3xl font-normal p-3'>Cours non trouvé</h1>
                </div>
            </LayoutEnseignant>
        );
    }

    return (
        <LayoutEnseignant>
            <Toast ref={toast} position='bottom-right' />
            <ConfirmDialog />
            <div className='w-full text-gray-800 custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-3xl font-normal p-3'>Détails du cours {cours.titre}</h1>

                <div className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                    <h1 className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg'>{cours.titre}</h1>
                    <div className='p-3'>
                        <p className='font-semibold text-xl'>Description du cours :</p>
                        <Divider />
                        <div className='p-10'>
                            <ReactQuill
                                value={description}
                                onChange={setDescription}
                                modules={{
                                    toolbar: [
                                        ['bold', 'italic', 'underline', 'strike'],
                                        ['blockquote'],
                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                        [{ 'indent': '-1' }, { 'indent': '+1' }],
                                        [{ 'header': [1, 2, 3, false] }],
                                        [{ 'color': [] }, { 'background': [] }],
                                        [{ 'align': [] }],
                                        ['clean']
                                    ]
                                }}
                                style={{ height: '250px', marginBottom: '50px' }}
                            />
                        </div>
                        <div className='flex justify-end'>
                            <Button
                                label={isSaving ? "Enregistrement..." : "Enregistrer"}
                                icon="pi pi-save"
                                onClick={handleSaveDescription}
                                disabled={isSaving}
                            />
                        </div>
                    </div>
                </div>

                {['document', 'audio', 'video'].map((type) => {
                    const supports = cours.supports?.filter((s) => s.type === type) || [];
                    const typeConfig = {
                        document: { icon: <IoIosDocument className='text-2xl' />, label: 'Document' },
                        audio: { icon: <FaFileAudio className='text-2xl' />, label: 'Audio' },
                        video: { icon: <FaFileVideo className='text-2xl' />, label: 'Vidéo' }
                    }[type];

                    return (
                        <div key={type} className='flex flex-col shadow-md m-5 border-[1px] rounded-lg'>
                            <div className='p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center gap-2'>
                                {typeConfig.icon}
                                <h1>Support {typeConfig.label}</h1>
                            </div>
                            <div className='p-3'>
                                <DataTable
                                    value={supports}
                                    paginator
                                    rows={5}
                                    dataKey="id"
                                    globalFilter={globalFilterValue}
                                    header={renderHeader(typeConfig.label)}
                                    emptyMessage={`Aucun ${typeConfig.label} trouvé`}
                                >
                                    <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                                    <Column field="nom" header="Fichier" sortable style={{ minWidth: '5rem' }} />
                                    <Column field="date" header="Date d'ajout" sortable style={{ minWidth: '8rem' }} />
                                    <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                                </DataTable>
                            </div>
                        </div>
                    );
                })}

                {/* Section Commentaires */}
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
                                    placeholder="Votre commentaire..."
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
                                        <Avatar label={comment.avatar} shape="circle" className={`${comment.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`} />
                                        <div className='flex-grow'>
                                            <div className='flex justify-between items-center'>
                                                <div className='flex items-center gap-2'>
                                                    <span className='font-semibold'>{comment.author}</span>
                                                    {comment.author === 'Enseignant' && (
                                                        <div className='flex gap-1'>
                                                            <Button
                                                                icon={<FaEdit />}
                                                                className='p-button-text p-button-sm'
                                                                onClick={() => handleEditComment(comment.id, comment.content, false)}
                                                                tooltip="Modifier"
                                                                tooltipOptions={{ position: 'top' }}
                                                            />
                                                            <Button
                                                                icon={<FaTimes />}
                                                                className='p-button-text p-button-sm p-button-danger'
                                                                onClick={() => confirmDelete(comment.id, false)}
                                                                tooltip="Supprimer"
                                                                tooltipOptions={{ position: 'top' }}
                                                            />
                                                        </div>
                                                    )}
                                                </div>
                                                <span className='text-sm text-gray-500'>{comment.date}</span>
                                            </div>

                                            {editingComment?.id === comment.id && !editingComment.isReply ? (
                                                <div className='mt-2 flex gap-2'>
                                                    <InputText
                                                        value={editContent}
                                                        onChange={(e) => setEditContent(e.target.value)}
                                                        className='flex-grow'
                                                    />
                                                    <Button
                                                        icon="pi pi-check"
                                                        className='p-button-success'
                                                        onClick={handleUpdateComment}
                                                        disabled={!editContent.trim()}
                                                    />
                                                    <Button
                                                        icon="pi pi-times"
                                                        className='p-button-danger'
                                                        onClick={() => setEditingComment(null)}
                                                    />
                                                </div>
                                            ) : (
                                                <p className='mt-1'>{comment.content}</p>
                                            )}

                                            <Button
                                                label="Répondre"
                                                icon={<FaReply />}
                                                className='p-button-text p-button-sm mt-2'
                                                onClick={() => setReplyingTo(replyingTo === comment.id ? null : comment.id)}
                                                disabled={comment.author === 'Enseignant'}
                                            />
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
                                                    <Avatar label={reply.avatar} shape="circle" className={`${reply.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`} />
                                                    <div className='flex-grow'>
                                                        <div className='flex justify-between items-center'>
                                                            <div className='flex items-center gap-2'>
                                                                <span className='font-semibold'>{reply.author}</span>
                                                                {reply.author === 'Enseignant' && (
                                                                    <div className='flex gap-1'>
                                                                        <Button
                                                                            icon={<FaEdit />}
                                                                            className='p-button-text p-button-sm'
                                                                            onClick={() => handleEditComment(reply.id, reply.content, true)}
                                                                            tooltip="Modifier"
                                                                            tooltipOptions={{ position: 'top' }}
                                                                        />
                                                                        <Button
                                                                            icon={<FaTimes />}
                                                                            className='p-button-text p-button-sm p-button-danger'
                                                                            onClick={() => confirmDelete(reply.id, true)}
                                                                            tooltip="Supprimer"
                                                                            tooltipOptions={{ position: 'top' }}
                                                                        />
                                                                    </div>
                                                                )}
                                                            </div>
                                                            <span className='text-sm text-gray-500'>{reply.date}</span>
                                                        </div>

                                                        {editingComment?.id === reply.id && editingComment.isReply ? (
                                                            <div className='mt-2 flex gap-2'>
                                                                <InputText
                                                                    value={editContent}
                                                                    onChange={(e) => setEditContent(e.target.value)}
                                                                    className='flex-grow'
                                                                />
                                                                <Button
                                                                    icon="pi pi-check"
                                                                    className='p-button-success'
                                                                    onClick={handleUpdateComment}
                                                                    disabled={!editContent.trim()}
                                                                />
                                                                <Button
                                                                    icon="pi pi-times"
                                                                    className='p-button-danger'
                                                                    onClick={() => setEditingComment(null)}
                                                                />
                                                            </div>
                                                        ) : (
                                                            <p className='mt-1'>{reply.content}</p>
                                                        )}
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
        </LayoutEnseignant>
    );
};

export default DescriptionCoursEnseignant;