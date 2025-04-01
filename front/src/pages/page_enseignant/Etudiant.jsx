import React, { useState } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import LayoutEnseignant from '../../components/LayoutEnseignant';

export default function Etudiant() {
    const [data] = useState([
        { id: 1, numero: 1, nom: 'Jean Dupont', email: 'jean.dupont@example.com', mention: 'Mathématiques', niveau: 'Licence 1' },
        { id: 2, numero: 2, nom: 'Marie Curie', email: 'marie.curie@example.com', mention: 'Physique', niveau: 'Master 2' },
        { id: 3, numero: 3, nom: 'Albert Einstein', email: 'albert.einstein@example.com', mention: 'Relativité', niveau: 'Doctorat' },
        { id: 4, numero: 4, nom: 'Isaac Newton', email: 'isaac.newton@example.com', mention: 'Physique', niveau: 'Licence 2' },
        { id: 5, numero: 5, nom: 'Stephen Hawking', email: 'stephen.hawking@example.com', mention: 'Astrophysique', niveau: 'Master 1' },
        { id: 6, numero: 6, nom: 'Alan Turing', email: 'alan.turing@example.com', mention: 'Informatique', niveau: 'Licence 3' },
        { id: 7, numero: 7, nom: 'Ada Lovelace', email: 'ada.lovelace@example.com', mention: 'Algorithmique', niveau: 'Master 2' },
        { id: 8, numero: 8, nom: 'Niels Bohr', email: 'niels.bohr@example.com', mention: 'Physique Quantique', niveau: 'Doctorat' },
        { id: 9, numero: 9, nom: 'Galilée', email: 'galilee@example.com', mention: 'Astronomie', niveau: 'Licence 1' },
        { id: 10, numero: 10, nom: 'Charles Darwin', email: 'charles.darwin@example.com', mention: 'Biologie', niveau: 'Master 1' }
    ]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal'>Mes étudiants</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };

    const header = renderHeader();

    return (
        <LayoutEnseignant>
            <div>
                <DataTable value={data} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucune donnée trouvée.">
                    <Column field="numero" header="Numéro" sortable style={{ minWidth: '5rem' }} />
                    <Column field="nom" header="Nom et Prénom" sortable style={{ minWidth: '5rem' }} />
                    <Column field="email" header="Email" sortable style={{ minWidth: '5rem' }} />
                    <Column field="mention" header="Mention" sortable style={{ minWidth: '5rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </LayoutEnseignant>
    );
}
