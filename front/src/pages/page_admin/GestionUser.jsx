import React, { useState, useEffect, useRef } from 'react';
import LayoutAdmin from '../../components/LayoutAdmin';
import { TabView, TabPanel } from 'primereact/tabview';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import { InputSwitch } from 'primereact/inputswitch';
import { Toast } from 'primereact/toast';
import { Dropdown } from 'primereact/dropdown';
import { Plus, User } from 'react-feather';
import { Password } from 'primereact/password';
import { getUsers, createEtudiant, createProf, createAdmin, updateEtudiant, updateProf, updateAdmin, deleteEtudiant, deleteProf, deleteAdmin, getMentions, getParcours, getNiveaux, getProvinces, getYears, uploadUserAvatar } from '../../Services/userManagementService';

const GestionUser = () => {
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [displayDialog, setDisplayDialog] = useState(false);
    const [displayDeleteDialog, setDisplayDeleteDialog] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [isNewUser, setIsNewUser] = useState(false);
    const [activeTab, setActiveTab] = useState(0);
    const [activeDialogTab, setActiveDialogTab] = useState(0);
    const [dataStudent, setDataStudent] = useState([]);
    const [dataTeacher, setDataTeacher] = useState([]);
    const [dataAdmin, setDataAdmin] = useState([]);
    const [user, setUser] = useState({
        photo: '',
        matricule: '',
        nom: '',
        prenom: '',
        email: '',
        telephone: '',
        niveau: '',
        mention: '',
        parcours: '',
        typePaiement: '',
        referencePaiement: '',
        province: '',
        ville: '',
        year: '',
        statut: true,
        password: '',
        confirmPassword: ''
    });
    const [tempPhoto, setTempPhoto] = useState(null);
    const [mentionOptions, setMentionOptions] = useState([]);
    const [parcoursOptions, setParcoursOptions] = useState([]);
    const [niveauOptions, setNiveauOptions] = useState([]);
    const [provinceOptions, setProvinceOptions] = useState([]);
    const [yearOptions, setYearOptions] = useState([]);
    const toast = useRef(null);

    const paymentTypes = [
        { label: 'Espèces', value: 'especes' },
        { label: 'Chèque', value: 'cheque' },
        { label: 'Virement', value: 'virement' },
        { label: 'Mobile Money', value: 'mobile_money' }
    ];

    useEffect(() => {
        const fetchData = async () => {
            try {
                const users = await getUsers();
                const formattedUsers = users.map(user => ({
                    ...user,
                    statut: user.status,
                    telephone: user.telephone || 'N/A',
                    province: user.province || 'N/A',
                    ville: user.ville || 'N/A',
                    typePaiement: user.typePaiement || 'N/A',
                    referencePaiement: user.referencePaiement || 'N/A',
                    avatar: user.avatar || user.photo,
                    mention: user.mention || '',
                    parcours: user.parcours || '',
                    niveau: user.niveau || '',
                    year: user.year || 'N/A'
                }));
                setDataStudent(formattedUsers.filter(u => u.type === 'etudiant'));
                setDataTeacher(formattedUsers.filter(u => u.type === 'prof'));
                setDataAdmin(formattedUsers.filter(u => u.type === 'admin'));

                const mentions = await getMentions();
                setMentionOptions(mentions);

                const niveaux = await getNiveaux();
                setNiveauOptions(niveaux);

                const provinces = await getProvinces();
                setProvinceOptions(provinces);

                const years = await getYears();
                setYearOptions(years);
            } catch (error) {
                console.error('Error fetching data:', error);
                toast.current.show({
                    severity: 'error',
                    summary: 'Erreur',
                    detail: error.message || 'Erreur lors du chargement des données',
                    life: 3000
                });
            }
        };
        fetchData();
    }, []);

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const openNew = () => {
        setUser({
            photo: '',
            matricule: '',
            nom: '',
            prenom: '',
            email: '',
            telephone: '',
            niveau: '',
            mention: '',
            parcours: '',
            typePaiement: '',
            referencePaiement: '',
            province: '',
            ville: '',
            year: '',
            statut: true,
            password: '',
            confirmPassword: ''
        });
        setTempPhoto(null);
        setParcoursOptions([]);
        setIsNewUser(true);
        setActiveDialogTab(0);
        setDisplayDialog(true);
    };

    const openEdit = async (userData) => {
        const mentionId = userData.mentionId || '';
        const niveauId = userData.niveauId || '';
        const provinceId = userData.provinceId || '';
        const yearId = userData.yearId || '';

        const formattedUser = {
            id: userData.id,
            etudiantId: userData.etudiantId || null,
            profId: userData.profId || null,
            type: userData.type,
            nom: userData.nom,
            prenom: userData.prenom,
            email: userData.email,
            password: '',
            confirmPassword: '',
            statut: userData.status,
            telephone: userData.telephone === 'N/A' ? '' : userData.telephone,
            province: provinceId,
            ville: userData.ville === 'N/A' ? '' : userData.ville,
            typePaiement: userData.typePaiement === 'N/A' ? '' : userData.typePaiement,
            referencePaiement: userData.referencePaiement || '',
            matricule: userData.matricule || '',
            mention: mentionId,
            parcours: userData.parcoursId || '',
            niveau: niveauId,
            year: yearId,
            photo: userData.avatar || ''
        };

        let parcoursOptionsFetched = [];
        if (mentionId && niveauId) {
            const parcours = await getParcours(mentionId, niveauId);
            parcoursOptionsFetched = parcours;
            setParcoursOptions(parcours);
            if (userData.parcoursId && !parcours.find(p => p.value === userData.parcoursId)) {
                formattedUser.parcours = '';
            }
        } else {
            setParcoursOptions([]);
        }

        setUser(formattedUser);
        setTempPhoto(null);
        setIsNewUser(false);
        setActiveDialogTab(userData.type === 'etudiant' ? 0 : userData.type === 'prof' ? 1 : 2);
        setDisplayDialog(true);
    };

    const hideDialog = () => {
        setDisplayDialog(false);
        setUser({});
        setTempPhoto(null);
        setParcoursOptions([]);
    };

    const hideDeleteDialog = () => {
        setDisplayDeleteDialog(false);
        setSelectedUser(null);
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setTempPhoto(file);
            const reader = new FileReader();
            reader.onloadend = () => {
                setUser(prev => ({ ...prev, photo: reader.result }));
            };
            reader.readAsDataURL(file);
        }
    };

    const fetchParcours = async (mentionId, niveauId) => {
        try {
            if (!mentionId || !niveauId) {
                setParcoursOptions([]);
                return;
            }
            const parcours = await getParcours(mentionId, niveauId);
            setParcoursOptions(parcours);
            // Reset parcours if current selection is invalid
            if (user.parcours && !parcours.find(p => p.value === user.parcours)) {
                setUser(prev => ({ ...prev, parcours: '' }));
            }
        } catch (error) {
            console.error('Error fetching parcours:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Erreur lors de la récupération des parcours',
                life: 3000
            });
            setParcoursOptions([]);
        }
    };

    const handleMentionChange = async (e) => {
        const mention = e.value;
        let _user = { ...user, mention, parcours: '' };
        setUser(_user);
        if (mention && user.niveau) {
            await fetchParcours(mention, user.niveau);
        } else {
            setParcoursOptions([]);
        }
    };

    const handleNiveauChange = async (e) => {
        const niveau = e.value;
        let _user = { ...user, niveau, parcours: '' };
        setUser(_user);
        if (niveau && user.mention) {
            await fetchParcours(user.mention, niveau);
        } else {
            setParcoursOptions([]);
        }
    };

    const saveUser = async () => {
        if (user.password !== user.confirmPassword) {
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: 'Les mots de passe ne correspondent pas',
                life: 3000
            });
            return;
        }

        const formData = new FormData();
        formData.append('nom', user.nom);
        formData.append('prenom', user.prenom);
        formData.append('email', user.email);
        formData.append('telephone', user.telephone || '');
        formData.append('province', user.province || '');
        formData.append('ville', user.ville || '');
        formData.append('status', user.statut ? '1' : '0');
        if (user.password) {
            formData.append('password', user.password);
        }
        if (tempPhoto) {
            formData.append('avatarFile', tempPhoto);
        }

        try {
            let response;
            let avatarResponse;
            if (isNewUser) {
                if (activeTab === 0) {
                    if (!user.matricule || !user.mention || !user.parcours || !user.niveau || !user.year) {
                        toast.current.show({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Veuillez remplir tous les champs obligatoires pour l\'étudiant',
                            life: 3000
                        });
                        return;
                    }
                    if (!parcoursOptions.find(p => p.value === user.parcours)) {
                        toast.current.show({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Parcours sélectionné non valide',
                            life: 3000
                        });
                        return;
                    }
                    formData.append('matricule', user.matricule);
                    formData.append('mention', user.mention);
                    formData.append('parcours', user.parcours);
                    formData.append('niveau', user.niveau);
                    formData.append('type_payement', user.typePaiement || '');
                    formData.append('reference', user.referencePaiement || '');
                    formData.append('year', user.year);
                    response = await createEtudiant(formData);
                    setDataStudent([...dataStudent, {
                        ...response,
                        statut: response.status,
                        telephone: response.telephone || 'N/A',
                        province: response.province || 'N/A',
                        ville: response.ville || 'N/A',
                        typePaiement: response.typePaiement || 'N/A',
                        referencePaiement: response.referencePaiement || 'N/A',
                        mention: response.mention,
                        parcours: response.parcours,
                        niveau: response.niveau,
                        year: response.year
                    }]);
                } else if (activeTab === 1) {
                    response = await createProf(formData);
                    setDataTeacher([...dataTeacher, {
                        id: response.id,
                        profId: response.profId,
                        type: response.type,
                        nom: response.nom,
                        prenom: response.prenom,
                        email: response.email,
                        telephone: response.telephone || 'N/A',
                        avatar: response.avatar,
                        statut: response.status,
                        province: response.province || 'N/A',
                        provinceId: response.provinceId || '',
                        ville: response.ville || 'N/A'
                    }]);
                } else {
                    response = await createAdmin(formData);
                    setDataAdmin([...dataAdmin, {
                        ...response,
                        statut: response.status,
                        telephone: response.telephone || 'N/A',
                        province: response.province || 'N/A',
                        ville: response.ville || 'N/A'
                    }]);
                }
                toast.current.show({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Utilisateur créé avec succès',
                    life: 3000
                });
                hideDialog();
            } else {
                const data = {
                    nom: user.nom,
                    prenom: user.prenom,
                    email: user.email,
                    telephone: user.telephone || null,
                    province: user.province || null,
                    ville: user.ville || null,
                    status: user.statut,
                    ...(user.password && { password: user.password }),
                    ...(activeTab === 0 && {
                        matricule: user.matricule,
                        mention: user.mention,
                        parcours: user.parcours,
                        niveau: user.niveau,
                        type_payement: user.typePaiement || null,
                        reference: user.referencePaiement || null,
                        year: user.year || null
                    })
                };

                if (activeTab === 0) {
                    if (!parcoursOptions.find(p => p.value === user.parcours)) {
                        toast.current.show({
                            severity: 'error',
                            summary: 'Erreur',
                            detail: 'Parcours sélectionné non valide',
                            life: 3000
                        });
                        return;
                    }
                    response = await updateEtudiant(user.etudiantId, data);
                    if (tempPhoto) {
                        avatarResponse = await uploadUserAvatar(user.id, tempPhoto);
                    }
                    setDataStudent(dataStudent.map(u => u.id === user.id ? {
                        ...u,
                        ...response,
                        avatar: avatarResponse ? avatarResponse.avatar : u.avatar,
                        statut: response.status,
                        telephone: response.telephone || 'N/A',
                        province: response.province ? provinceOptions.find(p => p.value === response.provinceId)?.label || 'N/A' : 'N/A',
                        ville: response.ville || 'N/A',
                        typePaiement: response.typePaiement || 'N/A',
                        referencePaiement: response.referencePaiement || 'N/A',
                        mention: response.mention || u.montion,
                        mentionId: response.mentionId || u.montionId,
                        parcours: response.parcours || u.parcours,
                        parcoursId: response.parcoursId || u.parcoursId,
                        niveau: response.niveau || u.niveau,
                        niveauId: response.niveauId || u.niveauId,
                        year: response.year || u.year,
                        yearId: response.yearId || u.yearId
                    } : u));
                } else if (activeTab === 1) {
                    response = await updateProf(user.profId, data);
                    if (tempPhoto) {
                        avatarResponse = await uploadUserAvatar(user.id, tempPhoto);
                    }
                    setDataTeacher(dataTeacher.map(u => u.id === user.id ? {
                        ...u,
                        ...response,
                        avatar: avatarResponse ? avatarResponse.avatar : u.avatar,
                        statut: response.status,
                        telephone: response.telephone || 'N/A',
                        province: response.province ? provinceOptions.find(p => p.value === response.provinceId)?.label || 'N/A' : 'N/A',
                        ville: response.ville || 'N/A'
                    } : u));
                } else {
                    response = await updateAdmin(user.id, data);
                    if (tempPhoto) {
                        avatarResponse = await uploadUserAvatar(user.id, tempPhoto);
                    }
                    setDataAdmin(dataAdmin.map(u => u.id === user.id ? {
                        ...u,
                        ...response,
                        avatar: avatarResponse ? avatarResponse.avatar : u.avatar,
                        statut: response.status,
                        telephone: response.telephone || 'N/A',
                        province: response.province ? provinceOptions.find(p => p.value === response.provinceId)?.label || 'N/A' : 'N/A',
                        ville: response.ville || 'N/A'
                    } : u));
                }

                toast.current.show({
                    severity: 'success',
                    summary: 'Succès',
                    detail: 'Utilisateur mis à jour avec succès',
                    life: 3000
                });
                hideDialog();
            }
        } catch (error) {
            console.error('Error saving user:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Erreur de l\'enregistrement de l\'utilisateur',
                life: 3000
            });
        }
    };

    const confirmDelete = (user) => {
        setSelectedUser(user);
        setDisplayDeleteDialog(true);
    };

    const deleteUser = async () => {
        try {
            if (activeTab === 0) {
                await deleteEtudiant(selectedUser.etudiantId); // Utiliser etudiantId
                setDataStudent(dataStudent.filter(u => u.etudiantId !== selectedUser.etudiantId));
            } else if (activeTab === 1) {
                await deleteProf(selectedUser.profId); // Utiliser profId
                setDataTeacher(dataTeacher.filter(u => u.profId !== selectedUser.profId));
            } else {
                await deleteAdmin(selectedUser.id); // Utiliser userId pour les admins
                setDataAdmin(dataAdmin.filter(u => u.id !== selectedUser.id));
            }
            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Utilisateur supprimé avec succès',
                life: 3000
            });
            hideDeleteDialog();
        } catch (error) {
            console.error('Error deleting user:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Erreur lors de la suppression de l\'utilisateur',
                life: 3000
            });
        }
    };

    const onStatusChange = async (e, user) => {
        const newStatus = e.value;
        try {
            const data = { status: newStatus };
            if (activeTab === 0) {
                await updateEtudiant(user.id, data);
                setDataStudent(dataStudent.map(u => u.id === user.id ? { ...u, statut: newStatus } : u));
            } else if (activeTab === 1) {
                await updateProf(user.id, data);
                setDataTeacher(dataTeacher.map(u => u.id === user.id ? { ...u, statut: newStatus } : u));
            } else {
                await updateAdmin(user.id, data);
                setDataAdmin(dataAdmin.map(u => u.id === user.id ? { ...u, statut: newStatus } : u));
            }
        } catch (error) {
            console.error('Error updating status:', error);
            toast.current.show({
                severity: 'error',
                summary: 'Erreur',
                detail: error.message || 'Erreur lors de la mise à jour du statut',
                life: 3000
            });
        }
    };

    const renderHeaderStudent = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal text-gray-800'>Etudiants</h1>
                <div className='flex items-center justify-center space-x-5'>
                    <Button icon="pi pi-plus" rounded onClick={openNew}
                        tooltip="Ajouter un étudiant" tooltipOptions={{ position: 'top' }} />
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange}
                            placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                </div>
            </div>
        );
    };

    const renderHeaderTeacher = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal text-gray-800'>Enseignants</h1>
                <div className='flex items-center justify-center space-x-5'>
                    <Button icon="pi pi-plus" rounded onClick={openNew}
                        tooltip="Ajouter un enseignant" tooltipOptions={{ position: 'top' }} />
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange}
                            placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                </div>
            </div>
        );
    };

    const renderHeaderAdmin = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal text-gray-800'>Administrateurs</h1>
                <div className='flex items-center justify-center space-x-5'>
                    <Button icon="pi pi-plus" rounded onClick={openNew}
                        tooltip="Ajouter un administrateur" tooltipOptions={{ position: 'top' }} />
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange}
                            placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                </div>
            </div>
        );
    };

    const statusBodyTemplate = (rowData) => {
        return (
            <InputSwitch
                checked={rowData.statut}
                onChange={(e) => onStatusChange(e, rowData)}
            />
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex items-center justify-center space-x-2'>
                <Button icon="pi pi-pencil" severity="info" className="p-2 bg-transparent hover:bg-blue-100 text-blue-600 border-none" rounded text
                    onClick={() => openEdit(rowData)}
                    tooltip="Modifier" tooltipOptions={{ position: 'top' }} />
                <Button icon="pi pi-trash" severity="danger" className="p-2 bg-transparent hover:bg-red-100 text-red-600 border-none" rounded text
                    onClick={() => confirmDelete(rowData)}
                    tooltip="Supprimer" tooltipOptions={{ position: 'top' }} />
            </div>
        );
    };

    const imageBodyTemplate = (rowData) => {
        return (
            <div className="flex items-center justify-center">
                <div className="relative w-10 h-10">
                    <img
                        src={rowData.avatar || rowData.photo}
                        alt={`${rowData.nom} ${rowData.prenom}`}
                        className="absolute w-full h-full rounded-full object-cover border-2 border-white shadow-sm"
                    />
                </div>
            </div>
        );
    };

    const nameBodyTemplate = (rowData) => {
        return <span>{rowData.nom} {rowData.prenom}</span>;
    };

    const mentionTemplate = (rowData) => {
        return <span>{rowData.mention || 'N/A'}</span>;
    };

    const parcoursTemplate = (rowData) => {
        return <span>{rowData.parcours || 'N/A'}</span>;
    };

    const niveauTemplate = (rowData) => {
        return <span>{rowData.niveau || 'N/A'}</span>;
    };

    const paymentTypeTemplate = (rowData) => {
        const paymentLabel = paymentTypes.find(pt => pt.value === rowData.typePaiement)?.label || 'N/A';
        return <span>{paymentLabel}</span>;
    };

    const villeTemplate = (rowData) => {
        return <span>{rowData.ville || 'N/A'}</span>;
    };

    const yearTemplate = (rowData) => {
        return <span>{rowData.year || 'N/A'}</span>;
    };

    const dialogFooter = (
        <>
            <Button label="Annuler" icon="pi pi-times" outlined onClick={hideDialog} />
            <Button label="Enregistrer" icon="pi pi-check" onClick={saveUser} />
        </>
    );

    const deleteDialogFooter = (
        <>
            <Button label="Non" icon="pi pi-times" outlined onClick={hideDeleteDialog} />
            <Button label="Oui" icon="pi pi-check" severity="danger" onClick={deleteUser} />
        </>
    );

    const renderPhotoUpload = () => {
        return (
            <div className="relative mb-4 items-center justify-center flex">
                {tempPhoto || user.photo ? (
                    <img
                        src={tempPhoto ? URL.createObjectURL(tempPhoto) : user.photo}
                        alt="Profil"
                        className="w-24 h-24 rounded-full mb-4 object-cover"
                    />
                ) : (
                    <div className="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-4">
                        <User className="w-12 h-12 text-gray-400" />
                    </div>
                )}
                <label className="absolute bottom-4 right-36 text-gray-500 hover:text-gray-700 z-10 bg-gray-400 rounded-full p-1 shadow-md flex items-center justify-center cursor-pointer">
                    <Plus className="w-7 h-7 text-gray-800" />
                    <input
                        type="file"
                        className="hidden"
                        onChange={handleFileChange}
                        accept="image/*"
                    />
                </label>
            </div>
        );
    };

    const renderProfileTab = () => {
        return (
            <div className="grid">
                <div className="col-12">
                    {renderPhotoUpload()}
                </div>

                {activeTab === 0 && (
                    <div className="col-12">
                        <div className="field">
                            <label htmlFor="matricule">Matricule</label>
                            <InputText
                                id="matricule"
                                value={user.matricule}
                                onChange={(e) => onInputChange(e, 'matricule')}
                                className='custom-input'
                                required
                            />
                        </div>
                    </div>
                )}

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="nom">Nom</label>
                        <InputText
                            id="nom"
                            value={user.nom}
                            onChange={(e) => onInputChange(e, 'nom')}
                            className='custom-input'
                            required
                            autoFocus
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="prenom">Prénom</label>
                        <InputText
                            id="prenom"
                            value={user.prenom}
                            onChange={(e) => onInputChange(e, 'prenom')}
                            className='custom-input'
                            required
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="email">Email</label>
                        <InputText
                            id="email"
                            value={user.email}
                            onChange={(e) => onInputChange(e, 'email')}
                            className='custom-input'
                            required
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="telephone">Téléphone</label>
                        <InputText
                            id="telephone"
                            value={user.telephone}
                            onChange={(e) => onInputChange(e, 'telephone')}
                            className='custom-input'
                        />
                    </div>
                </div>

                {activeTab === 0 && (
                    <>
                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="niveau">Niveau</label>
                                <Dropdown
                                    id="niveau"
                                    value={user.niveau}
                                    options={niveauOptions}
                                    onChange={handleNiveauChange}
                                    placeholder={niveauOptions.length ? "Sélectionner un niveau" : "Aucun niveau disponible"}
                                    className="w-full"
                                    disabled={!niveauOptions.length}
                                />
                            </div>
                        </div>

                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="mention">Mention</label>
                                <Dropdown
                                    id="mention"
                                    value={user.mention}
                                    options={mentionOptions}
                                    onChange={handleMentionChange}
                                    placeholder={mentionOptions.length ? "Sélectionner une mention" : "Aucune mention disponible"}
                                    className="w-full"
                                    disabled={!mentionOptions.length}
                                />
                            </div>
                        </div>

                        <div className="col-12">
                            <div className="field">
                                <label htmlFor="parcours">Parcours</label>
                                <Dropdown
                                    id="parcours"
                                    value={user.parcours}
                                    options={parcoursOptions}
                                    onChange={(e) => onDropdownChange(e, 'parcours')}
                                    placeholder={parcoursOptions.length ? "Sélectionner un parcours" : "Aucun parcours disponible"}
                                    className="w-full"
                                    disabled={!user.mention || !user.niveau || !parcoursOptions.length}
                                />
                            </div>
                        </div>

                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="year">Année</label>
                                <Dropdown
                                    id="year"
                                    value={user.year}
                                    options={yearOptions}
                                    onChange={(e) => onDropdownChange(e, 'year')}
                                    placeholder={yearOptions.length ? "Sélectionner une année" : "Aucune année disponible"}
                                    className="w-full"
                                    disabled={!yearOptions.length}
                                />
                            </div>
                        </div>

                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="typePaiement">Type de paiement</label>
                                <Dropdown
                                    id="typePaiement"
                                    value={user.typePaiement}
                                    options={paymentTypes}
                                    optionLabel="label"
                                    onChange={(e) => onDropdownChange(e, 'typePaiement')}
                                    placeholder="Sélectionner"
                                    className="w-full"
                                />
                            </div>
                        </div>

                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="referencePaiement">Référence paiement</label>
                                <InputText
                                    id="referencePaiement"
                                    value={user.referencePaiement}
                                    onChange={(e) => onInputChange(e, 'referencePaiement')}
                                    className='custom-input'
                                />
                            </div>
                        </div>
                    </>
                )}

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="province">Province</label>
                        <Dropdown
                            id="province"
                            value={user.province}
                            options={provinceOptions}
                            optionLabel="label"
                            onChange={(e) => onDropdownChange(e, 'province')}
                            placeholder={provinceOptions.length ? "Sélectionner une province" : "Aucune province disponible"}
                            className="w-full"
                            disabled={!provinceOptions.length}
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="ville">Ville</label>
                        <InputText
                            id="ville"
                            value={user.ville}
                            onChange={(e) => onInputChange(e, 'ville')}
                            className='custom-input'
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field flex items-center justify-start space-x-5">
                        <label htmlFor="statut">Statut</label>
                        <InputSwitch
                            id="statut"
                            checked={user.statut}
                            onChange={(e) => setUser({ ...user, statut: e.value })}
                        />
                    </div>
                </div>
            </div>
        );
    };

    const renderPasswordTab = () => {
        return (
            <div className="grid">
                <div className="col-12">
                    <div className="field">
                        <label htmlFor="password">Nouveau mot de passe</label>
                        <Password
                            id="password"
                            value={user.password}
                            onChange={(e) => onInputChange(e, 'password')}
                            feedback={false}
                            className="w-full"
                        />
                    </div>
                </div>

                <div className="col-12">
                    <div className="field">
                        <label htmlFor="confirmPassword">Confirmer le mot de passe</label>
                        <Password
                            id="confirmPassword"
                            value={user.confirmPassword}
                            onChange={(e) => onInputChange(e, 'confirmPassword')}
                            feedback={false}
                            className="w-full"
                        />
                    </div>
                </div>
            </div>
        );
    };

    const onInputChange = (e, name) => {
        const val = (e.target && e.target.value) || '';
        let _user = { ...user };
        _user[`${name}`] = val;
        setUser(_user);
    };

    const onDropdownChange = (e, name) => {
        let _user = { ...user };
        _user[name] = e.value;
        setUser(_user);
    };

    const headerStudent = renderHeaderStudent();
    const headerTeacher = renderHeaderTeacher();
    const headerAdmin = renderHeaderAdmin();

    return (
        <LayoutAdmin>
            <Toast ref={toast} />
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold text-gray-800 p-5 mb-4'>Gestion des utilisateurs</h1>
                <div>
                    <TabView activeIndex={activeTab} onTabChange={(e) => setActiveTab(e.index)} className="ml-4 md:ml-0 custom-tabview">
                        <TabPanel header="Liste des étudiants" className='flex flex-col items-center'>
                            <DataTable
                                value={dataStudent}
                                paginator
                                rows={5}
                                dataKey="id"
                                sortField="nom"
                                sortOrder={1}
                                globalFilter={globalFilterValue}
                                header={headerStudent}
                                emptyMessage="Aucune donnée trouvée."
                                className="w-full"
                            >
                                <Column field="avatar" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="matricule" header="Matricule" sortable style={{ minWidth: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="niveau" header="Niveau" body={niveauTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" body={mentionTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="parcours" header="Parcours" body={parcoursTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="year" header="Année" body={yearTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="typePaiement" header="Type de paiement" body={paymentTypeTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="ville" header="Ville" body={villeTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Liste des enseignants" className='flex flex-col items-center'>
                            <DataTable
                                value={dataTeacher}
                                paginator
                                rows={5}
                                dataKey="id"
                                sortField="nom"
                                sortOrder={1}
                                globalFilter={globalFilterValue}
                                header={headerTeacher}
                                emptyMessage="Aucune donnée trouvée."
                                className="w-full"
                            >
                                <Column field="avatar" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="ville" header="Ville" body={villeTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Liste des administrateurs" className='flex flex-col items-center'>
                            <DataTable
                                value={dataAdmin}
                                paginator
                                rows={5}
                                dataKey="id"
                                sortField="nom"
                                sortOrder={1}
                                globalFilter={globalFilterValue}
                                header={headerAdmin}
                                emptyMessage="Aucune donnée trouvée."
                                className="w-full"
                            >
                                <Column field="avatar" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="ville" header="Ville" body={villeTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>

            <Dialog visible={displayDialog} style={{ width: '500px' }} header={isNewUser ? 'Nouvel Utilisateur' : 'Modifier Utilisateur'} modal className="p-fluid" footer={dialogFooter} onHide={hideDialog}>
                <TabView activeIndex={activeDialogTab} onTabChange={(e) => setActiveDialogTab(e.index)}>
                    <TabPanel header="Profil">
                        {renderProfileTab()}
                    </TabPanel>
                    <TabPanel header="Mot de passe">
                        {renderPasswordTab()}
                    </TabPanel>
                </TabView>
            </Dialog>

            <Dialog visible={displayDeleteDialog} style={{ width: '450px' }} header="Confirmation" modal footer={deleteDialogFooter} onHide={hideDeleteDialog}>
                <div className="confirmation-content">
                    <i className="pi pi-exclamation-triangle mr-3" style={{ fontSize: '2rem' }} />
                    {selectedUser && (
                        <span>Voulez-vous vraiment supprimer <b>{selectedUser.nom} {selectedUser.prenom}</b> ?</span>
                    )}
                </div>
            </Dialog>
        </LayoutAdmin>
    );
};

export default GestionUser;