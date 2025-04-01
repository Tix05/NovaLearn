import React, { useState } from 'react';
import { useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import LayoutEnseignant from '../../components/LayoutEnseignant';
import { Link, useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import { mentions } from '../../../public/constants/data';

export default function Cours() {
    const { mentionId } = useParams();
    const [selectedSemestre, setSelectedSemestre] = useState(null);

    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const semestres = mention?.semestres || [];

    useEffect(() => {
        if (semestres.length > 0) {
            setSelectedSemestre(semestres[0].id);
        }
    }, [semestres]);

    const semestre = semestres.find((s) => s.id === selectedSemestre);
    const data = semestre?.cours || [];

    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const onGlobalFilterChange = (e) => {
        setGlobalFilterValue(e.target.value);
    };

    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl font-normal'>{mention?.nom} - {mention?.niveau}</h1>
                <div className="flex items-center py-4 space-x-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/enseignant/coursEnseignant/${mentionId}/${s.id}`}
                            className={`pb-2 transition duration-400 ${selectedSemestre === s.id
                                ? "border-b-2 border-blue-500 text-blue-600 font-semibold"
                                : "text-gray-600 hover:text-blue-500"
                                }`}
                            onClick={() => setSelectedSemestre(s.id)}
                        >
                            {s.intitule}
                        </Link>
                    ))}
                </div>

                <Divider />
                <div className='flex justify-between items-center'>
                    <h1>Parcours : {mention?.parcours}</h1>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText value={globalFilterValue} onChange={onGlobalFilterChange} placeholder="Rechercher..." className='custom-input' />
                    </IconField>
                </div>
            </div>
        );
    };

    const creditBodyTemplate = (rowData) => {
        return (
            <span className="px-2 py-1 rounded-xl font-semibold bg-[#C23B42] text-white text-xs">
                {rowData.credit}
            </span>
        );
    };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex space-x-2 items-center justify-center'>
                <Link to={`/enseignant/coursEnseignant/${mentionId}/${selectedSemestre}/${rowData.id}`} className='bg-[#39B54A] px-2 py-1 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-green-600 duration-300'>
                    Visualiser le cours
                </Link>
                <Link
                    to={`/enseignant/coursEnseignant/${mentionId}/${selectedSemestre}/${rowData.id}/ajouter-support`}
                    className='bg-[#DC3545] px-2 py-1 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-[#822a33] duration-300'
                >
                    Ajouter support
                </Link>
            </div>
        );
    };

    const header = renderHeader();

    return (
        <LayoutEnseignant>
            <div>
                <DataTable value={data} paginator rows={4} dataKey="id" sortField="nom" sortOrder={1} globalFilter={globalFilterValue} header={header} emptyMessage="Aucune donnée trouvée.">
                    <Column field="titre" header="EC" sortable style={{ minWidth: '5rem' }} />
                    <Column field="credit" header="Crédit" sortable body={creditBodyTemplate} style={{ minWidth: '5rem' }} />
                    <Column field="action" body={actionBodyTemplate} style={{ minWidth: '5rem' }} />
                </DataTable>
            </div>
        </LayoutEnseignant>
    );
}
