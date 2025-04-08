import React, { useState } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import Layout from '../../components/Layout';
import { Button } from 'primereact/button';

export default function Bibliotheque() {
    const [data] = useState([
        { id: 1, titre: 'Livre 1', mention: 'Mathématiques', niveau: 'Licence 1', categorie: 'Science' },
        { id: 2, titre: 'Livre 2', mention: 'Physique', niveau: 'Master 2', categorie: 'Science' },
        { id: 3, titre: 'Livre 3', mention: 'Informatique', niveau: 'Licence 3', categorie: 'Technologie' },
        { id: 4, titre: 'Livre 4', mention: 'Biologie', niveau: 'Master 1', categorie: 'Science' },
        { id: 5, titre: 'Livre 5', mention: 'Chimie', niveau: 'Doctorat', categorie: 'Science' },
        { id: 6, titre: 'Livre 6', mention: 'Philosophie', niveau: 'Licence 3', categorie: 'Littérature' },
    ]);

    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [categorieFilter, setCategorieFilter] = useState('Tous');

    const mentions = [{ label: 'Mention', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.mention))).map(m => ({ label: m, value: m }))];
    const niveaux = [{ label: 'Niveau', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.niveau))).map(n => ({ label: n, value: n }))];
    const categories = [{ label: 'Catégorie', value: 'Tous' }, ...Array.from(new Set(data.map(item => item.categorie))).map(c => ({ label: c, value: c }))];

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const filteredData = data.filter(item =>
        (mentionFilter === 'Tous' || item.mention === mentionFilter) &&
        (niveauFilter === 'Tous' || item.niveau === niveauFilter) &&
        (categorieFilter === 'Tous' || item.categorie === categorieFilter) &&
        (item.titre.toLowerCase().includes(globalFilterValue) ||
            item.mention.toLowerCase().includes(globalFilterValue) ||
            item.niveau.toLowerCase().includes(globalFilterValue) ||
            item.categorie.toLowerCase().includes(globalFilterValue)));


    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className='text-3xl p-5 font-semibold'>Bibliothèque</h1>
                <div className='grid md:grid-cols-2 grid-cols-1 justify-center gap-3 items-center'>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
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

                        <Dropdown value={categorieFilter} onChange={(e) => setCategorieFilter(e.value)} options={categories}
                            optionLabel="label" placeholder="Catégorie"
                            filter valueTemplate={dropdownTemplate} itemTemplate={dropdownTemplate}
                            panelClassName="font-poppins text-sm"
                            className="rounded font-poppins text-sm bg-white"
                        />
                    </div>
                </div>
            </div>
        );
    };

    const handleDownload = (filename) => {
        const link = document.createElement('a');
        link.href = `/path/to/supports/${filename}`;
        link.download = filename;
        link.click();
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

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex items-center gap-x-2'>
                <Button icon="pi pi-download" rounded severity="secondary" onClick={() => handleDownload(rowData.nom)} />
                <Button icon="pi pi-eye" rounded severity="success" onClick={() => handleDownload(rowData.nom)} />
            </div>

        );
    };

    return (
        <Layout>
            <div>
                <DataTable value={filteredData} paginator rows={5} dataKey="id" sortField="titre" sortOrder={1} header={renderHeader()} emptyMessage="Aucune donnée trouvée.">
                    <Column field="titre" header="Titre" sortable style={{ minWidth: '10rem' }} />
                    <Column field="mention" header="Mention" sortable style={{ minWidth: '10rem' }} />
                    <Column field="niveau" header="Niveau" sortable style={{ minWidth: '10rem' }} />
                    <Column field="categorie" header="Catégorie" sortable style={{ minWidth: '10rem' }} />
                    <Column body={actionBodyTemplate} header="Action" sortable style={{ minWidth: '10rem' }} />
                </DataTable>
            </div>
        </Layout>
    );
}
