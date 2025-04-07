import React, { useState, useEffect } from 'react';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';
import LayoutAdmin from '../../components/LayoutAdmin';
import { Link, useParams } from 'react-router-dom';
import { Divider } from 'primereact/divider';
import { mentions } from '../../../public/constants/data';

export default function CoursAdmin() {
    const { mentionId, niveauId } = useParams();
    const [selectedSemestre, setSelectedSemestre] = useState(null);

    // Trouver la mention et le niveau correspondant
    const mention = mentions.find((m) => m.id === parseInt(mentionId));
    const niveau = mention?.niveaux?.find((n) => n.id === niveauId);
    const semestres = niveau?.semestres || [];

    useEffect(() => {
        if (semestres.length > 0) {
            setSelectedSemestre(semestres[0].id);
        }
    }, [semestres]);

    const semestre = semestres.find((s) => s.id === selectedSemestre);
    const data = semestre?.cours || [];
    const [globalFilterValue, setGlobalFilterValue] = useState('');

    const renderHeader = () => {
        return (
            <div className="flex flex-col">
                <h1 className='text-3xl font-normal'>{mention?.nom} - {niveau?.nom}</h1>
                <div className="flex items-center py-4 space-x-5">
                    {semestres.map((s) => (
                        <Link
                            key={s.id}
                            to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${s.id}/cours`}
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
                    <h1>Parcours : {niveau?.parcours}</h1>
                    <IconField iconPosition="left">
                        <InputIcon className="pi pi-search" />
                        <InputText
                            value={globalFilterValue}
                            onChange={(e) => setGlobalFilterValue(e.target.value)}
                            placeholder="Rechercher..."
                        />
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
            <Link
                to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${selectedSemestre}/cours/${rowData.id}`}
                className='bg-[#39B54A] px-3 py-2 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-green-600 duration-300'
            >
                Visualiser le cours
            </Link>
        );
    };

    return (
        <LayoutAdmin>
            <DataTable
                value={data}
                paginator
                rows={5}
                header={renderHeader()}
                emptyMessage="Aucun cours trouvé"
            >
                <Column field="titre" header="EC" sortable />
                <Column field="credit" header="Crédit" body={creditBodyTemplate} />
                <Column body={actionBodyTemplate} header="Actions" />
            </DataTable>
        </LayoutAdmin>
    );
}