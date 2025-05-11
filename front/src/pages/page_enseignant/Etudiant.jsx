import React, { useState, useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import { ProgressSpinner } from 'primereact/progressspinner';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { getTeacherStudents } from '../../Services/teacherAuthService';

export default function Etudiant() {
    const [students, setStudents] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [searchTerm, setSearchTerm] = useState('');

    useEffect(() => {
        const fetchStudents = async () => {
            try {
                setLoading(true);
                const data = await getTeacherStudents();
                setStudents(data);
                setLoading(false);
            } catch (err) {
                setError(err.message);
                setLoading(false);
            }
        };
        fetchStudents();
    }, []);

    useEffect(() => {
        const timer = setTimeout(() => setGlobalFilterValue(searchTerm), 300);
        return () => clearTimeout(timer);
    }, [searchTerm]);

    // Créer les options pour les filtres de mentions et niveaux
    const mentions = [
        { label: 'Tous', value: 'Tous' },
        ...Array.from(new Set(students.map(item => item.mention))).map(m => ({ label: m, value: m }))
    ];
    const niveaux = [
        { label: 'Tous', value: 'Tous' },
        ...Array.from(new Set(students.map(item => item.niveau))).map(n => ({ label: n, value: n }))
    ];

    // Filtrer les données
    const filteredData = students.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (globalFilterValue === '' ||
            item.nom.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.email.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.mention.toLowerCase().includes(globalFilterValue.toLowerCase()) ||
            item.niveau.toLowerCase().includes(globalFilterValue.toLowerCase()))
    );

    const onGlobalFilterChange = (e) => {
        setSearchTerm(e.target.value);
    };

    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className='text-3xl p-5 font-semibold'>Mes Étudiants</h1>
                <div className='grid md:grid-cols-2 grid-cols-1 justify-center gap-3 items-center'>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={searchTerm} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                    <div className="flex flex-wrap items-center justify-center gap-3 bibliotheque-dropdown">
                        <Dropdown value={mentionFilter} onChange={(e) => setMentionFilter(e.value)} options={mentions}
                            optionLabel="label" placeholder="Mention"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                        <Dropdown value={niveauFilter} onChange={(e) => setNiveauFilter(e.value)} options={niveaux}
                            optionLabel="label" placeholder="Niveau"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                    </div>
                </div>
            </div>
        );
    };

    const dropdownTemplate = (option, props) => {
        if (option) {
            return (
                <div className="flex align-items-center">
                    <div>{option.label}</div>
                </div>
            );
        }
        return <span>{props.placeholder}</span>;
    };

    const imageBodyTemplate = (rowData) => {
        return (
            <div className="flex justify-center">
                <img
                    src={rowData.profil}
                    alt={rowData.nom}
                    className="w-10 h-10 rounded-full object-cover border-2 border-white shadow"
                    onError={(e) => {
                        e.target.src = 'https://www.gravatar.com/avatar/default?s=200&d=mm';
                        e.target.onerror = null;
                    }}
                />
            </div>
        );
    };

    const header = renderHeader();

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
                <div className="flex justify-center items-center h-full">
                    <p className="text-red-500">{error}</p>
                </div>
            </LayoutEnseignant>
        );
    }

    return (
        <LayoutEnseignant>
            <div className='custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <DataTable value={filteredData} paginator rows={10} dataKey="id" sortField="nom" sortOrder={1} header={header} emptyMessage="Aucun étudiant trouvé.">
                    <Column field="numero" header="N°" sortable style={{ width: '2rem' }} />
                    <Column field="profil" header="Photo" body={imageBodyTemplate} style={{ width: '5rem' }} />
                    <Column field="nom" header="Nom et Prénom" sortable style={{ minWidth: '5rem' }} />
                    <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                    <Column field="mention" header="Mention" sortable style={{ minWidth: '5rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </LayoutEnseignant>

    );
}