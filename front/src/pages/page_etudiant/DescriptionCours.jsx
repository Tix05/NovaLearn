import React, { useState, useEffect, useRef } from 'react';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { FaFileAudio, FaFileVideo, FaDownload, FaReply, FaEye, FaLink, FaTrash } from 'react-icons/fa6';
import { FaEdit } from 'react-icons/fa';
import { useParams } from 'react-router-dom';
import { getStudentCoursDetails, addComment, updateComment, deleteComment } from '../../Services/authService';
import { Avatar } from 'primereact/avatar';
import { Toast } from 'primereact/toast';
import { MdErrorOutline } from 'react-icons/md';
import { Dialog } from 'primereact/dialog';
import { Button } from 'primereact/button';
import { ProgressSpinner } from 'primereact/progressspinner';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';
import 'react-quill/dist/quill.snow.css';

const DescriptionCours = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [cours, setCours] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [newComment, setNewComment] = useState('');
    const [replyingTo, setReplyingTo] = useState(null);
    const [replyContent, setReplyContent] = useState('');
    const [editingComment, setEditingComment] = useState(null);
    const [editContent, setEditContent] = useState('');
    const [selectedSupport, setSelectedSupport] = useState(null);
    const [showPreview, setShowPreview] = useState(false);
    const toast = useRef(null);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const data = await getStudentCoursDetails(mentionId, semestreId, coursId);
                setCours(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };
        fetchData();
    }, [mentionId, semestreId, coursId]);

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

    const handleAddComment = async () => {
        if (!newComment.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire un commentaire');
            return;
        }

        try {
            const response = await addComment(coursId, newComment);
            setCours((prev) => ({
                ...prev,
                commentaires: [
                    ...prev.commentaires,
                    {
                        id: response.id,
                        author: response.author,
                        avatar: response.avatar,
                        content: response.content,
                        date: response.date,
                        isOwner: response.isOwner,
                        replies: []
                    }
                ]
            }));
            setNewComment('');
            showToast('success', 'Succès', 'Commentaire ajouté');
        } catch (error) {
            showToast('error', 'Erreur', error.message);
        }
    };

    const handleReply = async (commentId) => {
        if (!replyContent.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire une réponse');
            return;
        }

        try {
            const response = await addComment(coursId, replyContent, commentId);
            setCours((prev) => ({
                ...prev,
                commentaires: prev.commentaires.map((comment) =>
                    comment.id === commentId
                        ? {
                            ...comment,
                            replies: [
                                ...comment.replies,
                                {
                                    id: response.id,
                                    author: response.author,
                                    avatar: response.avatar,
                                    content: response.content,
                                    date: response.date,
                                    isOwner: response.isOwner
                                }
                            ]
                        }
                        : comment
                )
            }));
            setReplyingTo(null);
            setReplyContent('');
            showToast('success', 'Succès', 'Réponse ajoutée');
        } catch (error) {
            showToast('error', 'Erreur', error.message);
        }
    };

    const handleEditComment = (comment) => {
        setEditingComment(comment.id);
        setEditContent(comment.content);
    };

    const handleUpdateComment = async (commentId, isReply) => {
        if (!editContent.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire un commentaire');
            return;
        }

        try {
            const response = await updateComment(commentId, editContent);
            setCours((prev) => ({
                ...prev,
                commentaires: prev.commentaires.map((comment) => {
                    if (isReply) {
                        return {
                            ...comment,
                            replies: comment.replies.map((reply) =>
                                reply.id === commentId ? { ...reply, content: response.content, date: response.date } : reply
                            )
                        };
                    }
                    return comment.id === commentId ? { ...comment, content: response.content, date: response.date } : comment;
                })
            }));
            setEditingComment(null);
            setEditContent('');
            showToast('success', 'Succès', 'Commentaire mis à jour');
        } catch (error) {
            showToast('error', 'Erreur', error.message);
        }
    };

    const handleDeleteComment = async (commentId, isReply) => {
        confirmDialog({
            message: 'Voulez-vous vraiment supprimer ce commentaire ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: async () => {
                try {
                    await deleteComment(commentId);
                    setCours((prev) => ({
                        ...prev,
                        commentaires: isReply
                            ? prev.commentaires.map((comment) => ({
                                ...comment,
                                replies: comment.replies.filter((reply) => reply.id !== commentId)
                            }))
                            : prev.commentaires.filter((comment) => comment.id !== commentId)
                    }));
                    showToast('success', 'Succès', 'Commentaire supprimé');
                } catch (error) {
                    showToast('error', 'Erreur', error.message);
                }
            }
        });
    };

    const showToast = (severity, summary, detail) => {
        if (toast.current) {
            toast.current.show({
                severity,
                summary,
                detail,
                life: 3000
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
                        className="custom-input"
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
            mp4: 'video/mp4',
            webm: 'video/webm',
            ogg: 'video/ogg'
        };
        const audioMimeTypes = {
            mp3: 'audio/mpeg',
            wav: 'audio/wav',
            ogg: 'audio/ogg'
        };

        const extension = support.url.split('.').pop().toLowerCase();
        const mimeType =
            support.mimeType ||
            (support.type === 'video'
                ? videoMimeTypes[extension]
                : support.type === 'audio'
                    ? audioMimeTypes[extension]
                    : null);

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
                        <video controls className="w-full h-full object-contain" autoPlay={false}>
                            <source src={staticUrl} type={mimeType || 'video/mp4'} />
                            Votre navigateur ne supporte pas cette vidéo
                        </video>
                    </div>
                );
            case 'audio':
                return (
                    <div className="w-full h-full flex items-center justify-center p-4">
                        <audio controls className="w-full max-w-md" autoPlay={false}>
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
                    <p className="text-xl font-bold">Erreur lors du chargement des données</p>
                    <p className="text-lg font-semibold">{error}</p>
                </div>
            </Layout>
        );
    }

    if (!cours) {
        return (
            <Layout>
                <div className="w-full text-gray-800 custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                    <h1 className="text-3xl font-normal p-3">Cours non trouvé</h1>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <Toast ref={toast} position="bottom-right" />
            <ConfirmDialog />
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
            <div className="w-full text-gray-800 custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className="text-3xl font-normal p-3">Détails du cours - {cours.titre}</h1>

                <div className="flex flex-col shadow-md m-5 border-[1px] rounded-lg">
                    <h1 className="p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg">{cours.titre}</h1>
                    <div className="p-3">
                        <p className="font-semibold text-xl">Description du cours :</p>
                        <Divider />
                        <div className="p-10">
                            <div className="ql-snow">
                                <div
                                    className="ql-editor"
                                    dangerouslySetInnerHTML={{
                                        __html: cours.description || '<p>Aucune description disponible</p>'
                                    }}
                                    style={{ minHeight: '100px', padding: '0' }}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {['document', 'audio', 'video', 'lien'].map((type) => {
                    const supports = cours.supports?.filter((s) => s.type === type) || [];
                    const typeConfig = {
                        document: { icon: <IoIosDocument className="text-2xl mr-2" />, label: 'Document' },
                        audio: { icon: <FaFileAudio className="text-2xl mr-2" />, label: 'Audio' },
                        video: { icon: <FaFileVideo className="text-2xl mr-2" />, label: 'Vidéo' },
                        lien: { icon: <FaLink className="text-2xl mr-2" />, label: 'Lien' }
                    }[type];

                    return (
                        <div key={type} className="flex flex-col shadow-md m-5 border-[1px] rounded-lg">
                            <div className="p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg flex items-center">
                                {typeConfig.icon}
                                <h1>Support {typeConfig.label}</h1>
                            </div>
                            <div>
                                <DataTable
                                    value={supports}
                                    paginator
                                    rows={5}
                                    dataKey="id"
                                    globalFilter={globalFilterValue}
                                    header={() => renderHeader(`Support ${typeConfig.label}`)}
                                    emptyMessage={`Aucun ${typeConfig.label} trouvé`}
                                >
                                    <Column field="titre" header="Titre" sortable />
                                    <Column
                                        header={type === 'lien' ? 'Lien' : 'Fichier'}
                                        body={(row) =>
                                            row.url ? (
                                                <span className="text-blue-500 cursor-pointer" onClick={() => handlePreview(row)}>
                                                    {row.titre}
                                                </span>
                                            ) : (
                                                'Non disponible'
                                            )
                                        }
                                    />
                                    <Column body={actionBodyTemplate} style={{ width: '150px' }} />
                                </DataTable>
                            </div>
                        </div>
                    );
                })}

                <div className="flex flex-col shadow-md m-5 border-[1px] rounded-lg">
                    <div className="p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg">
                        <h1>Discussion sur le cours</h1>
                    </div>
                    <div className="p-5">
                        <div className="mb-6">
                            <label htmlFor="newComment" className="block mb-2 font-medium">
                                Ajouter un commentaire :
                            </label>
                            <div className="flex gap-2">
                                <InputText
                                    id="newComment"
                                    value={newComment}
                                    onChange={(e) => setNewComment(e.target.value)}
                                    placeholder="Votre question ou commentaire..."
                                    className="flex-grow"
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
                        <div className="space-y-6">
                            {cours.commentaires?.map((comment) => (
                                <div key={comment.id} className="border-b pb-4">
                                    <div className="flex items-start gap-3">
                                        <Avatar
                                            image={comment.avatar?.startsWith('http') ? comment.avatar : null}
                                            label={!comment.avatar?.startsWith('http') ? comment.avatar : null}
                                            shape="circle"
                                            className={`${comment.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`}
                                        />
                                        <div className="flex-grow">
                                            {editingComment === comment.id ? (
                                                <div className="flex gap-2">
                                                    <InputText
                                                        value={editContent}
                                                        onChange={(e) => setEditContent(e.target.value)}
                                                        className="flex-grow"
                                                    />
                                                    <Button
                                                        label="Enregistrer"
                                                        icon="pi pi-save"
                                                        onClick={() => handleUpdateComment(comment.id, false)}
                                                        disabled={!editContent.trim()}
                                                        type="button"
                                                    />
                                                    <Button
                                                        label="Annuler"
                                                        icon="pi pi-times"
                                                        className="p-button-text p-button-sm"
                                                        onClick={() => setEditingComment(null)}
                                                        type="button"
                                                    />
                                                </div>
                                            ) : (
                                                <>
                                                    <div className="flex justify-between items-center">
                                                        <div className="flex items-center gap-2">
                                                            <span className="font-semibold">{comment.author}</span>
                                                            {comment.isOwner && (
                                                                <>
                                                                    <Button
                                                                        icon={<FaEdit />}
                                                                        className="p-button-text p-button-md p-button-info"
                                                                        onClick={() => handleEditComment(comment)}
                                                                        tooltip="Modifier"
                                                                        tooltipOptions={{ position: 'top' }}
                                                                        type="button"
                                                                    />
                                                                    <Button
                                                                        icon={<FaTrash />}
                                                                        className="p-button-text p-button-md p-button-danger"
                                                                        onClick={() => handleDeleteComment(comment.id, false)}
                                                                        tooltip="Supprimer"
                                                                        tooltipOptions={{ position: 'top' }}
                                                                        type="button"
                                                                    />
                                                                </>
                                                            )}
                                                        </div>
                                                        <span className="text-sm text-gray-500">{comment.date}</span>
                                                    </div>
                                                    <p className="mt-1">{comment.content}</p>
                                                    <Button
                                                        label="Répondre"
                                                        icon={<FaReply />}
                                                        className="p-button-text p-button-sm mt-2"
                                                        onClick={() => setReplyingTo(replyingTo === comment.id ? null : comment.id)}
                                                        type="button"
                                                    />
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    {replyingTo === comment.id && (
                                        <div className="ml-12 mt-3">
                                            <div className="flex gap-2">
                                                <InputText
                                                    value={replyContent}
                                                    onChange={(e) => setReplyContent(e.target.value)}
                                                    placeholder="Votre réponse..."
                                                    className="flex-grow"
                                                />
                                                <Button
                                                    label="Envoyer"
                                                    icon="pi pi-send"
                                                    onClick={() => handleReply(comment.id)}
                                                    disabled={!replyContent.trim()}
                                                    type="button"
                                                />
                                                <Button
                                                    label="Annuler"
                                                    icon="pi pi-times"
                                                    className="p-button-text p-button-sm"
                                                    onClick={() => setReplyingTo(null)}
                                                    type="button"
                                                />
                                            </div>
                                        </div>
                                    )}
                                    {comment.replies.length > 0 && (
                                        <div className="ml-12 mt-4 space-y-4">
                                            {comment.replies.map((reply) => (
                                                <div key={reply.id} className="flex items-start gap-3">
                                                    <Avatar
                                                        image={reply.avatar?.startsWith('http') ? reply.avatar : null}
                                                        label={!reply.avatar?.startsWith('http') ? reply.avatar : null}
                                                        shape="circle"
                                                        className={`${reply.author === 'Enseignant' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`}
                                                    />
                                                    <div className="flex-grow">
                                                        {editingComment === reply.id ? (
                                                            <div className="flex gap-2">
                                                                <InputText
                                                                    value={editContent}
                                                                    onChange={(e) => setEditContent(e.target.value)}
                                                                    className="flex-grow"
                                                                />
                                                                <Button
                                                                    label="Enregistrer"
                                                                    icon="pi pi-save"
                                                                    onClick={() => handleUpdateComment(reply.id, true)}
                                                                    disabled={!editContent.trim()}
                                                                    type="button"
                                                                />
                                                                <Button
                                                                    label="Annuler"
                                                                    icon="pi pi-times"
                                                                    className="p-button-text p-button-sm"
                                                                    onClick={() => setEditingComment(null)}
                                                                    type="button"
                                                                />
                                                            </div>
                                                        ) : (
                                                            <>
                                                                <div className="flex justify-between items-center">
                                                                    <div className="flex items-center gap-2">
                                                                        <span className="font-semibold">{reply.author}</span>
                                                                        {reply.isOwner && (
                                                                            <>
                                                                                <Button
                                                                                    icon={<FaEdit />}
                                                                                    className="p-button-text p-button-md p-button-info"
                                                                                    onClick={() => handleEditComment(reply)}
                                                                                    tooltip="Modifier"
                                                                                    tooltipOptions={{ position: 'top' }}
                                                                                    type="button"
                                                                                />
                                                                                <Button
                                                                                    icon={<FaTrash />}
                                                                                    className="p-button-text p-button-md p-button-danger"
                                                                                    onClick={() => handleDeleteComment(reply.id, true)}
                                                                                    tooltip="Supprimer"
                                                                                    tooltipOptions={{ position: 'top' }}
                                                                                    type="button"
                                                                                />
                                                                            </>
                                                                        )}
                                                                    </div>
                                                                    <span className="text-sm text-gray-500">{reply.date}</span>
                                                                </div>
                                                                <p className="mt-1">{reply.content}</p>
                                                            </>
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
        </Layout>
    );
};

export default DescriptionCours;