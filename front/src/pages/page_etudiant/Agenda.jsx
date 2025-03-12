import React, { useState } from 'react';
import { TabView, TabPanel } from 'primereact/tabview';
import Layout from '../../components/Layout';
import { Divider } from 'primereact/divider';
import { Dropdown } from 'primereact/dropdown';

export default function Agenda() {
    const [periodeFilter, setPeriodeFilter] = useState('Tout');
    const periodeOptions = [
        { label: 'Tout', value: 'Tout' },
        { label: 'Semaine', value: 'Semaine' },
        { label: 'Mois', value: 'Mois' },
        { label: 'Trimestre', value: 'Trimestre' },
        { label: 'Semestre', value: 'Semestre' },
    ];

    const coursData = [
        { id: 1, titre: 'Cours de Mathématiques', date: '2023-10-24', description: '../../../public/images/icon-esum.png', professeur: 'Prof. Dupont' },
        { id: 2, titre: 'Cours de Physique', date: '2023-10-25', description: ' Lorem ipsum dolor sit amet consectetur adipisicing elit. Omnis deserunt quis reprehenderit recusandae officiis. Sed aut ex deserunt non sunt, perferendis nobis dolor, magnam modi architecto nam fugiat possimus. Non perferendis harum enim blanditiis exercitationem accusantium quae, beatae nulla porro illo. Temporibus vel quidem porro sit eos optio voluptas possimus totam, reiciendis, repellat quibusdam, quae eaque quis perferendis atque nihil expedita! Neque minima, architecto commodi exercitationem nostrum modi? Quibusdam libero nostrum odio illo voluptas in necessitatibus harum dolorum aperiam a alias deserunt, soluta non expedita dignissimos quidem dicta nisi repellendus sit commodi. Ratione nam iure sapiente architecto sed quidem corporis. Provident asperiores exercitationem beatae ullam, tempore consequatur? Tempora laudantium illo sunt, error recusandae nesciunt, non, quae dolor nobis excepturi cumque assumenda culpa aliquid illum fugiat! Repudiandae ab rem reprehenderit inventore quo perspiciatis blanditiis officiis iste dolor, rerum perferendis deleniti facilis eveniet impedit aut at libero vitae odit, cupiditate explicabo saepe repellat atque quasi expedita? Laudantium, dolor. Hic debitis commodi libero, beatae animi molestiae odit eveniet iste porro fugit optio. Tempore maiores sed amet reprehenderit aut quos ullam placeat repudiandae, praesentium iusto harum ducimus dolore quae adipisci fugiat dolorem! Nihil tempore quam blanditiis inventore assumenda neque optio quia delectus iusto eos voluptatum modi quaerat, ipsa fugit aliquid cumque dignissimos nam in suscipit voluptate repudiandae? Sunt labore veniam veritatis nobis voluptates eveniet quaerat aut quia, maxime iusto atque perferendis enim facilis? Perspiciatis dignissimos facere voluptatibus nemo dolore odit officia. Quos eos, voluptate dolor possimus voluptatem fuga quia sit quidem cupiditate quaerat unde perferendis ratione hic repellat esse quasi quo error praesentium cumque velit laboriosam perspiciatis! Sunt ipsa fugit vel soluta aut provident praesentium, impedit suscipit fuga doloribus ea velit assumenda blanditiis, ab dicta distinctio! Cupiditate vero excepturi provident? Commodi ipsa, exercitationem ipsum hic repellendus earum maxime laboriosam cupiditate tenetur porro. Quas, ea.', professeur: 'Prof. Martin' },
        { id: 3, titre: 'Cours de Chimie', date: '2023-11-01', description: 'Réactions chimiques', professeur: 'Prof. Leroy' },
    ];

    const examensData = [
        { id: 1, titre: 'Examen de Mathématiques', date: '2023-11-10', description: 'Examen final', professeur: 'Prof. Dupont' },
        { id: 2, titre: 'Examen de Physique', date: '2023-11-15', description: 'Examen partiel', professeur: 'Prof. Martin' },
    ];

    const evenementsData = [
        { id: 1, titre: 'Conférence sur l\'IA', date: '2023-10-30', description: 'Conférence avec un expert en IA', organisateur: 'Dr. Smith' },
        { id: 2, titre: 'Journée portes ouvertes', date: '2023-11-05', description: 'Découvrez notre établissement', organisateur: 'Équipe administrative' },
    ];

    const filterData = (data) => {
        const today = new Date();
        const selectedDate = new Date(today);

        switch (periodeFilter) {
            case 'Semaine':
                selectedDate.setDate(today.getDate() + 7);
                break;
            case 'Mois':
                selectedDate.setMonth(today.getMonth() + 1);
                break;
            case 'Trimestre':
                selectedDate.setMonth(today.getMonth() + 3);
                break;
            case 'Semestre':
                selectedDate.setMonth(today.getMonth() + 6);
                break;
            default:
                return data;
        }

        return data.filter(item => {
            const itemDate = new Date(item.date);
            return itemDate <= selectedDate && itemDate >= today;
        });
    };

    const renderItem = (item) => (
        <div key={item.id} className='flex border-2 border-gray-400 mb-4 w-[100%] md:w-[80%] rounded-sm'>
            <div className='flex flex-col text-4xl items-center p-5 flex-shrink-0'>
                <h1 className='font-bold'>{new Date(item.date).getDate()}</h1>
                <h1 className='font-normal'>{new Date(item.date).toLocaleString('default', { month: 'long' })}</h1>
            </div>
            <Divider layout="vertical" />
            <div className='flex flex-col p-3 space-y-5 overflow-hidden w-full items-center justify-center'>
                <h1 className='text-2xl font-semibold text-blue-600 overflow-wrap break-word'>
                    {item.titre}
                </h1>

                {typeof item.description === 'string' && item.description.endsWith('.png' || '.jpg') ? (
                    <img src={item.description} alt={item.titre} className="w-32 h-32 object-cover rounded-lg" />
                ) : (
                    <p className='overflow-wrap break-word'>{item.description}</p>
                )}

                <h1 className='font-semibold text-xl overflow-wrap break-word'>
                    {item.professeur || item.organisateur}
                </h1>
            </div>
        </div>
    );


    return (
        <Layout>
            <div className="card">
                <TabView className='custom-tabview'>
                    <TabPanel header="Cours" className='flex flex-col items-center'>
                        <div className="mb-3 flex w-40">
                            <Dropdown
                                value={periodeFilter}
                                onChange={(e) => setPeriodeFilter(e.value)}
                                options={periodeOptions}
                                optionLabel="label"
                                placeholder="Période"
                                panelClassName="font-poppins text-sm"
                                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
                            />
                        </div>
                        {filterData(coursData).map(renderItem)}
                    </TabPanel>
                    <TabPanel header="Examens" className='flex flex-col items-center'>
                        <div className="mb-3 flex w-40">
                            <Dropdown
                                value={periodeFilter}
                                onChange={(e) => setPeriodeFilter(e.value)}
                                options={periodeOptions}
                                optionLabel="label"
                                placeholder="Période"
                                panelClassName="font-poppins text-sm"
                                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
                            />
                        </div>
                        {filterData(examensData).map(renderItem)}
                    </TabPanel>
                    <TabPanel header="Evènements" className='flex flex-col items-center'>
                        <div className="mb-3 flex w-40">
                            <Dropdown
                                value={periodeFilter}
                                onChange={(e) => setPeriodeFilter(e.value)}
                                options={periodeOptions}
                                optionLabel="label"
                                placeholder="Période"
                                panelClassName="font-poppins text-sm"
                                className="rounded font-poppins text-lg font-semibold bg-white w-full md:w-64"
                            />
                        </div>
                        {filterData(evenementsData).map(renderItem)}
                    </TabPanel>
                </TabView>
            </div>
        </Layout>
    );
}