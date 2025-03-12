import React, { useState } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import Layout from '../../components/Layout';


export default function Enseignant() {
    const [data] = useState([
        { id: 1, nom: 'Jean Dupont', ec: 'Mathématiques', niveau: 'Licence 1' },
        { id: 2, nom: 'Marie Curie', ec: 'Physique', niveau: 'Master 2' },
        { id: 3, nom: 'Albert Einstein', ec: 'Relativité', niveau: 'Doctorat' },
        { id: 4, nom: 'Isaac Newton', ec: 'Physique', niveau: 'Licence 2' },
        { id: 5, nom: 'Stephen Hawking', ec: 'Astrophysique', niveau: 'Master 1' },
        { id: 6, nom: 'Alan Turing', ec: 'Informatique', niveau: 'Licence 3' },
        { id: 7, nom: 'Ada Lovelace', ec: 'Algorithmique', niveau: 'Master 2' },
        { id: 8, nom: 'Niels Bohr', ec: 'Physique Quantique', niveau: 'Doctorat' },
        { id: 9, nom: 'Galilée', ec: 'Astronomie', niveau: 'Licence 1' },
        { id: 10, nom: 'Charles Darwin', ec: 'Biologie', niveau: 'Master 1' },
        { id: 11, nom: 'Louis Pasteur', ec: 'Microbiologie', niveau: 'Licence 2' },
        { id: 12, nom: 'Marie Curie', ec: 'Chimie', niveau: 'Doctorat' },
        { id: 13, nom: 'Thomas Edison', ec: 'Électrotechnique', niveau: 'Licence 3' },
        { id: 14, nom: 'Nikola Tesla', ec: 'Ingénierie Électrique', niveau: 'Master 2' },
        { id: 15, nom: 'Leonardo da Vinci', ec: 'Arts et Sciences', niveau: 'Licence 1' },
        { id: 16, nom: 'Pythagore', ec: 'Mathématiques', niveau: 'Master 1' },
        { id: 17, nom: 'Archimède', ec: 'Physique', niveau: 'Licence 2' },
        { id: 18, nom: 'Euclide', ec: 'Géométrie', niveau: 'Doctorat' },
        { id: 19, nom: 'Socrate', ec: 'Philosophie', niveau: 'Licence 3' },
        { id: 20, nom: 'Platon', ec: 'Philosophie', niveau: 'Master 2' }
    ]);
    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const renderHeader = () => {
        return (
            <div className="flex justify-between items-center">
                <h1 className='text-3xl font-normal'>Mes enseignant</h1>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                </IconField>
            </div>
        );
    };


    const header = renderHeader();

    return (
        <Layout>
            <div>
                <DataTable value={data} paginator rows={7} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucune donnée trouvée.">
                    <Column field="nom" header="Nom et Prénom" sortable style={{ minWidth: '5rem' }} />
                    <Column field="ec" header="EC" sortable style={{ minWidth: '5rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </Layout>
    );
}
