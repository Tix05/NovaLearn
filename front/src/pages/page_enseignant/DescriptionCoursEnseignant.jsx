import React, { useState, useEffect, useRef } from 'react';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Divider } from 'primereact/divider';
import { IoIosDocument } from 'react-icons/io';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { Button } from 'primereact/button';
import { FaFileAudio, FaFileVideo, FaTrash, FaDownload, FaReply, FaEye, FaLink } from 'react-icons/fa6';
import { FaEdit } from 'react-icons/fa';
import { useParams } from 'react-router-dom';
import { Toast } from 'primereact/toast';
import ReactQuill from 'react-quill';
import 'react-quill/dist/quill.snow.css';
import { Avatar } from 'primereact/avatar';
import { ConfirmDialog, confirmDialog } from 'primereact/confirmdialog';
import { Dialog } from 'primereact/dialog';
import { getTeacherCoursDetails, addTeacherComment, updateTeacherComment, deleteTeacherComment, deleteTeacherSupport, updateEcDescription } from '../../Services/teacherAuthService';
import { ProgressSpinner } from 'primereact/progressspinner';
import { MdErrorOutline } from 'react-icons/md';

const DescriptionCoursEnseignant = () => {
    const { mentionId, semestreId, coursId } = useParams();
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [description, setDescription] = useState('');
    const [isSaving, setIsSaving] = useState(false);
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
                const data = await getTeacherCoursDetails(mentionId, semestreId, parseInt(coursId));
                setCours(data);
                setDescription(data.description || '');
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
            if (!support?.url) {
                return null;
            }
            if (support.type === 'lien') {
                return support.url;
            }
            const filename = support.url.split('/').pop()?.split('?')[0];
            const staticUrl = `http://localhost:8000/uploads/supports/${encodeURIComponent(filename)}${isPreview ? '?disposition=inline' : ''}`;
            return staticUrl;
        } catch (error) {
            return null;
        }
    };

    const handleDownload = async (support, e) => {
        e.preventDefault();
        e.stopPropagation();
        if (!support?.url) {
            showToast('info', 'Information', 'Aucun fichier disponible');
            return;
        }
        try {
            if (support.type === 'lien') {
                window.open(support.url, '_blank');
                showToast('info', 'Information', 'Lien ouvert dans un nouvel onglet');
                return;
            }
            const staticUrl = getProxyUrl(support);
            if (!staticUrl) {
                throw new Error('URL de téléchargement invalide');
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

    const handlePreview = async (support) => {
        if (!support?.url) {
            showToast('error', 'Erreur', 'Aucun fichier disponible pour la visualisation');
            return;
        }
        if (support.type === 'lien') {
            window.open(support.url, '_blank');
            showToast('info', 'Information', 'Lien ouvert');
            return;
        }
        setSelectedSupport(support);
        setShowPreview(true);
    };

    const handleDeleteSupport = async (support) => {
        confirmDialog({
            message: 'Voulez-vous vraiment supprimer ce support ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: async () => {
                try {
                    await deleteTeacherSupport(support.id);
                    setCours((prev) => ({
                        ...prev,
                        supports: prev.supports.filter((s) => s.id !== support.id),
                    }));
                    showToast('success', 'Succès', 'Support supprimé avec succès');
                } catch (err) {
                    showToast('error', 'Erreur', err.message);
                }
            },
        });
    };

    const handleSaveDescription = async () => {
        const cleanDescription = description.replace(/<[^>]+>/g, '').trim();
        if (!cleanDescription) {
            showToast('warn', 'Attention', 'La description ne peut pas être vide');
            return;
        }
        setIsSaving(true);
        try {
            await updateEcDescription(coursId, description);
            setCours((prev) => ({ ...prev, description }));
            showToast('success', 'Succès', 'Description enregistrée avec succès');
        } catch (err) {
            showToast('error', 'Erreur', err.message);
        } finally {
            setIsSaving(false);
        }
    };

    const handleAddCommentaire = async () => {
        if (!newComment.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire un commentaire');
            return;
        }
        try {
            const response = await addTeacherComment(coursId, newComment);
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
                        replies: [],
                    },
                ],
            }));
            setNewComment('');
            showToast('success', 'Succès', 'Commentaire ajouté avec succès');
        } catch (err) {
            showToast('error', 'Erreur', err.message);
        }
    };

    const handleReply = async (commentId) => {
        if (!replyContent.trim()) {
            showToast('warn', 'Attention', 'Veuillez écrire une réponse');
            return;
        }
        try {
            const response = await addTeacherComment(coursId, replyContent, commentId);
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
                                    isOwner: response.isOwner,
                                },
                            ],
                        }
                        : comment
                ),
            }));
            setReplyingTo(null);
            setReplyContent('');
            showToast('success', 'Succès', 'Réponse ajoutée avec succès');
        } catch (err) {
            showToast('error', 'Erreur', err.message);
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
            const response = await updateTeacherComment(commentId, editContent);
            setCours((prev) => ({
                ...prev,
                commentaires: prev.commentaires.map((comment) => {
                    if (isReply) {
                        return {
                            ...comment,
                            replies: comment.replies.map((reply) =>
                                reply.id === commentId
                                    ? { ...reply, content: response.content, date: response.date }
                                    : reply
                            ),
                        };
                    }
                    return comment.id === commentId
                        ? { ...comment, content: response.content, date: response.date }
                        : comment;
                }),
            }));
            setEditingComment(null);
            setEditContent('');
            showToast('success', 'Succès', 'Commentaire mis à jour avec succès');
        } catch (err) {
            showToast('error', 'Erreur', err.message);
        }
    };

    const handleDeleteComment = (commentId, isReply) => {
        confirmDialog({
            message: 'Voulez-vous vraiment supprimer ce commentaire ?',
            header: 'Confirmation de suppression',
            icon: 'pi pi-exclamation-triangle',
            acceptLabel: 'Oui',
            rejectLabel: 'Non',
            accept: async () => {
                try {
                    await deleteTeacherComment(commentId);
                    setCours((prev) => ({
                        ...prev,
                        commentaires: isReply
                            ? prev.commentaires.map((comment) => ({
                                ...comment,
                                replies: comment.replies.filter((reply) => reply.id !== commentId),
                            }))
                            : prev.commentaires.filter((comment) => comment.id !== commentId),
                    }));
                    showToast('success', 'Succès', 'Commentaire supprimé avec succès');
                } catch (err) {
                    showToast('error', 'Erreur', err.message);
                }
            },
        });
    };

    const showToast = (severity, summary, detail) => {
        toast.current?.show({
            severity,
            summary,
            detail,
            life: 3000,
        });
    };

    const renderHeader = (title) => {
        return (
            <div className="flex justify-between items-center">
                <h2 className="text-lg font-semibold">{title}</h2>
                <IconField iconPosition="left">
                    <i className="pi pi-search" style={{ position: 'absolute', left: '10px', top: '50%', transform: 'translateY(-50%)' }} />
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
                    className="p-button p-button-rounded p-button-info p-2"
                    onClick={() => handlePreview(rowData)}
                    disabled={!rowData.url}
                    title={rowData.type === 'lien' ? 'Ouvrir le lien' : 'Visualiser'}
                    type="button"
                >
                    {rowData.type === 'lien' ? <FaLink /> : <FaEye />}
                </button>
                {rowData.type !== 'lien' && (
                    <button
                        className="p-button p-button-rounded p-button-secondary p-2"
                        onClick={(e) => handleDownload(rowData, e)}
                        disabled={!rowData.url}
                        title="Télécharger"
                        type="button"
                    >
                        <FaDownload />
                    </button>
                )}
                <button
                    className="p-button p-button-rounded p-button-danger p-2"
                    onClick={() => handleDeleteSupport(rowData)}
                    title="Supprimer"
                    type="button"
                >
                    <FaTrash />
                </button>
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
            ogg: 'video/ogg',
        };
        const audioMimeTypes = {
            mp3: 'audio/mpeg',
            wav: 'audio/wav',
            ogg: 'audio/ogg',
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
            <LayoutEnseignant>
                <div className="flex justify-center items-center h-full">
                    <ProgressSpinner />
                </div>
            </LayoutEnseignant>
        );
    }

    if (error) {
        return (
            <LayoutEnseignant>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className="text-xl font-bold">Erreur lors du chargement des données</p>
                    <p className="text-lg font-semibold">{error}</p>
                </div>
            </LayoutEnseignant>
        );
    }

    if (!cours) {
        return (
            <LayoutEnseignant>
                <div className="w-full text-gray-800 custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                    <h1 className="text-3xl font-normal p-3">Cours non trouvé</h1>
                </div>
            </LayoutEnseignant>
        );
    }

    return (
        <LayoutEnseignant>
            <Toast ref={toast} position="bottom-right" />
            <ConfirmDialog />
            <Dialog
                header={selectedSupport?.titre || 'Visualisation'}
                visible={showPreview}
                maximized
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
                <h1 className="text-3xl font-normal p-3">Détails du cours {cours.titre}</h1>

                <div className="flex flex-col shadow-md m-5 border-[1px] rounded-lg">
                    <h1 className="p-3 font-semibold text-lg text-white bg-[#C23B42] rounded-t-lg">{cours.titre}</h1>
                    <div className="p-3">
                        <p className="font-semibold text-xl">Description du cours :</p>
                        <Divider />
                        <div className="p-10">
                            <ReactQuill
                                value={description}
                                onChange={setDescription}
                                modules={{
                                    toolbar: [
                                        ['bold', 'italic', 'underline', 'strike'],
                                        ['blockquote'],
                                        [{ list: 'ordered' }, { list: 'bullet' }],
                                        [{ indent: '-1' }, { indent: '+1' }],
                                        [{ header: [1, 2, 3, false] }],
                                        [{ color: [] }, { background: [] }],
                                        [{ align: [] }],
                                        ['clean'],
                                    ],
                                }}
                                style={{ height: '250px', marginBottom: '50px' }}
                            />
                            <div className="mt-4">
                                <p className="font-semibold text-lg mb-2">Aperçu de la description :</p>
                                <div className="ql-snow border p-4 rounded">
                                    <div
                                        className="ql-editor"
                                        dangerouslySetInnerHTML={{ __html: description || 'Aucune description disponible' }}
                                    />
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-end">
                            <Button
                                label={isSaving ? 'Enregistrement...' : 'Enregistrer'}
                                icon={isSaving ? 'pi pi-spin pi-spinner' : 'pi pi-save'}
                                onClick={handleSaveDescription}
                                disabled={isSaving}
                            />
                        </div>
                    </div>
                </div>

                {['document', 'audio', 'video', 'lien'].map((type) => {
                    const supports = cours.supports?.filter((s) => s.type === type) || [];
                    const typeConfig = {
                        document: { icon: <IoIosDocument className="text-2xl mr-2" />, label: 'Document' },
                        audio: { icon: <FaFileAudio className="text-2xl mr-2" />, label: 'Audio' },
                        video: { icon: <FaFileVideo className="text-2xl mr-2" />, label: 'Vidéo' },
                        lien: { icon: <FaLink className="text-2xl mr-2" />, label: 'Lien' },
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
                                    emptyMessage={`Aucun ${typeConfig.label.toLowerCase()} trouvé`}
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
                                    onClick={handleAddCommentaire}
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
                                            className={`${comment.author === 'Moi' ? 'bg-[#3B82F6]' : 'bg-[#C23B42]'} text-white`}
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
                                                        onClick={() =>
                                                            setReplyingTo(replyingTo === comment.id ? null : comment.id)
                                                        }
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
                                    {comment.replies?.length > 0 && (
                                        <div className="ml-12 mt-4 space-y-4">
                                            {comment.replies.map((reply) => (
                                                <div key={reply.id} className="flex items-start gap-3">
                                                    <Avatar
                                                        image={reply.avatar?.startsWith('http') ? reply.avatar : null}
                                                        label={!reply.avatar?.startsWith('http') ? reply.avatar : null}
                                                        shape="circle"
                                                        className={`${reply.author === 'Moi' ? 'bg-[#3B42F6]' : 'bg-[#C23B42]'} text-white`}
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
        </LayoutEnseignant>
    );
};

export default DescriptionCoursEnseignant;