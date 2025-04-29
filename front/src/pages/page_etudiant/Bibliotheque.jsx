import React, { useState, useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import { Dropdown } from 'primereact/dropdown';
import Layout from '../../components/Layout';
import { Button } from 'primereact/button';
import { getBibliothequeItems } from '../../Services/bibliothequeService';

export default function Bibliotheque() {
    const [data, setData] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [globalFilterValue, setGlobalFilterValue] = useState('');
    const [mentionFilter, setMentionFilter] = useState('Tous');
    const [niveauFilter, setNiveauFilter] = useState('Tous');
    const [categorieFilter, setCategorieFilter] = useState('Tous');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value.toLowerCase());
    };

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await getBibliothequeItems();
                console.log('Données reçues:', response);
                setData(Array.isArray(response) ? response : []);
                setLoading(false);
            } catch (err) {
                console.error("Erreur:", err);
                setError(err.message);
                setLoading(false);
            }
        };

        fetchData();
    }, []);

    // Options pour les dropdowns
    const mentions = [
        { label: 'Mention', value: 'Tous' },
        ...(Array.isArray(data)
            ? Array.from(new Set(data
                .filter(item => item?.mentionName && item.mentionName !== 'N/A')
                .map(item => item.mentionName)))
                .map(m => ({ label: m, value: m }))
            : [])
    ];

    const niveaux = [
        { label: 'Niveau', value: 'Tous' },
        ...(Array.isArray(data)
            ? Array.from(new Set(data
                .filter(item => item?.niveauNom && item.niveauNom !== 'N/A')
                .map(item => item.niveauNom)))
                .map(n => ({ label: n, value: n }))
            : [])
    ];

    const categories = [
        { label: 'Catégorie', value: 'Tous' },
        ...(Array.isArray(data)
            ? Array.from(new Set(data
                .filter(item => item?.type && item.type !== 'N/A')
                .map(item => item.type)))
                .map(c => ({ label: c, value: c }))
            : [])
    ];

    // Filtrage des données
    const filteredData = Array.isArray(data) ? data.filter(item => {
        if (!item) return false;

        const matchesGlobal = globalFilterValue === '' ||
            (item.titre?.toLowerCase().includes(globalFilterValue)) ||
            (item.mentionName?.toLowerCase().includes(globalFilterValue)) ||
            (item.niveauNom?.toLowerCase().includes(globalFilterValue)) ||
            (item.type?.toLowerCase().includes(globalFilterValue));

        const matchesMention = mentionFilter === 'Tous' ||
            item.mentionName === mentionFilter;

        const matchesNiveau = niveauFilter === 'Tous' ||
            item.niveauNom === niveauFilter;

        const matchesCategorie = categorieFilter === 'Tous' ||
            item.type === categorieFilter;

        return matchesGlobal && matchesMention && matchesNiveau && matchesCategorie;
    }) : [];

    const renderHeader = () => {
        return (
            <div className="flex flex-col space-y-4">
                <h1 className='text-3xl p-5 font-semibold'>Bibliothèque</h1>
                <div className='grid md:grid-cols-2 grid-cols-1 justify-center gap-3 items-center'>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText
                            value={globalFilterValue}
                            onChange={onGlobalFilterChange}
                            placeholder="Rechercher..."
                            className='custom-input'
                        />
                    </IconField>

                    <div className="flex flex-wrap items-center justify-center gap-3 bibliotheque-dropdown">
                        <Dropdown
                            value={mentionFilter}
                            onChange={(e) => setMentionFilter(e.value)}
                            options={mentions}
                            optionLabel="label"
                            placeholder="Mention"
                            className="rounded font-poppins text-sm bg-white"
                            disabled={loading}
                        />

                        <Dropdown
                            value={niveauFilter}
                            onChange={(e) => setNiveauFilter(e.value)}
                            options={niveaux}
                            optionLabel="label"
                            placeholder="Niveau"
                            className="rounded font-poppins text-sm bg-white"
                            disabled={loading}
                        />

                        <Dropdown
                            value={categorieFilter}
                            onChange={(e) => setCategorieFilter(e.value)}
                            options={categories}
                            optionLabel="label"
                            placeholder="Catégorie"
                            className="rounded font-poppins text-sm bg-white"
                            disabled={loading}
                        />
                    </div>
                </div>
            </div>
        );
    };

    const handleDownload = (fichier) => {
        if (!fichier) return;
        const link = document.createElement('a');
        link.href = `/uploads/bibliotheque/${fichier}`;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex items-center gap-x-2'>
                <Button
                    icon="pi pi-download"
                    rounded
                    severity="secondary"
                    onClick={() => handleDownload(rowData.fichier)}
                    tooltip="Télécharger"
                    tooltipOptions={{ position: 'top' }}
                    disabled={!rowData.fichier}
                />
                <Button
                    icon="pi pi-eye"
                    rounded
                    severity="success"
                    onClick={() => handleDownload(rowData.fichier)}
                    tooltip="Voir"
                    tooltipOptions={{ position: 'top' }}
                    disabled={!rowData.fichier}
                />
            </div>
        );
    };

    if (loading) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex items-center justify-center">
                    <div class="spinner-container">
                        <div class="spinner-outer">
                            <div class="spinner-inner"></div>
                        </div>
                    </div>
                </div>
            </Layout>
        );
    }

    if (error) {
        return (
            <Layout>
                <div className="h-[90vh] w-full flex flex-col text-red-500 items-center space-y-5 justify-center">
                    <MdErrorOutline size={60} />
                    <p className='text-xl font-bold'>Erreur lors du chargement des données</p>
                    <p className='text-lg font-semibold'>{error}</p>
                </div>
            </Layout>
        );
    }

    return (
        <Layout>
            <div className="p-4">
                <DataTable
                    value={filteredData}
                    paginator
                    rows={10}
                    dataKey="id"
                    sortField="titre"
                    sortOrder={1}
                    header={renderHeader()}
                    emptyMessage="Aucune donnée trouvée."
                    loading={loading}
                >
                    <Column
                        field="titre"
                        header="Titre"
                        sortable
                        style={{ minWidth: '12rem' }}
                        body={(rowData) => rowData.titre || 'N/A'}
                    />
                    <Column
                        field="mentionName"
                        header="Mention"
                        sortable
                        style={{ minWidth: '12rem' }}
                        body={(rowData) => rowData.mentionName || 'N/A'}
                    />
                    <Column
                        field="niveauNom"
                        header="Niveau"
                        sortable
                        style={{ minWidth: '10rem' }}
                        body={(rowData) => rowData.niveauNom || 'N/A'}
                    />
                    <Column
                        field="type"
                        header="Type"
                        sortable
                        style={{ minWidth: '10rem' }}
                        body={(rowData) => rowData.type || 'N/A'}
                    />
                    <Column
                        body={actionBodyTemplate}
                        header="Actions"
                        style={{ minWidth: '10rem' }}
                        exportable={false}
                    />
                </DataTable>
            </div>
        </Layout>
    );
}