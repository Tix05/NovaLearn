import React from 'react';
import img from '../assets/images/logo.png';

const Loading = () => {
    return (
        <div className='flex flex-col items-center justify-center min-h-screen space-y-10'>
            <img src={img} alt="logo" className='w-[300px]' />
            <div class="spinner-container">
                <div class="spinner-outer">
                    <div class="spinner-inner"></div>
                </div>
            </div>
        </div>

    );
};

export default Loading;