import React, { useState } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import Layout from '../../components/Layout';
import { Button } from 'primereact/button';


export default function Enseignant() {
    const [data] = useState([
        {
            id: 1,
            nom: 'Jean Dupont',
            ec: 'Mathématiques',
            niveau: 'Licence 1',
            profil: 'https://randomuser.me/api/portraits/men/1.jpg'
        },
        {
            id: 2,
            nom: 'Marie Curie',
            ec: 'Physique',
            niveau: 'Master 2',
            profil: 'https://randomuser.me/api/portraits/women/1.jpg'
        },
        {
            id: 3,
            nom: 'Albert Einstein',
            ec: 'Relativité',
            niveau: 'Doctorat',
            profil: 'https://randomuser.me/api/portraits/men/2.jpg'
        },
        {
            id: 4,
            nom: 'Isaac Newton',
            ec: 'Physique',
            niveau: 'Licence 2',
            profil: 'https://randomuser.me/api/portraits/men/3.jpg'
        },
        {
            id: 5,
            nom: 'Stephen Hawking',
            ec: 'Astrophysique',
            niveau: 'Master 1',
            profil: 'https://randomuser.me/api/portraits/men/4.jpg'
        },
        {
            id: 6,
            nom: 'Alan Turing',
            ec: 'Informatique',
            niveau: 'Licence 3',
            profil: 'https://randomuser.me/api/portraits/men/5.jpg'
        },
        {
            id: 7,
            nom: 'Ada Lovelace',
            ec: 'Algorithmique',
            niveau: 'Master 2',
            profil: 'https://randomuser.me/api/portraits/women/2.jpg'
        },
        {
            id: 8,
            nom: 'Niels Bohr',
            ec: 'Physique Quantique',
            niveau: 'Doctorat',
            profil: 'https://randomuser.me/api/portraits/men/6.jpg'
        },
        {
            id: 9,
            nom: 'Galilée',
            ec: 'Astronomie',
            niveau: 'Licence 1',
            profil: 'https://randomuser.me/api/portraits/men/7.jpg'
        },
        {
            id: 10,
            nom: 'Charles Darwin',
            ec: 'Biologie',
            niveau: 'Master 1',
            profil: 'https://randomuser.me/api/portraits/men/8.jpg'
        },
        {
            id: 11,
            nom: 'Louis Pasteur',
            ec: 'Microbiologie',
            niveau: 'Licence 2',
            profil: 'https://randomuser.me/api/portraits/men/9.jpg'
        },
        {
            id: 12,
            nom: 'Marie Curie',
            ec: 'Chimie',
            niveau: 'Doctorat',
            profil: 'https://randomuser.me/api/portraits/women/3.jpg'
        },
        {
            id: 13,
            nom: 'Thomas Edison',
            ec: 'Électrotechnique',
            niveau: 'Licence 3',
            profil: 'https://randomuser.me/api/portraits/men/10.jpg'
        },
        {
            id: 14,
            nom: 'Nikola Tesla',
            ec: 'Ingénierie Électrique',
            niveau: 'Master 2',
            profil: 'https://randomuser.me/api/portraits/men/11.jpg'
        },
        {
            id: 15,
            nom: 'Leonardo da Vinci',
            ec: 'Arts et Sciences',
            niveau: 'Licence 1',
            profil: 'https://randomuser.me/api/portraits/men/12.jpg'
        },
        {
            id: 16,
            nom: 'Pythagore',
            ec: 'Mathématiques',
            niveau: 'Master 1',
            profil: 'https://randomuser.me/api/portraits/men/13.jpg'
        },
        {
            id: 17,
            nom: 'Archimède',
            ec: 'Physique',
            niveau: 'Licence 2',
            profil: 'https://randomuser.me/api/portraits/men/14.jpg'
        },
        {
            id: 18,
            nom: 'Euclide',
            ec: 'Géométrie',
            niveau: 'Doctorat',
            profil: 'https://randomuser.me/api/portraits/men/15.jpg'
        },
        {
            id: 19,
            nom: 'Socrate',
            ec: 'Philosophie',
            niveau: 'Licence 3',
            profil: 'https://randomuser.me/api/portraits/men/16.jpg'
        },
        {
            id: 20,
            nom: 'Platon',
            ec: 'Philosophie',
            niveau: 'Master 2',
            profil: 'https://randomuser.me/api/portraits/men/17.jpg'
        }
    ]);

    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
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

    const actionBodyTemplate = (rowData) => {
        return (
            <Button icon="pi pi-send" rounded severity="success" tooltip="Envoyer message"
                tooltipOptions={{ position: 'top' }} />
        );
    };

    return (
        <Layout>
            <div className='custom-scrollbar' style={{ height: 'calc(100vh - 3.5rem)', overflowY: 'auto' }}>
                <DataTable value={data} paginator rows={10} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucune donnée trouvée.">
                    <Column field="profil" header="Photo" body={imageBodyTemplate} style={{ width: '5rem' }} />
                    <Column field="nom" header="Nom et Prénom" sortable style={{ minWidth: '5rem' }} />
                    <Column field="ec" header="EC" sortable style={{ minWidth: '5rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '5rem' }} />
                    <Column body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </Layout>
    );
}
