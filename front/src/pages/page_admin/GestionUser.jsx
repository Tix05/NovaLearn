import React, { useState } from 'react';
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

const GestionUser = () => {
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [displayDialog, setDisplayDialog] = useState(false);
    const [displayDeleteDialog, setDisplayDeleteDialog] = useState(false);
    const [selectedUser, setSelectedUser] = useState(null);
    const [isNewUser, setIsNewUser] = useState(false);
    const [activeTab, setActiveTab] = useState(0);
    const [activeDialogTab, setActiveDialogTab] = useState(0);
    const toast = React.useRef(null);

    const roleOptions = [
        { label: 'Administrateur', value: 'admin' },
        { label: 'Super Administrateur', value: 'super_admin' },
        { label: 'Gestionnaire', value: 'manager' }
    ];

    // Options pour les dropdowns
    const paymentTypes = [
        { label: 'Espèces', value: 'especes' },
        { label: 'Chèque', value: 'cheque' },
        { label: 'Virement', value: 'virement' },
        { label: 'Mobile Money', value: 'mobile_money' }
    ];

    const regionalCenters = [
        { label: 'Dakar', value: 'dakar' },
        { label: 'Thiès', value: 'thies' },
        { label: 'Saint-Louis', value: 'saint_louis' },
        { label: 'Ziguinchor', value: 'ziguinchor' },
        { label: 'Kaolack', value: 'kaolack' }
    ];

    // Options pour les niveaux
    const niveauOptions = [
        { label: 'L1', value: 'L1' },
        { label: 'L2', value: 'L2' },
        { label: 'L3', value: 'L3' },
        { label: 'M1', value: 'M1' },
        { label: 'M2', value: 'M2' }
    ];

    // Options pour les mentions
    const mentionOptions = [
        { label: 'Informatique', value: 'Informatique' },
        { label: 'Mathématiques', value: 'Mathématiques' },
        { label: 'Physique', value: 'Physique' },
        { label: 'Chimie', value: 'Chimie' },
        { label: 'Biologie', value: 'Biologie' },
        { label: 'Économie', value: 'Économie' },
        { label: 'Droit', value: 'Droit' },
        { label: 'Histoire', value: 'Histoire' },
        { label: 'Géographie', value: 'Géographie' },
        { label: 'Philosophie', value: 'Philosophie' }
    ];

    // Options pour les parcours (groupés par mention)
    const parcoursOptions = {
        'Informatique': [
            { label: 'Développement Web', value: 'Développement Web' },
            { label: 'Intelligence Artificielle', value: 'Intelligence Artificielle' },
            { label: 'Systèmes Informatiques', value: 'Systèmes Informatiques' }
        ],
        'Mathématiques': [
            { label: 'Analyse', value: 'Analyse' },
            { label: 'Algèbre', value: 'Algèbre' },
            { label: 'Statistiques', value: 'Statistiques' }
        ],
        'Physique': [
            { label: 'Physique Quantique', value: 'Physique Quantique' },
            { label: 'Physique des Matériaux', value: 'Physique des Matériaux' },
            { label: 'Astrophysique', value: 'Astrophysique' }
        ],
        'Chimie': [
            { label: 'Chimie Organique', value: 'Chimie Organique' },
            { label: 'Chimie Analytique', value: 'Chimie Analytique' },
            { label: 'Chimie Physique', value: 'Chimie Physique' }
        ],
        'Biologie': [
            { label: 'Biologie Moléculaire', value: 'Biologie Moléculaire' },
            { label: 'Biologie Cellulaire', value: 'Biologie Cellulaire' },
            { label: 'Écologie', value: 'Écologie' }
        ],
        'Économie': [
            { label: 'Économétrie', value: 'Économétrie' },
            { label: 'Économie Internationale', value: 'Économie Internationale' },
            { label: 'Finance', value: 'Finance' }
        ],
        'Droit': [
            { label: 'Droit International', value: 'Droit International' },
            { label: 'Droit des Affaires', value: 'Droit des Affaires' },
            { label: 'Droit Public', value: 'Droit Public' }
        ],
        'Histoire': [
            { label: 'Histoire Contemporaine', value: 'Histoire Contemporaine' },
            { label: 'Histoire Ancienne', value: 'Histoire Ancienne' },
            { label: 'Histoire Médiévale', value: 'Histoire Médiévale' }
        ],
        'Géographie': [
            { label: 'Géographie Humaine', value: 'Géographie Humaine' },
            { label: 'Géographie Physique', value: 'Géographie Physique' },
            { label: 'Aménagement du Territoire', value: 'Aménagement du Territoire' }
        ],
        'Philosophie': [
            { label: 'Philosophie Politique', value: 'Philosophie Politique' },
            { label: 'Philosophie des Sciences', value: 'Philosophie des Sciences' },
            { label: 'Éthique', value: 'Éthique' }
        ]
    };


    // Données initiales des étudiants (10 éléments)
    const initialStudents = [
        { id: 1, photo: 'https://randomuser.me/api/portraits/women/1.jpg', matricule: 'ET2023001', nom: 'Dupont', prenom: 'Marie', email: 'marie.dupont@email.com', telephone: '06 12 34 56 78', niveau: 'L3', mention: 'Informatique', parcours: 'Développement Web', typePaiement: 'especes', referencePaiement: 'REF001', centreRegional: 'dakar', statut: true },
        { id: 2, photo: 'https://randomuser.me/api/portraits/men/1.jpg', matricule: 'ET2023002', nom: 'Martin', prenom: 'Jean', email: 'jean.martin@email.com', telephone: '06 23 45 67 89', niveau: 'M1', mention: 'Mathématiques', parcours: 'Analyse', typePaiement: 'virement', referencePaiement: 'REF002', centreRegional: 'thies', statut: false },
        { id: 3, photo: 'https://randomuser.me/api/portraits/women/2.jpg', matricule: 'ET2023003', nom: 'Bernard', prenom: 'Sophie', email: 'sophie.bernard@email.com', telephone: '06 34 56 78 90', niveau: 'L2', mention: 'Physique', parcours: 'Physique Quantique', typePaiement: 'cheque', referencePaiement: 'REF003', centreRegional: 'saint_louis', statut: true },
        { id: 4, photo: 'https://randomuser.me/api/portraits/men/2.jpg', matricule: 'ET2023004', nom: 'Petit', prenom: 'Pierre', email: 'pierre.petit@email.com', telephone: '06 45 67 89 01', niveau: 'M2', mention: 'Chimie', parcours: 'Chimie Organique', typePaiement: 'mobile_money', referencePaiement: 'REF004', centreRegional: 'ziguinchor', statut: true },
        { id: 5, photo: 'https://randomuser.me/api/portraits/women/3.jpg', matricule: 'ET2023005', nom: 'Durand', prenom: 'Isabelle', email: 'isabelle.durand@email.com', telephone: '06 56 78 90 12', niveau: 'L1', mention: 'Biologie', parcours: 'Biologie Moléculaire', typePaiement: 'especes', referencePaiement: 'REF005', centreRegional: 'kaolack', statut: false },
        { id: 6, photo: 'https://randomuser.me/api/portraits/men/3.jpg', matricule: 'ET2023006', nom: 'Leroy', prenom: 'Thomas', email: 'thomas.leroy@email.com', telephone: '06 67 89 01 23', niveau: 'L3', mention: 'Économie', parcours: 'Économétrie', typePaiement: 'virement', referencePaiement: 'REF006', centreRegional: 'dakar', statut: true },
        { id: 7, photo: 'https://randomuser.me/api/portraits/women/4.jpg', matricule: 'ET2023007', nom: 'Moreau', prenom: 'Céline', email: 'celine.moreau@email.com', telephone: '06 78 90 12 34', niveau: 'M1', mention: 'Droit', parcours: 'Droit International', typePaiement: 'cheque', referencePaiement: 'REF007', centreRegional: 'thies', statut: true },
        { id: 8, photo: 'https://randomuser.me/api/portraits/men/4.jpg', matricule: 'ET2023008', nom: 'Simon', prenom: 'Nicolas', email: 'nicolas.simon@email.com', telephone: '06 89 01 23 45', niveau: 'L2', mention: 'Histoire', parcours: 'Histoire Contemporaine', typePaiement: 'mobile_money', referencePaiement: 'REF008', centreRegional: 'saint_louis', statut: false },
        { id: 9, photo: 'https://randomuser.me/api/portraits/women/5.jpg', matricule: 'ET2023009', nom: 'Laurent', prenom: 'Valérie', email: 'valerie.laurent@email.com', telephone: '06 90 12 34 56', niveau: 'M2', mention: 'Géographie', parcours: 'Géographie Humaine', typePaiement: 'especes', referencePaiement: 'REF009', centreRegional: 'ziguinchor', statut: true },
        { id: 10, photo: 'https://randomuser.me/api/portraits/men/5.jpg', matricule: 'ET2023010', nom: 'Michel', prenom: 'François', email: 'francois.michel@email.com', telephone: '06 01 23 45 67', niveau: 'L1', mention: 'Philosophie', parcours: 'Philosophie Politique', typePaiement: 'virement', referencePaiement: 'REF010', centreRegional: 'kaolack', statut: true }
    ];

    // Données initiales des enseignants (10 éléments)
    const initialTeachers = [
        { id: 1, photo: 'https://randomuser.me/api/portraits/men/6.jpg', nom: 'Roux', prenom: 'Michel', email: 'michel.roux@email.com', telephone: '06 12 34 56 78', centreRegional: 'dakar', statut: true },
        { id: 2, photo: 'https://randomuser.me/api/portraits/women/6.jpg', nom: 'Fournier', prenom: 'Isabelle', email: 'isabelle.fournier@email.com', telephone: '06 23 45 67 89', centreRegional: 'thies', statut: true },
        { id: 3, photo: 'https://randomuser.me/api/portraits/men/7.jpg', nom: 'Lefebvre', prenom: 'Philippe', email: 'philippe.lefebvre@email.com', telephone: '06 34 56 78 90', centreRegional: 'saint_louis', statut: false },
        { id: 4, photo: 'https://randomuser.me/api/portraits/women/7.jpg', nom: 'Dumont', prenom: 'Élodie', email: 'elodie.dumont@email.com', telephone: '06 45 67 89 01', centreRegional: 'ziguinchor', statut: true },
        { id: 5, photo: 'https://randomuser.me/api/portraits/men/8.jpg', nom: 'Girard', prenom: 'Jacques', email: 'jacques.girard@email.com', telephone: '06 56 78 90 12', centreRegional: 'kaolack', statut: true },
        { id: 6, photo: 'https://randomuser.me/api/portraits/women/8.jpg', nom: 'Bonnet', prenom: 'Christine', email: 'christine.bonnet@email.com', telephone: '06 67 89 01 23', centreRegional: 'dakar', statut: false },
        { id: 7, photo: 'https://randomuser.me/api/portraits/men/9.jpg', nom: 'Francois', prenom: 'Patrick', email: 'patrick.francois@email.com', telephone: '06 78 90 12 34', centreRegional: 'thies', statut: true },
        { id: 8, photo: 'https://randomuser.me/api/portraits/women/9.jpg', nom: 'Mercier', prenom: 'Nathalie', email: 'nathalie.mercier@email.com', telephone: '06 89 01 23 45', centreRegional: 'saint_louis', statut: true },
        { id: 9, photo: 'https://randomuser.me/api/portraits/men/10.jpg', nom: 'Legrand', prenom: 'Éric', email: 'eric.legrand@email.com', telephone: '06 90 12 34 56', centreRegional: 'ziguinchor', statut: false },
        { id: 10, photo: 'https://randomuser.me/api/portraits/women/10.jpg', nom: 'Faure', prenom: 'Sandrine', email: 'sandrine.faure@email.com', telephone: '06 01 23 45 67', centreRegional: 'kaolack', statut: true }
    ];

    const initialAdmins = [
        { id: 1, photo: 'https://randomuser.me/api/portraits/men/11.jpg', nom: 'Admin', prenom: 'Super', email: 'super.admin@email.com', telephone: '06 11 22 33 44', centreRegional: 'dakar', role: 'super_admin', statut: true },
        { id: 2, photo: 'https://randomuser.me/api/portraits/women/11.jpg', nom: 'Admin', prenom: 'Principal', email: 'admin.principal@email.com', telephone: '06 22 33 44 55', centreRegional: 'thies', role: 'admin', statut: true },
        { id: 3, photo: 'https://randomuser.me/api/portraits/men/12.jpg', nom: 'Gestionnaire', prenom: 'Campus', email: 'gestion.campus@email.com', telephone: '06 33 44 55 66', centreRegional: 'saint_louis', role: 'manager', statut: true },
        { id: 4, photo: 'https://randomuser.me/api/portraits/women/12.jpg', nom: 'Responsable', prenom: 'Finances', email: 'finances@email.com', telephone: '06 44 55 66 77', centreRegional: 'ziguinchor', role: 'manager', statut: false },
        { id: 5, photo: 'https://randomuser.me/api/portraits/men/13.jpg', nom: 'Coordinateur', prenom: 'Regional', email: 'coord.regional@email.com', telephone: '06 55 66 77 88', centreRegional: 'kaolack', role: 'manager', statut: true }
    ];

    const [dataStudent, setDataStudent] = useState(initialStudents);
    const [dataTeacher, setDataTeacher] = useState(initialTeachers);
    const [dataAdmin, setDataAdmin] = useState(initialAdmins);
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
        centreRegional: '',
        statut: true,
        password: '',
        confirmPassword: ''
    });
    const [tempPhoto, setTempPhoto] = useState(null);

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
            centreRegional: '',
            statut: true,
            password: '',
            confirmPassword: ''
        });
        setTempPhoto(null);
        setIsNewUser(true);
        setActiveDialogTab(0);
        setDisplayDialog(true);
    };

    const openEdit = (userData) => {
        setUser({
            ...userData,
            password: '',
            confirmPassword: ''
        });
        setTempPhoto(null);
        setIsNewUser(false);
        setActiveDialogTab(0);
        setDisplayDialog(true);
    };

    const hideDialog = () => {
        setDisplayDialog(false);
        setUser({});
        setTempPhoto(null);
    };

    const hideDeleteDialog = () => {
        setDisplayDeleteDialog(false);
        setSelectedUser(null);
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                setTempPhoto(reader.result);
            };
            reader.readAsDataURL(file);
        }
    };

    const saveUser = () => {
        // Gestion de la photo
        let photoUrl = tempPhoto || user.photo;
        if (!photoUrl) {
            photoUrl = `https://randomuser.me/api/portraits/${activeTab === 0 ? 'women' : 'men'}/${Math.floor(Math.random() * 50)}.jpg`;
        }

        if (isNewUser) {
            // Ajouter un nouvel utilisateur
            const newId = activeTab === 0
                ? Math.max(...dataStudent.map(u => u.id)) + 1
                : Math.max(...dataTeacher.map(u => u.id)) + 1;

            const newUser = {
                ...user,
                id: newId,
                photo: photoUrl
            };

            if (activeTab === 0) {
                setDataStudent([...dataStudent, newUser]);
            } else {
                setDataTeacher([...dataTeacher, newUser]);
            }

            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Utilisateur créé avec succès',
                life: 3000
            });
        } else {
            // Modifier un utilisateur existant
            const updatedUser = {
                ...user,
                photo: photoUrl
            };

            if (activeTab === 0) {
                setDataStudent([...dataStudent, newUser]);
            } else if (activeTab === 1) {
                setDataTeacher([...dataTeacher, newUser]);
            } else {
                setDataAdmin([...dataAdmin, newUser]);
            }

            toast.current.show({
                severity: 'success',
                summary: 'Succès',
                detail: 'Utilisateur mis à jour avec succès',
                life: 3000
            });
        }

        hideDialog();
    };

    const confirmDelete = (user) => {
        setSelectedUser(user);
        setDisplayDeleteDialog(true);
    };

    const deleteUser = () => {
        if (activeTab === 0) {
            setDataStudent(dataStudent.filter(u => u.id !== selectedUser.id));
        } else if (activeTab === 1) {
            setDataTeacher(dataTeacher.filter(u => u.id !== selectedUser.id));
        } else {
            setDataAdmin(dataAdmin.filter(u => u.id !== selectedUser.id));
        }

        toast.current.show({
            severity: 'success',
            summary: 'Succès',
            detail: 'Utilisateur supprimé avec succès',
            life: 3000
        });
        hideDeleteDialog();
    };

    const onStatusChange = (e, user) => {
        const newStatus = e.value;
        if (activeTab === 0) {
            setDataStudent(dataStudent.map(u =>
                u.id === user.id ? { ...u, statut: newStatus } : u
            ));
        } else if (activeTab === 1) {
            setDataTeacher(dataTeacher.map(u =>
                u.id === user.id ? { ...u, statut: newStatus } : u
            ));
        } else {
            setDataAdmin(dataAdmin.map(u =>
                u.id === user.id ? { ...u, statut: newStatus } : u
            ));
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
                disabled
            />
        );
    };

    const roleTemplate = (rowData) => {
        const role = roleOptions.find(r => r.value === rowData.role);
        return <span>{role ? role.label : rowData.role}</span>;
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
                        src={rowData.photo}
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

    const paymentTypeTemplate = (rowData) => {
        const payment = paymentTypes.find(p => p.value === rowData.typePaiement);
        return <span>{payment ? payment.label : rowData.typePaiement}</span>;
    };

    const centerTemplate = (rowData) => {
        const center = regionalCenters.find(c => c.value === rowData.centreRegional);
        return <span>{center ? center.label : rowData.centreRegional}</span>;
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

    const headerStudent = renderHeaderStudent();
    const headerTeacher = renderHeaderTeacher();
    const headerAdmin = renderHeaderAdmin();

    const onInputChange = (e, name) => {
        const val = (e.target && e.target.value) || '';
        let _user = { ...user };
        _user[`${name}`] = val;
        setUser(_user);
    };

    const onDropdownChange = (e, name) => {
        let _user = { ...user };
        _user[`${name}`] = e.value;
        setUser(_user);
    };

    const renderPhotoUpload = () => {
        return (
            <div className=" relative mb-4 items-center justify-center flex">
                {tempPhoto || user.photo ? (
                    <img
                        src={tempPhoto || user.photo}
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
    const [filteredParcours, setFilteredParcours] = useState([]);
    const handleMentionChange = (e) => {
        const mention = e.value;
        let _user = { ...user, mention, parcours: '' }; // Réinitialiser le parcours quand la mention change
        setUser(_user);
        setFilteredParcours(parcoursOptions[mention] || []);
    };

    const renderProfileTab = () => {
        return (
            <div className="grid">
                <div className="col-12">
                    {renderPhotoUpload()}
                </div>

                {/* Champ matricule visible seulement pour les étudiants */}
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

                {/* Champs communs à tous les utilisateurs */}
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
                            required
                        />
                    </div>
                </div>

                {/* Champs spécifiques aux étudiants */}
                {activeTab === 0 && (
                    <>
                        <div className="col-12 md:col-6">
                            <div className="field">
                                <label htmlFor="niveau">Niveau</label>
                                <Dropdown
                                    id="niveau"
                                    value={user.niveau}
                                    options={niveauOptions}
                                    onChange={(e) => onDropdownChange(e, 'niveau')}
                                    placeholder="Sélectionner un niveau"
                                    className="w-full"
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
                                    placeholder="Sélectionner une mention"
                                    className="w-full"
                                />
                            </div>
                        </div>

                        <div className="col-12">
                            <div className="field">
                                <label htmlFor="parcours">Parcours</label>
                                <Dropdown
                                    id="parcours"
                                    value={user.parcours}
                                    options={filteredParcours}
                                    onChange={(e) => onDropdownChange(e, 'parcours')}
                                    placeholder="Sélectionner un parcours"
                                    className="w-full"
                                    disabled={!user.mention}
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

                {/* Champ spécifique aux administrateurs */}
                {activeTab === 2 && (
                    <div className="col-12">
                        <div className="field">
                            <label htmlFor="role">Rôle</label>
                            <Dropdown
                                id="role"
                                value={user.role}
                                options={roleOptions}
                                optionLabel="label"
                                onChange={(e) => onDropdownChange(e, 'role')}
                                placeholder="Sélectionner un rôle"
                                className="w-full"
                            />
                        </div>
                    </div>
                )}

                {/* Champ centre régional commun à tous */}
                <div className="col-12">
                    <div className="field">
                        <label htmlFor="centreRegional">Centre régional</label>
                        <Dropdown
                            id="centreRegional"
                            value={user.centreRegional}
                            options={regionalCenters}
                            optionLabel="label"
                            onChange={(e) => onDropdownChange(e, 'centreRegional')}
                            placeholder="Sélectionner"
                            className="w-full"
                        />
                    </div>
                </div>

                {/* Champ statut commun à tous */}
                <div className="col-12">
                    <div className="field flex items-center justify-start space-x-5">
                        <label htmlFor="statut">Statut</label>
                        <InputSwitch
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



    return (
        <LayoutAdmin>
            <Toast ref={toast} />
            <div className="card custom-scrollbar" style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <h1 className='text-4xl font-semibold text-gray-800 p-5'>Gestion des utilisateurs</h1>
                <div>
                    <TabView activeIndex={activeTab} onTabChange={(e) => setActiveTab(e.index)} className='custom-tabview'>
                        <TabPanel header="Liste des étudiants" className='flex flex-col items-center'>
                            <DataTable value={dataStudent} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={headerStudent} emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="matricule" header="Matricule" sortable style={{ minWidth: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                                <Column field="mention" header="Mention" sortable style={{ minWidth: '5rem' }} />
                                <Column field="parcours" header="Parcours" sortable style={{ minWidth: '5rem' }} />
                                <Column field="typePaiement" header="Type Paiement" body={paymentTypeTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="centreRegional" header="Centre" body={centerTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Liste des enseignants" className='flex flex-col items-center'>
                            <DataTable value={dataTeacher} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={headerTeacher} emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="centreRegional" header="Centre" body={centerTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                        <TabPanel header="Liste des administrateurs" className='flex flex-col items-center'>
                            <DataTable value={dataAdmin} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={headerAdmin} emptyMessage="Aucune donnée trouvée." className='w-full'>
                                <Column field="photo" header="Profil" body={imageBodyTemplate} style={{ width: '5rem' }} />
                                <Column field="nom" header="Nom et Prénom" body={nameBodyTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                                <Column field="telephone" header="Téléphone" sortable style={{ minWidth: '5rem' }} />
                                <Column field="role" header="Rôle" body={roleTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="centreRegional" header="Centre" body={centerTemplate} sortable style={{ minWidth: '5rem' }} />
                                <Column field="statut" header="Statut" body={statusBodyTemplate} style={{ minWidth: '5rem' }} />
                                <Column body={actionBodyTemplate} style={{ minWidth: '8rem' }} />
                            </DataTable>
                        </TabPanel>
                    </TabView>
                </div>
            </div>

            {/* Dialog pour ajouter/modifier un utilisateur */}
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