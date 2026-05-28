import React, { useState, useEffect } from 'react';
import { Accordion, AccordionTab } from 'primereact/accordion';
import { DataTable } from 'primereact/datatable';
import { Column } from 'primereact/column';
import { Link } from 'react-router-dom';
import { InputText } from 'primereact/inputtext';
import { IconField } from 'primereact/iconfield';
import { InputIcon } from 'primereact/inputicon';

const AccordionUE = ({ ues, mentionId, niveauId, semestreId }) => {
    const [activeIndex, setActiveIndex] = useState(null);
    const [filters, setFilters] = useState({});

    useEffect(() => {
        const initialFilters = {};
        ues.forEach(ue => {
            initialFilters[ue.id] = '';
        });
        setFilters(initialFilters);
    }, [ues]);

    const creditBodyTemplate = (rowData) => {
        return (
            <span className="px-2 py-1 rounded-xl font-semibold bg-[#C23B42] text-white text-xs">
                {rowData.credit}
            </span>
        );
    };

    // const actionBodyTemplate = (rowData) => {
    //     return (
    //         <Link
    //             to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours/${rowData.id}`}
    //             className='bg-[#39B54A] px-3 py-2 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-emerald-600 duration-300'
    //         >
    //             Visualiser le cours
    //         </Link>
    //     );
    // };

    const actionBodyTemplate = (rowData) => {
        return (
            <div className='flex space-x-2 items-center justify-center'>
                <Link
                    to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours/${rowData.id}`}
                    className='bg-emerald-600 px-2 py-1 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-emerald-700 duration-300'
                >
                    Visualiser le cours
                </Link>
                <Link
                    to={`/admin/mentions/${mentionId}/niveaux/${niveauId}/semestres/${semestreId}/cours/${rowData.id}/ajout-support`}
                    className='bg-[#DC3545] px-2 py-1 rounded-md text-white font-semibold cursor-pointer text-sm hover:bg-[#822a33] duration-300'
                >
                    Ajouter support
                </Link>
            </div>
        );
    };

    const onGlobalFilterChange = (ueId, value) => {
        setFilters(prev => ({
            ...prev,
            [ueId]: value
        }));
    };

    const renderTableHeader = (ue) => {
        return (
            <div className="flex justify-between items-center mb-2">
                <span className="font-semibold">Liste des EC</span>
                <IconField iconPosition="left">
                    <InputIcon className="pi pi-search" />
                    <InputText
                        value={filters[ue.id] || ''}
                        onChange={(e) => onGlobalFilterChange(ue.id, e.target.value)}
                        placeholder="Rechercher un EC..."
                        className="w-full"
                        size="small"
                    />
                </IconField>
            </div>
        );
    };

    return (
        <div className="card">
            <Accordion activeIndex={activeIndex} onTabChange={(e) => setActiveIndex(e.index)}>
                {ues.map((ue) => (
                    <AccordionTab key={ue.id} header={ue.nom}>
                        <div className="p-2">
                            <DataTable
                                value={ue.cours}
                                header={renderTableHeader(ue)}
                                rows={5}
                                globalFilter={filters[ue.id]}
                                emptyMessage="Aucun cours trouvé dans cette UE"
                                paginator
                                paginatorTemplate="CurrentPageReport FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                                currentPageReportTemplate=""
                            >
                                <Column field="titre" header="EC" sortable />
                                <Column field="credit" header="Crédit" body={creditBodyTemplate} />
                                <Column body={actionBodyTemplate} header="Actions" />
                            </DataTable>
                        </div>
                    </AccordionTab>
                ))}
            </Accordion>
        </div>
    );
};

export default AccordionUE;